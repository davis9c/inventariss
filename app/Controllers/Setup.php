<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use Config\Database;

class Setup extends BaseController
{
    public function index()
    {
        if (is_app_installed()) {
            return redirect()->to('/');
        }

        $dbConnected = false;
        $tablesExist = false;
        $hostname    = '';
        $database    = '';

        try {
            $db          = Database::connect();
            $dbConnected = true;

            $dbConfig = config('Database');
            $group    = $dbConfig->defaultGroup;

            $hostname = $dbConfig->{$group}['hostname'] ?? '';
            $database = $dbConfig->{$group}['database'] ?? '';
            $tablesExist = $db->tableExists('roles');
        } catch (Throwable $e) {
            $dbConnected = false;
        }

        return view('setup/index', [
            'title'       => 'Setup Sistem',
            'dbConnected' => $dbConnected,
            'tablesExist' => $tablesExist,
            'hostname'    => $hostname,
            'database'    => $database,
        ]);
    }

    public function create()
    {
        $username        = trim((string) $this->request->getPost('username'));
        $password        = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');

        if ($username === '' || strlen($username) > 50) {
            return redirect()->back()->with('error', 'Username wajib diisi (maksimal 50 karakter).');
        }

        if ($password !== $passwordConfirm) {
            return redirect()->back()->with('error', 'Password tidak sama.');
        }

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password minimal 8 karakter.');
        }

        try {
            $db = Database::connect();
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Koneksi database gagal. Periksa konfigurasi database di file .env.');
        }

        if (!$db->tableExists('roles')) {
            try {
                service('migrations')->latest();
            } catch (Throwable $e) {
                return redirect()->back()->with('error', 'Migrasi database gagal: ' . $e->getMessage());
            }
        }

        $roleModel  = new RoleModel();
        $superAdmin = $roleModel->where('name', 'Super Admin')->first();

        if (!$superAdmin) {
            try {
                $seeder = new \CodeIgniter\Database\Seeder(new \Config\Database());
                $seeder->call('DatabaseSeeder');
            } catch (Throwable $e) {
                return redirect()->back()->with('error', 'Pengisian data awal gagal: ' . $e->getMessage());
            }

            $superAdmin = $roleModel->where('name', 'Super Admin')->first();
        }

        if (!$superAdmin) {
            return redirect()->back()->with('error', 'Role Super Admin tidak ditemukan setelah pengisian data awal.');
        }

        $userModel = new UserModel();

        if ($userModel->where('username', $username)->first()) {
            return redirect()->back()->with('error', 'Username sudah terpakai.');
        }

        $userModel->insert([
            'username'  => $username,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'name'      => 'Super Admin',
            'is_active' => true,
        ]);

        $userId = $userModel->getInsertID();

        $userRoleModel = new UserRoleModel();
        $userRoleModel->insert([
            'user_id'    => $userId,
            'role_id'    => $superAdmin['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()
            ->to('/login')
            ->with('success', 'Instalasi selesai. Silakan masuk dengan akun Super Admin Anda.');
    }
}