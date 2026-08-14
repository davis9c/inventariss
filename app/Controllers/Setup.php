<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;

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

    $userModel->insert([
        'role_id'   => $superAdmin['id'],
        'username'  => 'admin',
        'password'  => password_hash($password, PASSWORD_DEFAULT),
        'name'      => 'Super Admin',
        'is_active' => true,
    ]);

    return redirect()->to('/login');
}
}