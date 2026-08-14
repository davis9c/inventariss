<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('roles')->insertBatch([
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
        ]);
    }
}