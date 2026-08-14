<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaintenancesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'maintenance_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],

            'asset_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],

            'maintenance_date' => [
                'type' => 'DATE',
            ],

            'maintenance_type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'Preventive',
                    'Corrective',
                    'Inspection',
                ],
            ],

            'problem' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'action_taken' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'technician_type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'Internal',
                    'External',
                ],
            ],

            'technician_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],

            'vendor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],

            'cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'Diajukan',
                    'Diproses',
                    'Selesai',
                    'Dibatalkan',
                ],
                'default' => 'Diajukan',
            ],

            'completed_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'created_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey(
            'asset_id',
            'assets',
            'id',
            'CASCADE',
            'RESTRICT'
        );

        $this->forge->createTable('maintenances');
    }

    public function down()
    {
        $this->forge->dropTable('maintenances');
    }
}