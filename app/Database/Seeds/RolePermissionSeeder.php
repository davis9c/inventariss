<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $roleModel = $this->db->table('roles');
        $permissionModel = $this->db->table('permissions');
        $mapping = $this->db->table('role_permissions');

        $roles = $roleModel->get()->getResultArray();

        $permissions = $permissionModel
            ->get()
            ->getResultArray();

        $roleIds = [];

        foreach ($roles as $role) {
            $roleIds[$role['name']] = $role['id'];
        }

        $permissionIds = [];

        foreach ($permissions as $permission) {
            $permissionIds[$permission['name']] = $permission['id'];
        }

        /*
         * Super Admin
         * Semua permission
         */
        if (isset($roleIds['Super Admin'])) {

            foreach ($permissionIds as $permissionId) {

                $this->insertMapping(
                    $mapping,
                    $roleIds['Super Admin'],
                    $permissionId
                );
            }
        }

        /*
         * Teknisi
         */
        if (isset($roleIds['Teknisi'])) {

            $technicianPermissions = [
                'maintenance.view',
                'maintenance.create',
                'maintenance.update',
            ];

            foreach ($technicianPermissions as $permission) {

                if (isset($permissionIds[$permission])) {

                    $this->insertMapping(
                        $mapping,
                        $roleIds['Teknisi'],
                        $permissionIds[$permission]
                    );
                }
            }
        }

        /*
         * Manajemen
         */
        if (isset($roleIds['Manajemen'])) {

            $managementPermissions = [
                'maintenance.view',
                'maintenance.approve',
            ];

            foreach ($managementPermissions as $permission) {

                if (isset($permissionIds[$permission])) {

                    $this->insertMapping(
                        $mapping,
                        $roleIds['Manajemen'],
                        $permissionIds[$permission]
                    );
                }
            }
        }

        /*
         * Admin Inventaris
         */
        if (isset($roleIds['Admin Inventaris'])) {

            $adminPermissions = [
                'maintenance.view',
                'maintenance.create',
                'maintenance.update',
            ];

            foreach ($adminPermissions as $permission) {

                if (isset($permissionIds[$permission])) {

                    $this->insertMapping(
                        $mapping,
                        $roleIds['Admin Inventaris'],
                        $permissionIds[$permission]
                    );
                }
            }
        }
    }

    private function insertMapping($mapping, $roleId, $permissionId)
    {
        $exists = $mapping
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->get()
            ->getRow();

        if (!$exists) {

            $mapping->insert([
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
