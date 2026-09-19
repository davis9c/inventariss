<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\UserLocationModel;
use App\Models\LocationModel;
use App\Libraries\UserGateLibrary;

class User extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;
    protected UserRoleModel $userRoleModel;
    protected UserLocationModel $userLocationModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->userModel          = new UserModel();
        $this->roleModel          = new RoleModel();
        $this->userRoleModel      = new UserRoleModel();
        $this->userLocationModel  = new UserLocationModel();
        $this->locationModel      = new LocationModel();
    }

    /**
     * Helper: ambil UserGateLibrary dengan access token dari session.
     */
    private function ug(): UserGateLibrary
    {
        return new UserGateLibrary();
    }

    private function token(): string
    {
        return session()->get('access_token') ?? '';
    }

    /**
     * Daftar user — data dari UserGate + role/lokasi lokal.
     */
    public function index()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $search  = $this->request->getGet('search') ?? '';

        $result = $this->ug()->getUsers($this->token(), $page, 20, $search);

        $ugUsers = $result['data'] ?? [];
        $meta    = $result['meta'] ?? null;

        // Gabungkan dengan data lokal
        $users = [];
        foreach ($ugUsers as $ugUser) {
            $local = $this->userModel->syncFromUserGate($ugUser);

            $roles = $this->userRoleModel
                ->select('roles.name')
                ->join('roles', 'roles.id = user_roles.role_id')
                ->where('user_roles.user_id', $local['id'])
                ->findAll();

            $users[] = array_merge($local, [
                'ug_data' => $ugUser,
                'roles'   => $roles,
            ]);
        }

        return view('users/index', [
            'title' => 'User Management',
            'users' => $users,
            'meta'  => $meta,
            'search' => $search,
        ]);
    }

    /**
     * Detail user — data dari UserGate + role/lokasi lokal.
     */
    public function show($id)
    {
        // Cari user lokal by id
        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Ambil data dari UserGate jika punya usergate_id
        $ugUser = null;
        if (!empty($user['usergate_id'])) {
            $result = $this->ug()->getUser($this->token(), $user['usergate_id']);
            if ($result['status']) {
                $ugUser = $result['data'];
            }
        }

        // Role lokal
        $roles = $this->userRoleModel
            ->select('roles.name, roles.description')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $id)
            ->orderBy('roles.name', 'ASC')
            ->findAll();

        // Lokasi lokal
        $locations = $this->userLocationModel
            ->select('locations.name, locations.building, locations.floor, locations.room')
            ->join('locations', 'locations.id = user_locations.location_id')
            ->where('user_locations.user_id', $id)
            ->orderBy('locations.name', 'ASC')
            ->findAll();

        return view('users/show', [
            'title'     => 'Detail User',
            'user'      => $user,
            'ugUser'    => $ugUser,
            'roles'     => $roles,
            'locations' => $locations,
        ]);
    }

    /**
     * Form tambah user → proxy ke UserGate.
     */
    public function create()
    {
        return view('users/create', [
            'title' => 'Tambah User',
        ]);
    }

    /**
     * Simpan user baru → proxy ke UserGate POST /users.
     */
    public function store()
    {
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => 'required|min_length[3]|max_length[100]',
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
            ],
            'full_name' => [
                'label' => 'Nama Lengkap',
                'rules' => 'required|min_length[3]|max_length[150]',
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $result = $this->ug()->createUser($this->token(), [
            'username'  => $this->request->getPost('username'),
            'email'     => $this->request->getPost('email'),
            'full_name' => $this->request->getPost('full_name'),
            'password'  => $this->request->getPost('password'),
        ]);

        if (!$result['status']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result['message'] ?: 'Gagal membuat user.');
        }

        // Sync ke lokal
        $this->userModel->syncFromUserGate($result['data']);

        return redirect()
            ->to('/users')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Form edit user — atur role + lokasi lokal.
     */
    public function edit($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Role user lokal
        $userRoles = $this->userRoleModel
            ->where('user_id', $id)
            ->findAll();
        $roleIds = array_column($userRoles, 'role_id');

        // Lokasi user lokal
        $userLocations = $this->userLocationModel
            ->where('user_id', $id)
            ->findAll();
        $locationIds = array_column($userLocations, 'location_id');

        // Data dari UserGate (read-only untuk ditampilkan)
        $ugUser = null;
        if (!empty($user['usergate_id'])) {
            $result = $this->ug()->getUser($this->token(), $user['usergate_id']);
            if ($result['status']) {
                $ugUser = $result['data'];
            }
        }

        return view('users/edit', [
            'title'       => 'Edit User',
            'user'        => $user,
            'ugUser'      => $ugUser,
            'roles'       => $this->roleModel->orderBy('name', 'ASC')->findAll(),
            'roleIds'     => $roleIds,
            'locations'   => $this->locationModel->orderBy('name', 'ASC')->findAll(),
            'locationIds' => $locationIds,
        ]);
    }

    /**
     * Update user — proxy ke UserGate PUT /users/{id} + update role/lokasi lokal.
     */
    public function update($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // ─── Update ke UserGate (jika ada usergate_id) ──────
        if (!empty($user['usergate_id'])) {
            $ugData = [];

            if ($this->request->getPost('username')) {
                $ugData['username'] = $this->request->getPost('username');
            }
            if ($this->request->getPost('email')) {
                $ugData['email'] = $this->request->getPost('email');
            }
            if ($this->request->getPost('full_name')) {
                $ugData['full_name'] = $this->request->getPost('full_name');
            }
            if ($this->request->getPost('status')) {
                $ugData['status'] = $this->request->getPost('status');
            }

            if (!empty($ugData)) {
                $this->ug()->updateUser(
                    $this->token(),
                    $user['usergate_id'],
                    $ugData
                );
            }
        }

        // ─── Update lokal ───────────────────────────────────
        $localData = [
            'name'      => $this->request->getPost('full_name')
                ?: $this->request->getPost('name')
                ?: $user['name'],
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        $this->userModel->update($id, $localData);

        // ─── Update role lokal ──────────────────────────────
        $this->userRoleModel->where('user_id', $id)->delete();

        $roleIds = $this->request->getPost('role_ids');
        if (!empty($roleIds)) {
            foreach ($roleIds as $roleId) {
                $this->userRoleModel->insert([
                    'user_id'    => $id,
                    'role_id'    => $roleId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // ─── Update lokasi lokal ────────────────────────────
        $this->userLocationModel->where('user_id', $id)->delete();

        $locationIds = $this->request->getPost('location_ids');
        if (!empty($locationIds)) {
            foreach ($locationIds as $locationId) {
                $this->userLocationModel->insert([
                    'user_id'     => $id,
                    'location_id' => $locationId,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()
            ->to('/users')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Hapus user — proxy ke UserGate DELETE /users/{id} + hapus lokal.
     */
    public function delete($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Hapus di UserGate (jika ada usergate_id)
        if (!empty($user['usergate_id'])) {
            $this->ug()->deleteUser($this->token(), $user['usergate_id']);
        }

        // Hapus relasi lokal
        $this->userRoleModel->where('user_id', $id)->delete();
        $this->userLocationModel->where('user_id', $id)->delete();
        $this->userModel->delete($id);

        return redirect()
            ->to('/users')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Update role lokal saja (POST).
     */
    public function updateRoles($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->userRoleModel->where('user_id', $id)->delete();

        $roleIds = $this->request->getPost('role_ids');
        if (!empty($roleIds)) {
            foreach ($roleIds as $roleId) {
                $this->userRoleModel->insert([
                    'user_id'    => $id,
                    'role_id'    => $roleId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Role user berhasil diperbarui.');
    }

    /**
     * Update lokasi lokal saja (POST).
     */
    public function updateLocations($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->userLocationModel->where('user_id', $id)->delete();

        $locationIds = $this->request->getPost('location_ids');
        if (!empty($locationIds)) {
            foreach ($locationIds as $locationId) {
                $this->userLocationModel->insert([
                    'user_id'     => $id,
                    'location_id' => $locationId,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Lokasi user berhasil diperbarui.');
    }
}
