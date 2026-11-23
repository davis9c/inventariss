<?php

namespace App\Models;

use CodeIgniter\Model;

class UserAccessProfileModel extends Model
{
    protected $table      = 'user_access_profiles';
    protected $primaryKey = 'user_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'profile_id',
        'created_at',
    ];

    protected $useTimestamps = false;
}