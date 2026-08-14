<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetMutationModel extends Model
{
    protected $table = 'asset_mutations';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'asset_id',
        'from_unit_id',
        'to_unit_id',
        'from_location_id',
        'to_location_id',
        'mutation_date',
        'reason',
        'notes',
        'created_by',
    ];

    protected $useTimestamps = true;
}