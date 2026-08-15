<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryTransactions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'transaction_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'transaction_date' => [
                'type' => 'DATE',
            ],
            'transaction_type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'Masuk',
                    'Keluar',
                    'Pindah',
                    'Penyesuaian Naik',
                    'Penyesuaian Turun',
                    'Perolehan',
                    'Mutasi',
                    'Keluar Perusahaan',
                    'Pengembalian',
                ],
            ],
            'item_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Aset', 'Barang Stok'],
            ],
            'asset_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'stock_item_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'quantity' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
            ],
            'from_location_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'to_location_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'reference_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
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
        $this->forge->addKey('transaction_date');
        $this->forge->addKey('transaction_type');
        $this->forge->addKey('item_type');
        $this->forge->addKey('asset_id');
        $this->forge->addKey('stock_item_id');
        $this->forge->addForeignKey('asset_id', 'assets', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('stock_item_id', 'stock_items', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('from_location_id', 'locations', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('to_location_id', 'locations', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('inventory_transactions');

        $this->backfill();
    }

    private function backfill(): void
    {
        $db = \Config\Database::connect();

        $counter = 0;

        // Salin histori mutasi aset lama (asset_mutations)
        $mutations = $db->table('asset_mutations')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($mutations as $mutation) {
            $counter++;

            $db->table('inventory_transactions')->insert([
                'transaction_code' => 'TRX' . str_pad((string) $counter, 8, '0', STR_PAD_LEFT),
                'transaction_date' => $mutation['mutation_date'],
                'transaction_type' => 'Mutasi',
                'item_type'        => 'Aset',
                'asset_id'         => $mutation['asset_id'],
                'quantity'         => 1,
                'from_location_id' => $mutation['from_location_id'],
                'to_location_id'   => $mutation['to_location_id'],
                'reason'           => $mutation['reason'],
                'notes'            => $mutation['notes'],
                'created_by'       => $mutation['created_by'],
                'created_at'       => $mutation['created_at'],
                'updated_at'       => $mutation['updated_at'],
            ]);
        }

        // Salin histori transaksi stok lama (stock_transactions)
        $locationCache = [];

        $transactions = $db->table('stock_transactions')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($transactions as $transaction) {
            $counter++;

            if (!isset($locationCache[$transaction['stock_item_id']])) {
                $item = $db->table('stock_items')
                    ->select('location_id')
                    ->where('id', $transaction['stock_item_id'])
                    ->get()
                    ->getRowArray();

                $locationCache[$transaction['stock_item_id']] = $item ? $item['location_id'] : null;
            }

            $locationId = $locationCache[$transaction['stock_item_id']];

            $db->table('inventory_transactions')->insert([
                'transaction_code' => 'TRX' . str_pad((string) $counter, 8, '0', STR_PAD_LEFT),
                'transaction_date' => $transaction['transaction_date'],
                'transaction_type' => $transaction['type'],
                'item_type'        => 'Barang Stok',
                'stock_item_id'    => $transaction['stock_item_id'],
                'quantity'         => $transaction['quantity'],
                'from_location_id' => $transaction['type'] === 'Keluar' ? $locationId : null,
                'to_location_id'   => $transaction['type'] === 'Masuk' ? $locationId : null,
                'notes'            => $transaction['notes'],
                'created_by'       => $transaction['created_by'],
                'created_at'       => $transaction['created_at'],
                'updated_at'       => $transaction['updated_at'],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('inventory_transactions');
    }
}
