<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\UserLocationModel;
use App\Libraries\UserGateLibrary;

class Auth extends BaseController
{
    public function login()
    {
        // Jika tabel users belum ada (belum migrate), arahkan ke setup
        if (!\Config\Database::connect()->tableExists('users')) {
            return redirect()->to('/setup');
        }

        // Jika belum ada user lokal, arahkan ke setup
        $userModel = new UserModel();
        if ($userModel->countAllResults() === 0) {
            return redirect()->to('/setup');
        }

        return view('auth/login');
    }

    public function attempt()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return redirect()
                ->back()
                ->with('error', 'Username dan password wajib diisi.');
        }

        // ─── Login ke UserGate ───────────────────────────────
        $ug = new UserGateLibrary();
        $result = $ug->login($username, $password);

        if (!$result['status']) {
            return redirect()
                ->back()
                ->with('error', $result['message'] ?: 'Username atau password salah.');
        }

        $ugUser    = $result['data']['user'];
        $tokenData = $result['data'];

        // ─── Sync user lokal ─────────────────────────────────
        $userModel  = new UserModel();
        $localUser  = $userModel->syncFromUserGate($ugUser);

        // ─── Cek user pertama → Super Admin ──────────────────
        $totalUsers = $userModel->countAllResults();
        $userRoleModel = new UserRoleModel();

        if ($totalUsers === 1) {
            // Pastikan punya role Super Admin
            $this->ensureSuperAdminRole($localUser['id'], $userRoleModel);
        }

        // Jika UserGate admin → pastikan punya role Super Admin
        if (!empty($ugUser['is_super_admin'])) {
            $this->ensureSuperAdminRole($localUser['id'], $userRoleModel);
        }

        // ─── Ambil role lokal ────────────────────────────────
        $roles = $userRoleModel
            ->select('roles.id, roles.name')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $localUser['id'])
            ->findAll();

        $roleIds   = array_column($roles, 'id');
        $roleNames = array_column($roles, 'name');

        // Lokasi user
        $userLocationModel = new UserLocationModel();
        $locations = $userLocationModel
            ->where('user_id', $localUser['id'])
            ->findAll();
        $locationIds = array_column($locations, 'location_id');

        // ─── Set session ─────────────────────────────────────
        session()->set([
            'user_id'        => $localUser['id'],
            'username'       => $localUser['username'],
            'name'           => $localUser['name'],
            'location_ids'   => $locationIds,
            'role_ids'       => $roleIds,
            'roles'          => $roleNames,
            'isLoggedIn'     => true,
            'access_token'   => $tokenData['access_token'],
            'refresh_token'  => $tokenData['refresh_token'],
            'token_expiry'   => time() + ($tokenData['expires_in'] ?? 900),
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        // Fire-and-forget revoke token di UserGate
        $accessToken = session()->get('access_token');
        if ($accessToken) {
            $ug = new UserGateLibrary();
            $ug->logout($accessToken);
        }

        session()->destroy();

        return redirect()->to('/login');
    }

    /**
     * Pastikan user punya role Super Admin.
     */
    private function ensureSuperAdminRole(
        int $userId,
        UserRoleModel $userRoleModel
    ): void {
        $roleModel = new \App\Models\RoleModel();
        $superAdmin = $roleModel->where('name', 'Super Admin')->first();

        if (!$superAdmin) {
            return;
        }

        $exists = $userRoleModel
            ->where('user_id', $userId)
            ->where('role_id', $superAdmin['id'])
            ->first();

        if (!$exists) {
            $userRoleModel->insert([
                'user_id'    => $userId,
                'role_id'    => $superAdmin['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
