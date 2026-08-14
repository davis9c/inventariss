<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserLocationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
            ],
            'location_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['user_id', 'location_id'], true);

        $this->forge->addForeignKey(
            'user_id',
            'users',
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

        $this->forge->createTable('user_locations');
    }

    public function down()
    {
        $this->forge->dropTable('user_locations');
    }
}
