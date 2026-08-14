<?php

namespace App\Models;

use CodeIgniter\Model;

class MaintenanceModel extends Model
{
    protected $table = 'maintenances';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'maintenance_code',
        'asset_id',
        'maintenance_date',
        'maintenance_type',
        'problem',
        'action_taken',
        'technician_type',
        'technician_id',
        'technician_name',
        'vendor_name',
        'cost',
        'status',
        'completed_date',
        'notes',
        'created_by',

        // Approval
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $useTimestamps = true;
}
