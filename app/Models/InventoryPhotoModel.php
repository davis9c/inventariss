<?php
namespace App\Models;
use CodeIgniter\Model;
class InventoryPhotoModel extends Model { protected $table = 'inventory_photos'; protected $allowedFields = ['owner_type','owner_id','file_path','original_name','mime_type','caption','created_by']; protected $useTimestamps = true; protected $useSoftDeletes = true; }
