<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;

class Setup extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();

        if ($userModel->countAllResults() > 0) {
            return redirect()->to('/login');
        }

        return view('setup/index');
    }
    public function create()
{
    $password = $this->request->getPost('password');
    $passwordConfirm = $this->request->getPost('password_confirm');

    if ($password !== $passwordConfirm) {
        return redirect()->back()->with('error', 'Password tidak sama.');
    }

    if (strlen($password) < 8) {
        return redirect()->back()->with('error', 'Password minimal 8 karakter.');
    }

    $roleModel = new RoleModel();
    $userModel = new UserModel();

    $superAdmin = $roleModel
        ->where('name', 'Super Admin')
        ->first();

    if (!$superAdmin) {
        return redirect()->back()->with('error', 'Role Super Admin belum ada. Jalankan seeder terlebih dahulu.');
    }

    $userModel->insert([
        'username'  => 'admin',
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

    return redirect()->to('/login');
}
}