<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->seedRoles();
        $this->seedPermissions();
        $this->seedRolePermissions();
        $this->seedLocations();
        $this->seedCategories();
        $this->seedUnits();
    }

    private function seedRoles()
    {
        $roles = [
            [
                'name'        => 'Super Admin',
                'description' => 'Akses penuh ke seluruh sistem',
            ],
            [
                'name'        => 'Admin Inventaris',
                'description' => 'Mengelola data dan transaksi inventaris',
            ],
            [
                'name'        => 'Petugas Inventaris',
                'description' => 'Melakukan operasional inventaris',
            ],
            [
                'name'        => 'PIC Unit',
                'description' => 'Mengelola inventaris unit',
            ],
            [
                'name'        => 'Manajemen',
                'description' => 'Melihat laporan dan melakukan approval',
            ],
            [
                'name'        => 'Auditor',
                'description' => 'Melihat data dan audit log',
            ],
        ];

        $builder = $this->db->table('roles');

        $existingNames = array_column(
            $builder->select('name')->get()->getResultArray(),
            'name'
        );

        $inserts = array_filter(
            $roles,
            fn ($role) => !in_array($role['name'], $existingNames, true)
        );

        if ($inserts) {
            $builder->insertBatch($inserts);
        }
    }

    private function seedPermissions()
    {
        $permissions = [
            [
                'name'        => 'maintenance.view',
                'description' => 'Melihat data maintenance',
            ],
            [
                'name'        => 'maintenance.create',
                'description' => 'Membuat pengajuan maintenance',
            ],
            [
                'name'        => 'maintenance.update',
                'description' => 'Mengubah data maintenance',
            ],
            [
                'name'        => 'maintenance.delete',
                'description' => 'Menghapus data maintenance',
            ],
            [
                'name'        => 'maintenance.approve',
                'description' => 'Menyetujui atau menolak maintenance',
            ],
        ];

        $builder = $this->db->table('permissions');

        $existingNames = array_column(
            $builder->select('name')->get()->getResultArray(),
            'name'
        );

        $inserts = array_filter(
            $permissions,
            fn ($permission) => !in_array($permission['name'], $existingNames, true)
        );

        foreach ($inserts as $permission) {
            $builder->insert([
                ...$permission,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedRolePermissions()
    {
        $roleIds = [];

        foreach ($this->db->table('roles')->get()->getResultArray() as $role) {
            $roleIds[$role['name']] = $role['id'];
        }

        $permissionIds = [];

        foreach ($this->db->table('permissions')->get()->getResultArray() as $permission) {
            $permissionIds[$permission['name']] = $permission['id'];
        }

        $mappings = [];

        if (isset($roleIds['Super Admin'])) {
            foreach ($permissionIds as $permissionId) {
                $mappings[] = [$roleIds['Super Admin'], $permissionId];
            }
        }

        if (isset($roleIds['Teknisi'])) {
            foreach (['maintenance.view', 'maintenance.create', 'maintenance.update'] as $permission) {
                if (isset($permissionIds[$permission])) {
                    $mappings[] = [$roleIds['Teknisi'], $permissionIds[$permission]];
                }
            }
        }

        if (isset($roleIds['Manajemen'])) {
            foreach (['maintenance.view', 'maintenance.approve'] as $permission) {
                if (isset($permissionIds[$permission])) {
                    $mappings[] = [$roleIds['Manajemen'], $permissionIds[$permission]];
                }
            }
        }

        if (isset($roleIds['Admin Inventaris'])) {
            foreach (['maintenance.view', 'maintenance.create', 'maintenance.update'] as $permission) {
                if (isset($permissionIds[$permission])) {
                    $mappings[] = [$roleIds['Admin Inventaris'], $permissionIds[$permission]];
                }
            }
        }

        $builder = $this->db->table('role_permissions');

        $existing = array_map(
            fn ($row) => $row['role_id'] . '-' . $row['permission_id'],
            $builder->select('role_id, permission_id')->get()->getResultArray()
        );

        $inserts = array_filter(
            $mappings,
            fn ($mapping) => !in_array($mapping[0] . '-' . $mapping[1], $existing, true)
        );

        foreach ($inserts as $mapping) {
            $builder->insert([
                'role_id'       => $mapping[0],
                'permission_id' => $mapping[1],
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedLocations()
    {
        $locations = [
            [
                'name'        => 'Kantor Pusat',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 1',
                'room'        => 'A-101',
                'description' => 'Lokasi utama kantor pusat.',
            ],
            [
                'name'        => 'Kantor Cabang',
                'building'    => 'Gedung B',
                'floor'       => 'Lantai 1',
                'room'        => 'B-101',
                'description' => 'Lokasi kantor cabang.',
            ],
        ];

        $builder = $this->db->table('locations');

        $existingNames = array_column(
            $builder->select('name')->get()->getResultArray(),
            'name'
        );

        foreach ($locations as $location) {
            if (in_array($location['name'], $existingNames, true)) {
                continue;
            }

            $builder->insert([
                ...$location,
                'is_active'  => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedCategories()
    {
        $categories = [
            [
                'name'        => 'Perangkat Komputer',
                'description' => 'Komputer, laptop, printer, dan perangkat IT lainnya.',
            ],
            [
                'name'        => 'Perabotan & Sarana',
                'description' => 'Meja, kursi, rak, dan sarana kantor.',
            ],
            [
                'name'        => 'Keamanan',
                'description' => 'Perangkat keamanan seperti CCTV.',
            ],
            [
                'name'        => 'Peralatan IT & Jaringan',
                'description' => 'Perangkat jaringan seperti switch, router, kabel, dan aksesori IT.',
            ],
        ];

        $builder = $this->db->table('categories');

        $existingNames = array_column(
            $builder->select('name')->get()->getResultArray(),
            'name'
        );

        $inserts = array_filter(
            $categories,
            fn ($category) => !in_array($category['name'], $existingNames, true)
        );

        foreach ($inserts as $category) {
            $builder->insert([
                ...$category,
                'is_active'  => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedUnits()
    {
        $units = [
            [
                'name'        => 'Teknologi Informasi',
                'code'        => 'IT',
                'description' => 'Mengelola infrastruktur dan layanan teknologi informasi.',
            ],
            [
                'name'        => 'Keuangan',
                'code'        => 'KEU',
                'description' => 'Mengelola administrasi dan keuangan.',
            ],
            [
                'name'        => 'Sumber Daya Manusia',
                'code'        => 'SDM',
                'description' => 'Mengelola sumber daya manusia.',
            ],
            [
                'name'        => 'Logistik',
                'code'        => 'LOG',
                'description' => 'Mengelola penyimpanan dan distribusi barang.',
            ],
            [
                'name'        => 'Pengadaan',
                'code'        => 'PROC',
                'description' => 'Mengelola proses pengadaan barang.',
            ],
            [
                'name'        => 'Operasional',
                'code'        => 'OPS',
                'description' => 'Mengelola kegiatan operasional.',
            ],
            [
                'name'        => 'Keamanan',
                'code'        => 'SEC',
                'description' => 'Mengelola keamanan lingkungan.',
            ],
            [
                'name'        => 'Manajemen',
                'code'        => 'MGT',
                'description' => 'Mengelola kebijakan dan pengambilan keputusan.',
            ],
            [
                'name'        => 'Pemeliharaan',
                'code'        => 'MTC',
                'description' => 'Mengelola pemeliharaan dan perbaikan aset.',
            ],
            [
                'name'        => 'Umum',
                'code'        => 'GEN',
                'description' => 'Mengelola kebutuhan umum organisasi.',
            ],
        ];

        $builder = $this->db->table('units');

        $existingCodes = array_column(
            $builder->select('code')->get()->getResultArray(),
            'code'
        );

        $inserts = array_filter(
            $units,
            fn ($unit) => !in_array($unit['code'], $existingCodes, true)
        );

        foreach ($inserts as $unit) {
            $builder->insert([
                ...$unit,
                'is_active'  => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
