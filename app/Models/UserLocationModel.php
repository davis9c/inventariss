<?php

namespace App\Models;

use CodeIgniter\Model;

class UserLocationModel extends Model
{
    protected $table = 'user_locations';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'location_id',
        'created_at',
    ];

    protected $useTimestamps = false;
}
