<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
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

        foreach ($permissions as $permission) {
            $exists = $this->db
                ->table('permissions')
                ->where('name', $permission['name'])
                ->get()
                ->getRow();

            if (!$exists) {
                $this->db->table('permissions')->insert([
                    ...$permission,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
