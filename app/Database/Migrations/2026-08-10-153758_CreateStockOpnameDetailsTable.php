<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockOpnameDetailsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'stock_opname_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'asset_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'is_found' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'condition_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Baik', 'Rusak Ringan', 'Rusak Berat'],
                'default'    => 'Baik',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'checked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey(
            'stock_opname_id',
            'stock_opnames',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'asset_id',
            'assets',
            'id',
            'CASCADE',
            'RESTRICT'
        );

        $this->forge->createTable('stock_opname_details');
    }

    public function down()
    {
        $this->forge->dropTable('stock_opname_details');
    }
}