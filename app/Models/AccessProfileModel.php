<?php

namespace App\Models;

use CodeIgniter\Model;

class AccessProfileModel extends Model
{
    protected $table      = 'access_profiles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'description',
    ];

    protected $useTimestamps = true;
}