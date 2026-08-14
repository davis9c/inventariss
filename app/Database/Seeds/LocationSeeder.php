<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $locations = [
            // Gedung A
            [
                'name'        => 'Ruang Administrasi',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 1',
                'room'        => 'A-101',
                'description' => 'Ruang administrasi',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Keuangan',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 1',
                'room'        => 'A-102',
                'description' => 'Ruang keuangan',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang SDM',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 1',
                'room'        => 'A-103',
                'description' => 'Ruang sumber daya manusia',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Rapat',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 1',
                'room'        => 'A-104',
                'description' => 'Ruang rapat',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang IT',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 2',
                'room'        => 'A-201',
                'description' => 'Ruang teknologi informasi',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Server',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 2',
                'room'        => 'A-202',
                'description' => 'Ruang server',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Arsip',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 2',
                'room'        => 'A-203',
                'description' => 'Ruang penyimpanan arsip',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Logistik',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 3',
                'room'        => 'A-301',
                'description' => 'Ruang logistik',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Pengadaan',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 3',
                'room'        => 'A-302',
                'description' => 'Ruang pengadaan barang',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Manajemen',
                'building'    => 'Gedung A',
                'floor'       => 'Lantai 4',
                'room'        => 'A-401',
                'description' => 'Ruang manajemen',
                'is_active'   => true,
            ],

            // Gedung B
            [
                'name'        => 'Ruang Operasional',
                'building'    => 'Gedung B',
                'floor'       => 'Lantai 1',
                'room'        => 'B-101',
                'description' => 'Ruang operasional',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Teknisi',
                'building'    => 'Gedung B',
                'floor'       => 'Lantai 2',
                'room'        => 'B-201',
                'description' => 'Ruang teknisi',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Workshop',
                'building'    => 'Gedung B',
                'floor'       => 'Lantai 3',
                'room'        => 'B-301',
                'description' => 'Ruang workshop dan perawatan',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Penyimpanan',
                'building'    => 'Gedung B',
                'floor'       => 'Lantai 4',
                'room'        => 'B-401',
                'description' => 'Ruang penyimpanan barang',
                'is_active'   => true,
            ],

            // Gedung C
            [
                'name'        => 'Ruang Keamanan',
                'building'    => 'Gedung C',
                'floor'       => 'Lantai 1',
                'room'        => 'C-101',
                'description' => 'Ruang keamanan',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Kebersihan',
                'building'    => 'Gedung C',
                'floor'       => 'Lantai 2',
                'room'        => 'C-201',
                'description' => 'Ruang kebersihan',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Meeting',
                'building'    => 'Gedung C',
                'floor'       => 'Lantai 3',
                'room'        => 'C-301',
                'description' => 'Ruang meeting',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ruang Pelatihan',
                'building'    => 'Gedung C',
                'floor'       => 'Lantai 4',
                'room'        => 'C-401',
                'description' => 'Ruang pelatihan',
                'is_active'   => true,
            ],
        ];

        $this->db->table('locations')->insertBatch($locations);
    }
}