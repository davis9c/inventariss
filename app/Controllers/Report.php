<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\CategoryModel;
use App\Models\LocationModel;

class Report extends BaseController
{
    protected AssetModel $assetModel;
    protected CategoryModel $categoryModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->assetModel    = new AssetModel();
        $this->categoryModel = new CategoryModel();
        $this->locationModel = new LocationModel();
    }

    public function assets()
    {
        $builder = $this->assetModel
            ->select('
                assets.*,
                categories.name as category_name,
                units.name as unit_name,
                locations.name as location_name,
                locations.building,
                locations.room
            ')
            ->join(
                'categories',
                'categories.id = assets.category_id',
                'left'
            )
            ->join(
                'units',
                'units.id = assets.unit_id',
                'left'
            )
            ->join(
                'locations',
                'locations.id = assets.location_id',
                'left'
            );

        /*
         * Hak akses lokasi
         */
        if (has_location_restriction()) {
            $builder->whereIn(
                'assets.location_id',
                user_location_ids()
            );
        }

        /*
         * Filter lokasi
         */
        $locationId = $this->request->getGet('location_id');

        if ($locationId) {
            $builder->where(
                'assets.location_id',
                $locationId
            );
        }

        /*
         * Filter kategori
         */
        $categoryId = $this->request->getGet('category_id');

        if ($categoryId) {
            $builder->where(
                'assets.category_id',
                $categoryId
            );
        }

        /*
         * Filter kondisi
         */
        $condition = $this->request->getGet('condition_status');

        if ($condition) {
            $builder->where(
                'assets.condition_status',
                $condition
            );
        }

        /*
         * Filter status aset
         */
        $assetStatus = $this->request->getGet('asset_status');

        if ($assetStatus) {
            $builder->where(
                'assets.asset_status',
                $assetStatus
            );
        }

        $assets = $builder
            ->orderBy('assets.name', 'ASC')
            ->findAll();

        /*
         * Lokasi untuk filter
         */
        $locationBuilder = $this->locationModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC');

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        return view('reports/assets', [
            'title'      => 'Laporan Inventaris',
            'assets'     => $assets,
            'locations'  => $locationBuilder->findAll(),
            'categories' => $this->categoryModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),

            'filters' => [
                'location_id'      => $locationId,
                'category_id'      => $categoryId,
                'condition_status' => $condition,
                'asset_status'     => $assetStatus,
            ],
        ]);
    }
}
