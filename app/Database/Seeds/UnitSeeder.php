<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run()
    {
        $units = [
            [
                'name'        => 'Teknologi Informasi',
                'code'        => 'IT',
                'description' => 'Mengelola infrastruktur dan layanan teknologi informasi.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Keuangan',
                'code'        => 'KEU',
                'description' => 'Mengelola administrasi dan keuangan.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Sumber Daya Manusia',
                'code'        => 'SDM',
                'description' => 'Mengelola sumber daya manusia.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Logistik',
                'code'        => 'LOG',
                'description' => 'Mengelola penyimpanan dan distribusi barang.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Pengadaan',
                'code'        => 'PROC',
                'description' => 'Mengelola proses pengadaan barang.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Operasional',
                'code'        => 'OPS',
                'description' => 'Mengelola kegiatan operasional.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Keamanan',
                'code'        => 'SEC',
                'description' => 'Mengelola keamanan lingkungan.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Manajemen',
                'code'        => 'MGT',
                'description' => 'Mengelola kebijakan dan pengambilan keputusan.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Pemeliharaan',
                'code'        => 'MTC',
                'description' => 'Mengelola pemeliharaan dan perbaikan aset.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Umum',
                'code'        => 'GEN',
                'description' => 'Mengelola kebutuhan umum organisasi.',
                'is_active'   => true,
            ],
        ];

        $this->db->table('units')->insertBatch($units);
    }
}