<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsergateIdToUsers extends Migration
{
    public function up()
    {
        // Tambah kolom usergate_id
        $this->forge->addColumn('users', [
            'usergate_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        // Ubah password jadi nullable (auth via UserGate)
        $this->forge->modifyColumn('users', [
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        // Index untuk pencarian cepat
        $this->db->query(
            'ALTER TABLE users ADD INDEX idx_usergate_id (usergate_id)'
        );
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['usergate_id']);

        $this->forge->modifyColumn('users', [
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);
    }
}
