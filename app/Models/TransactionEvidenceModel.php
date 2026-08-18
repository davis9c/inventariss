<?php
namespace App\Models;
use CodeIgniter\Model;
class TransactionEvidenceModel extends Model { protected $table = 'transaction_evidence'; protected $allowedFields = ['transaction_id','evidence_type','file_path','original_name','mime_type','notes','created_by']; protected $useTimestamps = true; protected $useSoftDeletes = true; }
