<?php
namespace App\Models;
use CodeIgniter\Model;
class InventoryDocumentModel extends Model { protected $table = 'inventory_documents'; protected $allowedFields = ['owner_type','owner_id','document_type','document_number','document_date','valid_from','valid_until','file_path','original_name','mime_type','notes','created_by']; protected $useTimestamps = true; protected $useSoftDeletes = true; }
