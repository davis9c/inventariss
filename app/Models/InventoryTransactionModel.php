<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryTransactionModel extends Model
{
    protected $table = 'inventory_transactions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'transaction_code',
        'transaction_date',
        'transaction_type',
        'outbound_type',
        'recipient_name',
        'destination_unit',
        'document_number',
        'handed_over_by',
        'received_by',
        'item_type',
        'asset_id',
        'stock_item_id',
        'quantity',
        'from_location_id',
        'to_location_id',
        'from_unit_id',
        'to_unit_id',
        'reference_type',
        'reference_id',
        'reason',
        'notes',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    public function generateCode(): string
    {
        return 'TRX-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
