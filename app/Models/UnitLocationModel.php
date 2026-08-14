<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitLocationModel extends Model
{
    protected $table = 'unit_locations';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $allowedFields = [
        'unit_id',
        'location_id',
        'created_at',
    ];

    protected $useTimestamps = false;
}
