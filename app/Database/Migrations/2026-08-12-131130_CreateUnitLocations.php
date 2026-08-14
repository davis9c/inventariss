<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnitLocations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'unit_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],

            'location_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addUniqueKey([
            'unit_id',
            'location_id',
        ]);

        $this->forge->addForeignKey(
            'unit_id',
            'units',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'location_id',
            'locations',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('unit_locations');
    }

    public function down()
    {
        $this->forge->dropTable('unit_locations');
    }
}
