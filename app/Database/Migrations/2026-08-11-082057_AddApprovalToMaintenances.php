<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApprovalToMaintenances extends Migration
{
    public function up()
    {
        $this->forge->addColumn('maintenances', [
            'approved_by' => [
                'type'       => 'INT',
                'null'       => true,
                'after'      => 'status',
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_by',
            ],
            'approval_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'approved_at',
            ],
        ]);
    }
    public function down()
    {
        $this->forge->dropColumn(
            'maintenances',
            [
                'approved_by',
                'approved_at',
                'approval_notes',
            ]
        );
    }
}
