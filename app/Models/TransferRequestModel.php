<?php

namespace App\Models;

use CodeIgniter\Model;

class TransferRequestModel extends Model
{
    protected $table = 'transfer_requests';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'request_kind',
        'item_type',
        'asset_id',
        'stock_item_id',
        'from_location_id',
        'to_location_id',
        'from_unit_id',
        'to_unit_id',
        'quantity',
        'reason',
        'notes',
        'evidence_path',
        'evidence_original_name',
        'status',
        'requested_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $useTimestamps = true;

    /**
     * Permintaan pending yang masuk ke lokasi user (tujuan).
     */
    public function pendingIncoming(array $locationIds): array
    {
        $b = $this->builder()
            ->select('transfer_requests.*, fl.name as from_location_name, tl.name as to_location_name, u.name as requester_name')
            ->join('locations fl', 'fl.id = transfer_requests.from_location_id', 'left')
            ->join('locations tl', 'tl.id = transfer_requests.to_location_id', 'left')
            ->join('users u', 'u.id = transfer_requests.requested_by', 'left')
            ->where('transfer_requests.status', 'Pending')
            ->whereIn('transfer_requests.to_location_id', $locationIds);

        return $b->orderBy('transfer_requests.created_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Permintaan pending yang keluar dari lokasi user (asal).
     */
    public function pendingOutgoing(array $locationIds): array
    {
        $b = $this->builder()
            ->select('transfer_requests.*, fl.name as from_location_name, tl.name as to_location_name, u.name as requester_name')
            ->join('locations fl', 'fl.id = transfer_requests.from_location_id', 'left')
            ->join('locations tl', 'tl.id = transfer_requests.to_location_id', 'left')
            ->join('users u', 'u.id = transfer_requests.requested_by', 'left')
            ->where('transfer_requests.status', 'Pending')
            ->whereIn('transfer_requests.from_location_id', $locationIds);

        return $b->orderBy('transfer_requests.created_at', 'DESC')->get()->getResultArray();
    }

    public function countPendingIncoming(array $locationIds): int
    {
        if (empty($locationIds)) {
            return (int) $this->where('status', 'Pending')->countAllResults();
        }
        return (int) $this->where('status', 'Pending')
            ->whereIn('to_location_id', $locationIds)
            ->countAllResults();
    }
}