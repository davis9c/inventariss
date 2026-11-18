<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\LocationModel;
use App\Models\CategoryModel;
use App\Models\StockItemModel;

class Dashboard extends BaseController
{
    protected AssetModel $assetModel;
    protected LocationModel $locationModel;
    protected CategoryModel $categoryModel;
    protected StockItemModel $stockItemModel;

    public function __construct()
    {
        $this->assetModel     = new AssetModel();
        $this->locationModel  = new LocationModel();
        $this->categoryModel  = new CategoryModel();
        $this->stockItemModel = new StockItemModel();
    }

    public function index()
    {
        $assetBuilder = $this->assetModel;

        if (has_location_restriction()) {
            $assetBuilder->whereIn(
                'location_id',
                user_location_ids()
            );
        }

        $totalAssets = $assetBuilder->countAllResults(false);

        $usedBuilder = clone $assetBuilder;

        $usedAssets = $usedBuilder
            ->where('asset_status', 'Digunakan')
            ->countAllResults();

        $unusedBuilder = clone $assetBuilder;

        $unusedAssets = $unusedBuilder
            ->where('asset_status', 'Tidak Digunakan')
            ->countAllResults();

        $goodBuilder = clone $assetBuilder;

        $goodAssets = $goodBuilder
            ->where('condition_status', 'Baik')
            ->countAllResults();

        $lightDamageBuilder = clone $assetBuilder;

        $lightDamageAssets = $lightDamageBuilder
            ->where('condition_status', 'Rusak Ringan')
            ->countAllResults();

        $heavyDamageBuilder = clone $assetBuilder;

        $heavyDamageAssets = $heavyDamageBuilder
            ->where('condition_status', 'Rusak Berat')
            ->countAllResults();

        $locationBuilder = $this->locationModel
            ->where('is_active', 1);

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        $totalLocations = $locationBuilder
            ->countAllResults();

        $totalCategories = $this->categoryModel
            ->where('is_active', 1)
            ->countAllResults();

        /*
         * Ringkasan barang stok dalam satu query agregat. Hanya barang stok
         * aktif, dan tetap mengikuti pembatasan lokasi user.
         */
        $stockBuilder = $this->stockItemModel
            ->select(
                'COUNT(*) AS total_items,'
                . ' COALESCE(SUM(quantity), 0) AS total_quantity,'
                . ' COALESCE(SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END), 0) AS empty_items',
                false
            )
            ->where('is_active', 1);

        if (has_location_restriction()) {
            $stockBuilder->whereIn(
                'location_id',
                user_location_ids()
            );
        }

        $stockSummary = $stockBuilder->first();

        $totalStockItems    = (int) ($stockSummary['total_items'] ?? 0);
        $totalStockQuantity = (int) ($stockSummary['total_quantity'] ?? 0);
        $emptyStockItems    = (int) ($stockSummary['empty_items'] ?? 0);

        return view('dashboard/index', [
            'title'              => 'Dashboard',
            'totalAssets'        => $totalAssets,
            'usedAssets'         => $usedAssets,
            'unusedAssets'       => $unusedAssets,
            'goodAssets'         => $goodAssets,
            'lightDamageAssets'  => $lightDamageAssets,
            'heavyDamageAssets'  => $heavyDamageAssets,
            'totalLocations'     => $totalLocations,
            'totalCategories'    => $totalCategories,
            'totalStockItems'    => $totalStockItems,
            'totalStockQuantity' => $totalStockQuantity,
            'emptyStockItems'    => $emptyStockItems,
        ]);
    }
}
