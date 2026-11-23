<?php

namespace App\Models;

use CodeIgniter\Model;

class AccessProfileLocationModel extends Model
{
    protected $table      = 'access_profile_locations';
    protected $primaryKey = 'profile_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'profile_id',
        'location_id',
    ];

    protected $useTimestamps = false;
}