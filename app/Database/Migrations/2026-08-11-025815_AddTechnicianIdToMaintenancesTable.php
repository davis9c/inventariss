<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTechnicianIdToMaintenancesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('maintenances', [
            'technician_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'technician_type',
            ],
        ]);

        $this->forge->addForeignKey(
            'technician_id',
            'users',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->forge->dropColumn(
            'maintenances',
            'technician_id'
        );
    }
}