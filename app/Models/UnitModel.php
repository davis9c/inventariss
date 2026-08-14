<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table = 'units';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $useTimestamps = true;
}