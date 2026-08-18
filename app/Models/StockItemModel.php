<?php

namespace App\Models;

use CodeIgniter\Model;

class StockItemModel extends Model
{
    protected $table = 'stock_items';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'item_code',
        'name',
        'category_id',
        'unit_id',
        'location_id',
        'satuan',
        'quantity',
        'description',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
}
