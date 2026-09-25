<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Libraries\UserGateLibrary;

class Setup extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $tableExists = $db->tableExists('users');

        // Jika tabel ada dan sudah ada user, redirect ke login
        if ($tableExists) {
            $userModel = new UserModel();
            if ($userModel->countAllResults() > 0) {
                return redirect()->to('/login');
            }
        }

        return view('setup/index', [
            'tableExists' => $tableExists,
        ]);
    }

    public function create()
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
        $userModel = new UserModel();
        $localUser = $userModel->syncFromUserGate($ugUser);

        // ─── Assign Super Admin ──────────────────────────────
        $roleModel = new RoleModel();
        $superAdmin = $roleModel->where('name', 'Super Admin')->first();

        if ($superAdmin) {
            $userRoleModel = new UserRoleModel();
            $exists = $userRoleModel
                ->where('user_id', $localUser['id'])
                ->where('role_id', $superAdmin['id'])
                ->first();

            if (!$exists) {
                $userRoleModel->insert([
                    'user_id'    => $localUser['id'],
                    'role_id'    => $superAdmin['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // ─── Ambil role ──────────────────────────────────────
        $userRoleModel = new \App\Models\UserRoleModel();
        $roles = $userRoleModel
            ->select('roles.id, roles.name')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $localUser['id'])
            ->findAll();

        $roleIds   = array_column($roles, 'id');
        $roleNames = array_column($roles, 'name');

        // Lokasi
        $userLocationModel = new \App\Models\UserLocationModel();
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
}
