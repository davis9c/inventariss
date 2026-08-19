<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitsToInventoryTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('inventory_transactions', [
            'from_unit_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'from_location_id',
            ],
            'to_unit_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'to_location_id',
            ],
        ]);

        $db = \Config\Database::connect();

        $db->query('ALTER TABLE inventory_transactions ADD CONSTRAINT fk_inventory_transactions_from_unit FOREIGN KEY (from_unit_id) REFERENCES units (id) ON DELETE SET NULL ON UPDATE CASCADE');
        $db->query('ALTER TABLE inventory_transactions ADD CONSTRAINT fk_inventory_transactions_to_unit FOREIGN KEY (to_unit_id) REFERENCES units (id) ON DELETE SET NULL ON UPDATE CASCADE');

        $this->backfill();
    }

    private function backfill(): void
    {
        $db = \Config\Database::connect();

        // 1. Mutasi aset: cocokkan ke asset_mutations lama (yang punya from/to_unit_id)
        $mutations = $db->table('asset_mutations')
            ->where('from_unit_id IS NOT NULL OR to_unit_id IS NOT NULL')
            ->get()
            ->getResultArray();

        $matchByKey = [];

        foreach ($mutations as $mutation) {
            $matchByKey[$this->mutationKey($mutation)] = $mutation;
        }

        $transactionRows = $db->table('inventory_transactions')
            ->where('item_type', 'Aset')
            ->where('transaction_type', 'Mutasi')
            ->where('from_unit_id IS NULL')
            ->where('to_unit_id IS NULL')
            ->get()
            ->getResultArray();

        foreach ($transactionRows as $transaction) {
            $fromUnit = null;
            $toUnit   = null;

            $key = $this->mutationKey([
                'asset_id'       => $transaction['asset_id'],
                'mutation_date'  => $transaction['transaction_date'],
                'reason'         => $transaction['reason'],
                'notes'          => $transaction['notes'],
            ]);

            if (isset($matchByKey[$key])) {
                $fromUnit = $matchByKey[$key]['from_unit_id'];
                $toUnit   = $matchByKey[$key]['to_unit_id'];
            }

            if (!$fromUnit || !$toUnit) {
                $asset = $db->table('assets')
                    ->select('unit_id')
                    ->where('id', $transaction['asset_id'])
                    ->get()
                    ->getRowArray();

                $currentUnit = $asset ? $asset['unit_id'] : null;

                $fromUnit = $fromUnit ?: $currentUnit;
                $toUnit   = $toUnit ?: $currentUnit;
            }

            $db->table('inventory_transactions')
                ->where('id', $transaction['id'])
                ->update([
                    'from_unit_id' => $fromUnit,
                    'to_unit_id'   => $toUnit,
                ]);
        }

        // 2. Transaksi stok & aset lainnya: isi dari master data saat ini
        $types = [
            'Pindah'   => 'both',
            'Masuk'    => 'to',
            'Perolehan' => 'to',
            'Pengembalian' => 'to',
            'Penyesuaian Naik' => 'to',
            'Keluar'   => 'from',
            'Keluar Perusahaan' => 'from',
            'Penyesuaian Turun' => 'from',
        ];

        foreach ($types as $type => $direction) {
            $rows = $db->table('inventory_transactions')
                ->where('transaction_type', $type)
                ->where('from_unit_id IS NULL')
                ->where('to_unit_id IS NULL')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $unit = $this->itemUnit($db, $row['item_type'], $row['asset_id'], $row['stock_item_id']);

                if (!$unit) {
                    continue;
                }

                $update = [];

                if ($direction === 'both') {
                    $update['from_unit_id'] = $unit;
                    $update['to_unit_id']   = $unit;
                } elseif ($direction === 'to') {
                    $update['to_unit_id'] = $unit;
                } else {
                    $update['from_unit_id'] = $unit;
                }

                $db->table('inventory_transactions')
                    ->where('id', $row['id'])
                    ->update($update);
            }
        }
    }

    private function mutationKey(array $row): string
    {
        return implode('|', [
            (string) ($row['asset_id'] ?? ''),
            (string) ($row['mutation_date'] ?? ''),
            (string) ($row['reason'] ?? ''),
            (string) ($row['notes'] ?? ''),
        ]);
    }

    private function itemUnit($db, string $itemType, ?int $assetId, ?int $stockItemId): ?int
    {
        if ($itemType === 'Aset' && $assetId) {
            $row = $db->table('assets')
                ->select('unit_id')
                ->where('id', $assetId)
                ->get()
                ->getRowArray();

            return $row ? (int) $row['unit_id'] : null;
        }

        if ($itemType === 'Barang Stok' && $stockItemId) {
            $row = $db->table('stock_items')
                ->select('unit_id')
                ->where('id', $stockItemId)
                ->get()
                ->getRowArray();

            return $row ? (int) $row['unit_id'] : null;
        }

        return null;
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $db->query('ALTER TABLE inventory_transactions DROP FOREIGN KEY fk_inventory_transactions_to_unit');
        $db->query('ALTER TABLE inventory_transactions DROP FOREIGN KEY fk_inventory_transactions_from_unit');
        $this->forge->dropColumn('inventory_transactions', ['from_unit_id', 'to_unit_id']);
    }
}