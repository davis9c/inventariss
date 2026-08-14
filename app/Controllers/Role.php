<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\RolePermissionModel;


class Role extends BaseController
{
    protected RoleModel $roleModel;
    protected PermissionModel $permissionModel;
    protected RolePermissionModel $rolePermissionModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->rolePermissionModel = new RolePermissionModel();
    }

    private function checkSuperAdmin()
    {
        if (!isSuperAdmin()) {
            return redirect()
                ->to('/dashboard')
                ->with(
                    'error',
                    'Hanya Super Admin yang dapat mengakses manajemen role.'
                );
        }

        return null;
    }

    public function index()
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }

        $roles = $this->roleModel
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('roles/index', [
            'title' => 'Manajemen Role',
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }

        $permissionModel = new \App\Models\PermissionModel();

        return view('roles/create', [
            'title' => 'Tambah Role',

            'permissions' => $permissionModel
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function store()
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }

        $permissions = $this->request->getPost('permission_ids');

        $this->roleModel->insert([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        $roleId = $this->roleModel->getInsertID();

        if (!empty($permissions)) {
            foreach ($permissions as $permissionId) {
                $this->rolePermissionModel->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()
            ->to('/roles')
            ->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }

        $role = $this->roleModel->find($id);

        if (!$role) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rolePermissions = $this->rolePermissionModel
            ->where('role_id', $id)
            ->findAll();

        $permissionIds = array_column(
            $rolePermissions,
            'permission_id'
        );

        return view('roles/edit', [
            'title' => 'Edit Role',
            'role'  => $role,

            'permissions' => $this->permissionModel
                ->orderBy('name', 'ASC')
                ->findAll(),

            'permissionIds' => $permissionIds,
        ]);
    }

    public function update($id)
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }

        $role = $this->roleModel->find($id);

        if (!$role) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->roleModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Permission
        |--------------------------------------------------------------------------
        */

        $this->rolePermissionModel
            ->where('role_id', $id)
            ->delete();

        $permissions = $this->request->getPost('permission_ids');

        if (!empty($permissions)) {
            foreach ($permissions as $permissionId) {
                $this->rolePermissionModel->insert([
                    'role_id'       => $id,
                    'permission_id' => $permissionId,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()
            ->to('/roles')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }

        $role = $this->roleModel->find($id);

        if (!$role) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Hapus relasi permission terlebih dahulu
        $this->rolePermissionModel
            ->where('role_id', $id)
            ->delete();

        $this->roleModel->delete($id);

        return redirect()
            ->to('/roles')
            ->with('success', 'Role berhasil dihapus.');
    }
    public function show($id)
    {
        if ($response = $this->checkSuperAdmin()) {
            return $response;
        }

        $role = $this->roleModel->find($id);

        if (!$role) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $permissions = $this->rolePermissionModel
            ->select('permissions.name, permissions.description')
            ->join(
                'permissions',
                'permissions.id = role_permissions.permission_id'
            )
            ->where('role_permissions.role_id', $id)
            ->orderBy('permissions.name', 'ASC')
            ->findAll();

        return view('roles/show', [
            'title'       => 'Detail Role',
            'role'        => $role,
            'permissions' => $permissions,
        ]);
    }
}
