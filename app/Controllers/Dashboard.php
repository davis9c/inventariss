<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\LocationModel;
use App\Models\CategoryModel;

class Dashboard extends BaseController
{
    protected AssetModel $assetModel;
    protected LocationModel $locationModel;
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->assetModel    = new AssetModel();
        $this->locationModel = new LocationModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        if (!hasAppAccess()) {
            return view('dashboard/index', [
                'title'     => 'Dashboard',
                'hasAccess' => false,
            ]);
        }

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
            'hasAccess'          => true,
        ]);
    }
}
