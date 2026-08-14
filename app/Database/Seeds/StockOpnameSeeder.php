<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StockOpnameSeeder extends Seeder
{
    public function run()
    {
        $assetModel = new \App\Models\AssetModel();

        $asset = $assetModel->first();

        if (!$asset) {
            throw new \RuntimeException(
                'Jalankan AssetSeeder terlebih dahulu.'
            );
        }

        $builder = $this->db->table('stock_opnames');

        $builder->insert([
            'opname_code' => 'SO-DEMO-001',
            'opname_date' => date('Y-m-d'),
            'location_id' => $asset['location_id'],
            'status'      => 'Draft',
            'notes'       => 'Stock opname untuk testing.',
        ]);

        $opnameId = $this->db->insertID();

        $assets = $assetModel
            ->where('location_id', $asset['location_id'])
            ->findAll();

        foreach ($assets as $item) {
            $this->db->table('stock_opname_details')->insert([
                'stock_opname_id'  => $opnameId,
                'asset_id'         => $item['id'],
                'is_found'         => true,
                'condition_status' => $item['condition_status'],
                'notes'            => null,
            ]);
        }
    }
}