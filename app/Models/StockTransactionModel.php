<?php

namespace App\Models;

use CodeIgniter\Model;

class StockTransactionModel extends Model
{
    protected $table = 'stock_transactions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'stock_item_id',
        'type',
        'quantity',
        'transaction_date',
        'notes',
        'created_by',
    ];

    protected $useTimestamps = true;
}
