<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKeluarPerusahaanAssetStatus extends Migration
{
    public function up()
    {
        // Tambah status untuk aset yang berada di luar tanggung jawab perusahaan.
        $this->forge->modifyColumn('assets', [
            'asset_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Aktif', 'Dipinjam', 'Maintenance', 'Tidak Digunakan', 'Keluar Perusahaan'],
                'default'    => 'Aktif',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('assets', [
            'asset_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Aktif', 'Dipinjam', 'Maintenance', 'Tidak Digunakan'],
                'default'    => 'Aktif',
            ],
        ]);
    }
}
