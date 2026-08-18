<?php
namespace App\Models;
use CodeIgniter\Model;
class AuditLogModel extends Model { protected $table = 'audit_logs'; protected $allowedFields = ['user_id','role_name','action','module','record_id','before_data','after_data','notes','created_at']; protected $useTimestamps = false; }
