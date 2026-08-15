<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockOpnameStockDetails extends Migration
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
            'stock_item_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'system_qty' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'physical_qty' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
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
        $this->forge->addUniqueKey(['stock_opname_id', 'stock_item_id']);
        $this->forge->addForeignKey('stock_opname_id', 'stock_opnames', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('stock_item_id', 'stock_items', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('stock_opname_stock_details');
    }

    public function down()
    {
        $this->forge->dropTable('stock_opname_stock_details');
    }
}
