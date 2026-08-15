<?php

namespace App\Models;

use CodeIgniter\Model;

class StockOpnameStockDetailModel extends Model
{
    protected $table = 'stock_opname_stock_details';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'stock_opname_id',
        'stock_item_id',
        'system_qty',
        'physical_qty',
        'notes',
        'checked_at',
    ];
}
