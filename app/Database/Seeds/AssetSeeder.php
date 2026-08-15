<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run()
    {
        $categoryModel = new \App\Models\CategoryModel();
        $unitModel     = new \App\Models\UnitModel();
        $locationModel = new \App\Models\LocationModel();

        $categories = $categoryModel->findAll();
        $units      = $unitModel->findAll();
        $locations  = $locationModel->findAll();

        if (empty($categories) || empty($units) || empty($locations)) {
            throw new \RuntimeException(
                'Seeder kategori, unit, dan lokasi harus dijalankan terlebih dahulu.'
            );
        }

        $locationCount = count($locations);

        $assets = [
            [
                'asset_code'        => 'AST-0001',
                'name'              => 'Laptop',
                'category_id'       => $categories[0]['id'],
                'unit_id'           => $units[0]['id'],
                'location_id'       => $locations[0 % $locationCount]['id'],
                'brand'             => 'Lenovo',
                'model'             => 'ThinkPad',
                'serial_number'     => 'SN-LTP-0001',
                'acquisition_year'  => 2025,
                'acquisition_price' => 12500000,
                'condition_status'  => 'Baik',
                'asset_status'      => 'Aktif',
                'description'       => 'Laptop operasional.',
            ],
            [
                'asset_code'        => 'AST-0002',
                'name'              => 'Desktop PC',
                'category_id'       => $categories[0]['id'],
                'unit_id'           => $units[0]['id'],
                'location_id'       => $locations[5 % $locationCount]['id'],
                'brand'             => 'Dell',
                'model'             => 'OptiPlex',
                'serial_number'     => 'SN-PC-0001',
                'acquisition_year'  => 2024,
                'acquisition_price' => 9500000,
                'condition_status'  => 'Baik',
                'asset_status'      => 'Aktif',
                'description'       => 'Komputer kerja.',
            ],
            [
                'asset_code'        => 'AST-0003',
                'name'              => 'Printer',
                'category_id'       => $categories[0]['id'],
                'unit_id'           => $units[1]['id'],
                'location_id'       => $locations[1 % $locationCount]['id'],
                'brand'             => 'HP',
                'model'             => 'LaserJet',
                'serial_number'     => 'SN-PRN-0001',
                'acquisition_year'  => 2024,
                'acquisition_price' => 4500000,
                'condition_status'  => 'Baik',
                'asset_status'      => 'Aktif',
                'description'       => 'Printer administrasi.',
            ],
            [
                'asset_code'        => 'AST-0004',
                'name'              => 'Meja Kerja',
                'category_id'       => $categories[1]['id'],
                'unit_id'           => $units[2]['id'],
                'location_id'       => $locations[2 % $locationCount]['id'],
                'brand'             => 'Olympic',
                'model'             => 'Office Desk',
                'serial_number'     => null,
                'acquisition_year'  => 2023,
                'acquisition_price' => 2500000,
                'condition_status'  => 'Baik',
                'asset_status'      => 'Aktif',
                'description'       => 'Meja kerja staf.',
            ],
            [
                'asset_code'        => 'AST-0005',
                'name'              => 'Kursi Kantor',
                'category_id'       => $categories[1]['id'],
                'unit_id'           => $units[2]['id'],
                'location_id'       => $locations[2 % $locationCount]['id'],
                'brand'             => 'Informa',
                'model'             => 'Office Chair',
                'serial_number'     => null,
                'acquisition_year'  => 2023,
                'acquisition_price' => 1500000,
                'condition_status'  => 'Baik',
                'asset_status'      => 'Aktif',
                'description'       => 'Kursi kerja staf.',
            ],
            [
                'asset_code'        => 'AST-0006',
                'name'              => 'Proyektor',
                'category_id'       => $categories[0]['id'],
                'unit_id'           => $units[7]['id'],
                'location_id'       => $locations[3 % $locationCount]['id'],
                'brand'             => 'Epson',
                'model'             => 'EB-X06',
                'serial_number'     => 'SN-PRO-0001',
                'acquisition_year'  => 2024,
                'acquisition_price' => 7000000,
                'condition_status'  => 'Baik',
                'asset_status'      => 'Aktif',
                'description'       => 'Proyektor ruang rapat.',
            ],
            [
                'asset_code'        => 'AST-0007',
                'name'              => 'AC Split',
                'category_id'       => $categories[1]['id'],
                'unit_id'           => $units[8]['id'],
                'location_id'       => $locations[6 % $locationCount]['id'],
                'brand'             => 'Daikin',
                'model'             => 'FTKC',
                'serial_number'     => 'SN-AC-0001',
                'acquisition_year'  => 2023,
                'acquisition_price' => 6000000,
                'condition_status'  => 'Baik',
                'asset_status'      => 'Aktif',
                'description'       => 'AC ruang arsip.',
            ],
            [
                'asset_code'        => 'AST-0008',
                'name'              => 'Rak Arsip',
                'category_id'       => $categories[1]['id'],
                'unit_id'           => $units[3]['id'],
                'location_id'       => $locations[6 % $locationCount]['id'],
                'brand'             => 'Lion',
                'model'             => 'Office Rack',
                'serial_number'     => null,
                'acquisition_year'  => 2022,
                'acquisition_price' => 3000000,
                'condition_status'  => 'Rusak Ringan',
                'asset_status'      => 'Aktif',
                'description'       => 'Rak penyimpanan arsip.',
            ],
            [
                'asset_code'        => 'AST-0009',
                'name'              => 'CCTV',
                'category_id'       => $categories[2]['id'],
                'unit_id'           => $units[6]['id'],
                'location_id'       => $locations[14 % $locationCount]['id'],
                'brand'             => 'Hikvision',
                'model'             => 'IP Camera',
                'serial_number'     => 'SN-CCTV-0001',
                'acquisition_year'  => 2025,
                'acquisition_price' => 1800000,
                'condition_status'  => 'Baik',
                'asset_status'      => 'Aktif',
                'description'       => 'Kamera pengawasan.',
            ],
            [
                'asset_code'        => 'AST-0010',
                'name'              => 'Server',
                'category_id'       => $categories[0]['id'],
                'unit_id'           => $units[0]['id'],
                'location_id'       => $locations[5 % $locationCount]['id'],
                'brand'             => 'Dell',
                'model'             => 'PowerEdge',
                'serial_number'     => 'SN-SRV-0001',
                'acquisition_year'  => 2025,
                'acquisition_price' => 35000000,
                'condition_status'  => 'Baik',
                'asset_status'      => 'Aktif',
                'description'       => 'Server aplikasi inventaris.',
            ],
        ];

        $this->db->table('assets')->insertBatch($assets);
    }
}