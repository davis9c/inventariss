<?php

namespace App\Models;

use CodeIgniter\Model;

class StockOpnameModel extends Model
{
    protected $table = 'stock_opnames';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'opname_code',
        'opname_date',
        'location_id',
        'status',
        'notes',
        'created_by',
    ];

    protected $useTimestamps = true;
}