<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\UserLocationModel;
use App\Models\LocationModel;

class User extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;
    protected UserRoleModel $userRoleModel;
    protected UserLocationModel $userLocationModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->userLocationModel = new UserLocationModel();
        $this->locationModel = new LocationModel();
    }

    public function index()
    {
        $users = $this->userModel
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($users as &$user) {
            $roles = $this->userRoleModel
                ->select('roles.name')
                ->join('roles', 'roles.id = user_roles.role_id')
                ->where('user_roles.user_id', $user['id'])
                ->findAll();

            $user['roles'] = $roles;
        }

        return view('users/index', [
            'title' => 'User Management',
            'users' => $users,
        ]);
    }
    public function create()
    {
        return view('users/create', [
            'title' => 'Tambah User',

            'roles' => $this->roleModel
                ->orderBy('name', 'ASC')
                ->findAll(),

            'locations' => $this->locationModel
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'username' => [
                'label'  => 'Username',
                'rules'  => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'required|min_length[6]',
            ],
            'name' => [
                'label'  => 'Nama',
                'rules' => 'required|max_length[100]',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $roles = $this->request->getPost('role_ids');
        $locations = $this->request->getPost('location_ids');

        // Simpan user
        $this->userModel->insert([
            'username'  => $this->request->getPost('username'),
            'password'  => password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            ),
            'name'      => $this->request->getPost('name'),
            'is_active' => 1,
        ]);

        $userId = $this->userModel->getInsertID();

        // Simpan role
        if (!empty($roles)) {
            foreach ($roles as $roleId) {
                $this->userRoleModel->insert([
                    'user_id'    => $userId,
                    'role_id'    => $roleId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Simpan lokasi
        if (!empty($locations)) {
            foreach ($locations as $locationId) {
                $this->userLocationModel->insert([
                    'user_id'     => $userId,
                    'location_id' => $locationId,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()
            ->to('/users')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Ambil role user
        $userRoles = $this->userRoleModel
            ->where('user_id', $id)
            ->findAll();

        $roleIds = array_column($userRoles, 'role_id');

        // Ambil lokasi user
        $userLocations = $this->userLocationModel
            ->where('user_id', $id)
            ->findAll();

        $locationIds = array_column($userLocations, 'location_id');

        return view('users/edit', [
            'title'       => 'Edit User',
            'user'        => $user,

            'roles'       => $this->roleModel
                ->orderBy('name', 'ASC')
                ->findAll(),

            'roleIds'     => $roleIds,

            'locations'   => $this->locationModel
                ->orderBy('name', 'ASC')
                ->findAll(),

            'locationIds' => $locationIds,
        ]);
    }

    public function update($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
            ],
            'name' => [
                'label' => 'Nama',
                'rules' => 'required|max_length[100]',
            ],
        ];

        $password = $this->request->getPost('password');

        if (!empty($password)) {
            $rules['password'] = [
                'label' => 'Password',
                'rules' => 'min_length[6]',
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        /*
    |--------------------------------------------------------------------------
    | Data User
    |--------------------------------------------------------------------------
    */

        $data = [
            'username'  => $this->request->getPost('username'),
            'name'      => $this->request->getPost('name'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        // Jika password diisi, ubah password
        if (!empty($password)) {
            $data['password'] = password_hash(
                $password,
                PASSWORD_DEFAULT
            );
        }

        $this->userModel->update($id, $data);

        /*
    |--------------------------------------------------------------------------
    | Update Role
    |--------------------------------------------------------------------------
    */

        $this->userRoleModel
            ->where('user_id', $id)
            ->delete();

        $roles = $this->request->getPost('role_ids');

        if (!empty($roles)) {
            foreach ($roles as $roleId) {
                $this->userRoleModel->insert([
                    'user_id'    => $id,
                    'role_id'    => $roleId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Update Lokasi
    |--------------------------------------------------------------------------
    */

        $this->userLocationModel
            ->where('user_id', $id)
            ->delete();

        $locations = $this->request->getPost('location_ids');

        if (!empty($locations)) {
            foreach ($locations as $locationId) {
                $this->userLocationModel->insert([
                    'user_id'     => $id,
                    'location_id' => $locationId,
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()
            ->to('/users')
            ->with(
                'success',
                'User berhasil diperbarui.'
            );
    }

    public function show($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Role user
        $roles = $this->userRoleModel
            ->select('roles.name, roles.description')
            ->join(
                'roles',
                'roles.id = user_roles.role_id'
            )
            ->where('user_roles.user_id', $id)
            ->orderBy('roles.name', 'ASC')
            ->findAll();

        // Lokasi user
        $locations = $this->userLocationModel
            ->select('locations.name, locations.building, locations.floor, locations.room')
            ->join(
                'locations',
                'locations.id = user_locations.location_id'
            )
            ->where('user_locations.user_id', $id)
            ->orderBy('locations.name', 'ASC')
            ->findAll();

        return view('users/show', [
            'title'     => 'Detail User',
            'user'      => $user,
            'roles'     => $roles,
            'locations' => $locations,
        ]);
    }
}
