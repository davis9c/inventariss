<?php

namespace App\Models;

use CodeIgniter\Model;

class StockOpnameDetailModel extends Model
{
    protected $table = 'stock_opname_details';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'stock_opname_id',
        'asset_id',
        'is_found',
        'condition_status',
        'notes',
        'checked_at',
    ];
}