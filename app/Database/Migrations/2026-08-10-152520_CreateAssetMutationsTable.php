<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetMutationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'asset_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'from_unit_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'to_unit_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'from_location_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'to_location_id' => [
                'type'     => 'INT',
                'unsigned'       => true,
                'null'           => true,
            ],
            'mutation_date' => [
                'type' => 'DATE',
            ],
            'reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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

        $this->forge->addForeignKey(
            'from_unit_id',
            'units',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'to_unit_id',
            'units',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'from_location_id',
            'locations',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'to_location_id',
            'locations',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->createTable('asset_mutations');
    }

    public function down()
    {
        $this->forge->dropTable('asset_mutations');
    }
}