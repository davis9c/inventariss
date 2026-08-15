<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockTables extends Migration
{
    public function up()
    {
        // stock_items
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'item_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'category_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'unit_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'location_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'pcs',
            ],
            'quantity' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('unit_id', 'units', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('location_id', 'locations', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('stock_items');

        // stock_transactions
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'stock_item_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['Masuk', 'Keluar'],
            ],
            'quantity' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'transaction_date' => [
                'type' => 'DATE',
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
        $this->forge->addForeignKey('stock_item_id', 'stock_items', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('stock_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('stock_transactions');
        $this->forge->dropTable('stock_items');
    }
}
