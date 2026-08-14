<?php

namespace App\Models;

use CodeIgniter\Model;

class LocationModel extends Model
{
    protected $table = 'locations';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'building',
        'floor',
        'room',
        'description',
        'is_active',
    ];

    protected $useTimestamps = true;
}