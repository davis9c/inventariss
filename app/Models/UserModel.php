<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $protectFields = true;

    protected $allowedFields = [
        'usergate_id',
        'username',
        'password',
        'name',
        'is_active',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    /*
    |--------------------------------------------------------------------------
    | UserGate Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Cari user berdasarkan UserGate UUID.
     */
    public function findByUsergateId(string $uuid): ?array
    {
        return $this->where('usergate_id', $uuid)->first();
    }

    /**
     * Sinkronkan data user dari UserGate ke database lokal.
     * Insert jika belum ada, update jika sudah ada.
     *
     * @param array $ugUser Data dari UserGate response
     * @return array Data user lokal
     */
    public function syncFromUserGate(array $ugUser): array
    {
        $existing = null;

        // Cari by usergate_id dulu
        if (!empty($ugUser['id'])) {
            $existing = $this->findByUsergateId($ugUser['id']);
        }

        // Fallback: cari by username
        if (!$existing && !empty($ugUser['username'])) {
            $existing = $this->where('username', $ugUser['username'])->first();
        }

        $data = [
            'usergate_id' => $ugUser['id'] ?? null,
            'username'    => $ugUser['username'] ?? '',
            'name'        => $ugUser['full_name'] ?? $ugUser['username'] ?? '',
            'is_active'   => ($ugUser['status'] ?? 'ACTIVE') === 'ACTIVE' ? 1 : 0,
        ];

        if ($existing) {
            // Update
            $this->update($existing['id'], $data);
            $data['id'] = $existing['id'];
        } else {
            // Insert baru
            $this->insert($data);
            $data['id'] = $this->getInsertID();
        }

        return $data;
    }
}
