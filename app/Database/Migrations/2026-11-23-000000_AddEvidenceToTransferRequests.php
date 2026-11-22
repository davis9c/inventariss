<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEvidenceToTransferRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transfer_requests', [
            'evidence_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'notes',
            ],
            'evidence_original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'evidence_path',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transfer_requests', ['evidence_path', 'evidence_original_name']);
    }
}