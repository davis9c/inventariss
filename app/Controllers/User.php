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
    /**
     * Role yang boleh diberikan oleh pemanggil.
     *
     * Super Admin boleh semuanya; selain itu hanya role dengan level lebih
     * besar dari level caller (privilege lebih rendah). Dihitung di server
     * supaya view tidak pernah jadi penentu aturan.
     *
     * @return array<int, array>
     */
    private function assignableRoles(): array
    {
        if ($this->isSuperAdmin()) {
            return $this->roleModel->orderBy('name', 'ASC')->findAll();
        }

        $callerLevel = $this->currentRoleLevel();

        return array_values(array_filter(
            $this->roleModel->orderBy('name', 'ASC')->findAll(),
            static fn (array $r): bool => (int) ($r['level'] ?? 1) > $callerLevel
        ));
    }

    public function index()
    {
        // DataTables server-side.
        //
        // Daftar user TIDAK berasal dari tabel lokal: sumbernya adalah
        // API UserGate yang sudah terpaginasikan. Jadi di sini tidak
        // memakai datatableResponse(), melainkan meneruskan paging dan
        // pencarian DataTables ke UserGate. Konsekuensinya: UserGate
        // tidak menyediakan parameter sort, sehingga kolom users
        // sengaja dibuat orderable:false di view. Mengurutkan diam-diam
        // hanya di halaman saat ini akan menyesatkan.
        if ($this->request->getGet('format') === 'json') {
            $length = (int) ($this->request->getGet('length') ?: 25);
            $start  = (int) ($this->request->getGet('start') ?: 0);

            // length=-1 berarti "tampilkan semua"; UserGate butuh
            // per_page numerik, jadi pakai batas atas yang masuk akal.
            $perPage = $length > 0 ? $length : 200;
            $page    = $length > 0 ? intdiv($start, $length) + 1 : 1;

            $search = '';
            $searchParam = $this->request->getGet('search');
            if (is_array($searchParam) && isset($searchParam['value'])) {
                $search = trim((string) $searchParam['value']);
            }

            $result   = $this->ug()->getUsers($this->token(), $page, $perPage, $search);
            $ugUsers  = $result['data'] ?? [];
            $meta     = $result['meta'] ?? [];
            $total    = (int) ($meta['total'] ?? count($ugUsers));

            return $this->respondAjax([
                'draw'            => (int) ($this->request->getGet('draw') ?: 1),
                // Pencarian juga dijalankan di UserGate, jadi total dan
                // filtered selalu sama untuk permintaan ini.
                'recordsTotal'    => $total,
                'recordsFiltered' => $total,
                'data'            => $this->userRows($ugUsers),
            ]);
        }

        $result  = $this->ug()->getUsers($this->token(), 1, 25, '');
        $ugUsers = $result['data'] ?? [];

        return view('users/index', [
            'title' => 'User Management',
            // Dipakai oleh checkbox di modal create/edit.
            'assignableRoles' => $this->assignableRoles(),
            'locations'       => $this->locationModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    /**
     * Gabungkan data UserGate dengan data lokal (tabel users) dan role
     * yang Assign. Dipakai oleh path HTML maupun DataTables JSON.
     *
     * Query role dibuat satu kali untuk seluruh baris, bukan satu per
     * user, supaya tidak N+1 saat tabel menampilkan 25 baris.
     *
     * @param array<int, array> $ugUsers
     * @return array<int, array>
     */
    private function userRows(array $ugUsers): array
    {
        if ($ugUsers === []) {
            return [];
        }

        $users = [];

        foreach ($ugUsers as $ugUser) {
            $local = $this->userModel->syncFromUserGate($ugUser);

            $users[] = array_merge($local, [
                'ug_data' => $ugUser,
                'roles'   => [],
            ]);
        }

        $ids = array_map(static fn ($u) => (int) $u['id'], $users);
        $ids = array_values(array_filter($ids, static fn ($v) => $v > 0));

        if ($ids === []) {
            return $users;
        }

        $byUser = [];

        foreach ($this->userRoleModel
            ->select('user_roles.user_id, roles.name')
            ->join('roles', 'roles.id = user_roles.role_id', 'left')
            ->whereIn('user_roles.user_id', $ids)
            ->orderBy('roles.name', 'ASC')
            ->findAll() as $row) {
            $byUser[(int) $row['user_id']][] = ['name' => $row['name']];
        }

        foreach ($users as $i => $u) {
            $users[$i]['roles'] = $byUser[(int) $u['id']] ?? [];
        }

        return $users;
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
        // Form create berada di modal pada halaman index.
        return redirect()->to('/users?create=1');
    }

    /**
     * Simpan user baru → proxy ke UserGate POST /users.
     */
    public function store()
    {
        $isAjax = $this->request->isAJAX();

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
            if ($isAjax) {
                return $this->respondErrors(
                    'Data tidak valid.',
                    $this->validator->getErrors()
                );
            }

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
            if ($isAjax) {
                return $this->respondError(
                    $result['message'] ?: 'Gagal membuat user.',
                    422
                );
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result['message'] ?: 'Gagal membuat user.');
        }

        // Sync ke lokal
        $synced = $this->userModel->syncFromUserGate($result['data']);

        if ($isAjax) {
            return $this->respondSuccess('User berhasil ditambahkan.', [
                'id' => (int) ($synced['id'] ?? 0),
            ]);
        }

        return redirect()
            ->to('/users')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Form edit user — atur role + lokasi lokal.
     */
    /**
     * Satu user sebagai JSON, untuk mengisi modal edit dari ?edit=<id>.
     *
     * Menggabungkan data lokal (tabel users) dengan UserGate dan role
     * yang boleh diberikan oleh pemanggil.
     */
    public function data($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->respondError('User tidak ditemukan.', 404);
        }

        $ugUser = null;
        if (! empty($user['usergate_id'])) {
            $result = $this->ug()->getUser($this->token(), $user['usergate_id']);
            if ($result['status']) {
                $ugUser = $result['data'];
            }
        }

        $superAdminRole = $this->roleModel->where('name', 'Super Admin')->first();

        // Bentuk yang sama dengan User::show(), supaya modal detail
        // menampilkan isi yang identik dengan halaman detail.
        $roles = $this->userRoleModel
            ->select('roles.name, roles.description')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $id)
            ->orderBy('roles.name', 'ASC')
            ->findAll();

        $locations = $this->userLocationModel
            ->select('locations.name, locations.building, locations.floor, locations.room')
            ->join('locations', 'locations.id = user_locations.location_id')
            ->where('user_locations.user_id', $id)
            ->orderBy('locations.name', 'ASC')
            ->findAll();

        return $this->respondAjax([
            'id'               => (int) $user['id'],
            'username'         => $user['username'],
            'name'             => $user['name'],
            'is_active'        => (int) $user['is_active'],
            'ug_data'          => $ugUser,
            'role_ids'         => array_map('intval', array_column(
                $this->userRoleModel->where('user_id', $id)->findAll(),
                'role_id'
            )),
            'location_ids'     => array_map('intval', array_column(
                $this->userLocationModel->where('user_id', $id)->findAll(),
                'location_id'
            )),
            'roles'            => $roles,
            'locations'        => $locations,
            'assignable_roles' => $this->assignableRoles(),
            'is_super_admin'   => $this->isSuperAdmin(),
            'super_role_id'    => $superAdminRole ? (int) $superAdminRole['id'] : 0,
        ]);
    }

    public function edit($id)
    {
        // Form edit berada di modal pada halaman index. Role TIDAK lagi
        // diubah lewat halaman ini: peran itu milik
        // User::updateRoles(), yang melakukan validasi hierarchy dan
        // proteksi Super Admin. Keberadaan record divalidasi di data().
        return redirect()->to('/users?edit=' . (int) $id);
    }

    /**
     * Update user — proxy ke UserGate PUT /users/{id} + update role/lokasi lokal.
     */
    public function update($id)
    {
        $isAjax = $this->request->isAJAX();

        $user = $this->userModel->find($id);

        if (!$user) {
            if ($isAjax) {
                return $this->respondError('User tidak ditemukan.', 404);
            }

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

        // CATATAN: role TIDAK lagi diubah di sini. Perubahan role hanya
        // boleh dilakukan Super Admin lewat User::updateRoles(), yang
        // melakukan validasi dan proteksi Super Admin. Jangan kembalikan
        // blok role ke method ini.

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

        if ($isAjax) {
            return $this->respondSuccess('User berhasil diperbarui.', [
                'id' => (int) $id,
            ]);
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
            if ($this->request->isAJAX()) {
                return $this->respondError('User tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $error = $this->request->isAJAX()
            ? fn (string $m) => $this->respondError($m, 422)
            : fn (string $m) => redirect()->back()->with('error', $m);

        // Jangan sampai user menghapus akunnya sendiri.
        if ((int) session()->get('user_id') === (int) $id) {
            return $error('Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Menghapus user yang memegang role Super Admin harus Super Admin.
        $superAdmin = $this->roleModel->where('name', 'Super Admin')->first();

        if ($superAdmin) {
            $superId = (int) $superAdmin['id'];

            $targetIsSuper = (bool) $this->userRoleModel
                ->where('user_id', $id)
                ->where('role_id', $superId)
                ->first();

            if ($targetIsSuper && !$this->isSuperAdmin()) {
                $guard = $this->requireSuperAdmin();
                if ($guard) {
                    return $guard;
                }
            }

            // Jangan hapus Super Admin terakhir.
            if ($targetIsSuper) {
                $others = $this->userRoleModel
                    ->select('user_id')
                    ->where('role_id', $superId)
                    ->where('user_id !=', $id)
                    ->countAllResults();

                if ($others === 0) {
                    return $error(
                        'Tidak dapat menghapus Super Admin terakhir. '
                        . 'Tunjuk Super Admin lain terlebih dahulu.'
                    );
                }
            }
        }

        // Hapus di UserGate (jika ada usergate_id)
        if (!empty($user['usergate_id'])) {
            $this->ug()->deleteUser($this->token(), $user['usergate_id']);
        }

        // Hapus relasi lokal
        $this->userRoleModel->where('user_id', $id)->delete();
        $this->userLocationModel->where('user_id', $id)->delete();
        $this->userModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->respondSuccess('User berhasil dihapus.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()
            ->to('/users')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Update role lokal (POST).
     *
     * SATU-SATUNYA tempat input user sampai ke tabel user_roles.
     * Semua aturan berikut dijaga di sini, bukan hanya di route:
     *   1. Admin Inventaris BOLEH menetapkan role.
     *   2. Hanya Super Admin yang boleh memberikan ATAU mencabut
     *      role Super Admin.
     *   3. role_id harus benar-benar ada di tabel roles.
     *   4. Minimal satu role.
     *   5. Super Admin terakhir tidak boleh kehilangan role-nya.
     *   6. Delete + insert dibungkus transaksi.
     */
    public function updateRoles($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            if ($this->request->isAJAX()) {
                return $this->respondError('User tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $error = $this->request->isAJAX()
            ? fn (string $m) => $this->respondError($m, 422)
            : fn (string $m) => redirect()->back()->with('error', $m);

        // ─── Validasi role_id terhadap tabel roles ───────────
        $posted = $this->request->getPost('role_ids');
        $posted = is_array($posted) ? $posted : [];

        $roleIds = array_values(array_unique(array_map(
            static fn ($v) => (int) $v,
            $posted
        )));

        $roleIds = array_values(array_filter($roleIds, static fn ($v) => $v > 0));

        if (empty($roleIds)) {
            return $error('Pilih minimal satu role untuk user ini.');
        }

        $validRoleIds = array_column(
            $this->roleModel->select('id, name, level')->whereIn('id', $roleIds)->findAll(),
            'id'
        );
        $validRoleIds = array_map('intval', $validRoleIds);

        $unknown = array_diff($roleIds, $validRoleIds);
        if (!empty($unknown)) {
            return $error('Role tidak dikenal: ' . implode(', ', $unknown) . '.');
        }

        // ─── Hierarki: hanya boleh memberikan role di bawahnya ─
        // Level kecil = privilege tinggi. Super Admin (level 1) boleh
        // memberikan role apa pun; selain itu hanya role dengan level
        // LEBIH BESAR dari level caller (yaitu privilege lebih rendah).
        if (! $this->isSuperAdmin()) {
            $callerLevel = $this->currentRoleLevel();

            $tooHigh = $this->roleModel
                ->select('name')
                ->whereIn('id', $validRoleIds)
                ->where('level <=', $callerLevel)
                ->findAll();

            if (! empty($tooHigh)) {
                return $error(
                    'Anda hanya dapat memberikan role di bawah level Anda sendiri. '
                    . 'Tidak diizinkan: ' . implode(', ', array_column($tooHigh, 'name')) . '.'
                );
            }
        }

        // ─── Proteksi Super Admin ───────────────────────────
        $superAdmin = $this->roleModel->where('name', 'Super Admin')->first();
        $superId    = $superAdmin ? (int) $superAdmin['id'] : 0;

        $targetIsSuper = $superId > 0 && (bool) $this->userRoleModel
            ->where('user_id', $id)
            ->where('role_id', $superId)
            ->first();

        $willBeSuper = $superId > 0 && in_array($superId, $validRoleIds, true);

        // Role Super Admin bersifat mutlak: hanya Super Admin yang boleh
        // memberikan ATAU mencabut role ini. Admin Inventaris boleh
        // menentukan role lain, tapi tidak boleh menyentuh role ini —
        // termasuk tidak boleh mengubah role user yang sudah Super Admin
        // (karena itu berarti mencabut Super Admin-nya).
        if (!$this->isSuperAdmin() && ($targetIsSuper || $willBeSuper)) {
            $guard = $this->requireSuperAdmin(
                'Hanya Super Admin yang dapat memberikan atau mencabut role Super Admin.'
            );

            return $guard ?? $error(
                'Hanya Super Admin yang dapat memberikan atau mencabut role Super Admin.'
            );
        }

        // Super Admin terakhir tidak boleh kehilangan role-nya.
        if ($superId > 0 && $targetIsSuper && !$willBeSuper) {
            $others = $this->userRoleModel
                ->select('user_id')
                ->where('role_id', $superId)
                ->where('user_id !=', $id)
                ->countAllResults();

            if ($others === 0) {
                return $error(
                    'Tidak dapat mencabut role Super Admin terakhir. '
                    . 'Tunjuk Super Admin lain terlebih dahulu.'
                );
            }
        }

        // ─── Simpan (transaksi: delete + insert) ────────────
        $now = date('Y-m-d H:i:s');

        db_connect()->transStart();

        $this->userRoleModel->where('user_id', $id)->delete();

        foreach ($validRoleIds as $roleId) {
            $this->userRoleModel->insert([
                'user_id'    => (int) $id,
                'role_id'    => $roleId,
                'created_at' => $now,
            ]);
        }

        if (!db_connect()->transStatus()) {
            db_connect()->transRollback();

            return $error('Gagal menyimpan role. Perubahan tidak disimpan.');
        }

        db_connect()->transCommit();

        if ($this->request->isAJAX()) {
            return $this->respondSuccess('Role user berhasil diperbarui.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()
            ->to('/users')
            ->with('success', 'Role user berhasil diperbarui.');
    }

    /**
     * Update lokasi lokal saja (POST).
     */
    public function updateLocations($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            if ($this->request->isAJAX()) {
                return $this->respondError('User tidak ditemukan.', 404);
            }

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

        if ($this->request->isAJAX()) {
            return $this->respondSuccess('Lokasi user berhasil diperbarui.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Lokasi user berhasil diperbarui.');
    }
}
