<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetModel extends Model
{
    protected $table = 'assets';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'asset_code',
        'name',
        'category_id',
        'unit_id',
        'location_id',
        'brand',
        'model',
        'serial_number',
        'acquisition_year',
        'acquisition_price',
        'condition_status',
        'asset_status',
        'description',
    ];

    protected $useTimestamps = true;
}