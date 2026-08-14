<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\UserLocationModel;
use App\Models\RolePermissionModel;

class Auth extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function attempt()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();

        $user = $userModel
            ->where('username', $username)
            ->where('is_active', 1)
            ->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()
                ->back()
                ->with('error', 'Username atau password salah.');
        }

        // Ambil semua role user
        $userRoleModel = new UserRoleModel();

        $roles = $userRoleModel
            ->select('roles.id, roles.name')
            ->join(
                'roles',
                'roles.id = user_roles.role_id'
            )
            ->where(
                'user_roles.user_id',
                $user['id']
            )
            ->findAll();
        $userLocationModel = new UserLocationModel();

        $locations = $userLocationModel
            ->where('user_id', $user['id'])
            ->findAll();
        $locationIds = array_column($locations, 'location_id');
        $roleNames = array_column($roles, 'name');
        $roleIds   = array_column($roles, 'id');

        $rolePermissionModel = new RolePermissionModel();

        $permissions = $rolePermissionModel
            ->select('permissions.name')
            ->join(
                'permissions',
                'permissions.id = role_permissions.permission_id'
            )
            ->whereIn('role_permissions.role_id', $roleIds)
            ->findAll();

        $permissionNames = array_column($permissions, 'name');

        session()->set([
            'user_id'      => $user['id'],
            'username'     => $user['username'],
            'location_ids' => $locationIds,
            'name'         => $user['name'],
            'role_ids'     => $roleIds,
            'roles'        => $roleNames,
            'permissions'  => $permissionNames,
            'isLoggedIn'   => true,
        ]);

        //dd(session()->get());
        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
