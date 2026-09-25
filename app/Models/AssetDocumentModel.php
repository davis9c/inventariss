<?php

namespace App\Models;

use App\Libraries\AttachmentStorage;
use CodeIgniter\Model;

class AssetDocumentModel extends Model
{
    protected $table           = 'asset_documents';
    protected $returnType      = 'array';
    protected $useSoftDeletes  = false;
    protected $useTimestamps   = false;
    protected $allowedFields   = [
        'asset_id',
        'title',
        'description',
        'stored_name',
        'original_name',
        'mime_type',
        'extension',
        'size_bytes',
        'uploaded_by',
        'created_at',
    ];
    protected $skipValidation   = false;
    // CATATAN: asset_id TIDAK boleh is_unique — satu aset boleh punya
    // banyak dokumen.
    protected $validationRules  = [
        'asset_id'      => 'required|is_natural_no_zero',
        'title'         => 'required|max_length[150]',
        'description'   => 'permit_empty|max_length[1000]',
        'stored_name'   => 'required|max_length[255]',
        'original_name' => 'required|max_length[255]',
        'mime_type'     => 'required|max_length[100]',
        'extension'     => 'required|max_length[10]',
        'size_bytes'    => 'required|is_natural_no_zero',
    ];

    /**
     * @return array<int, array>
     */
    public function forAsset(int $assetId): array
    {
        return $this->builder()
            ->select('asset_documents.*, users.name AS uploader_name')
            ->join('users', 'users.id = asset_documents.uploaded_by', 'left')
            ->where('asset_documents.asset_id', $assetId)
            ->orderBy('asset_documents.created_at', 'DESC')
            ->orderBy('asset_documents.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function countForAsset(int $assetId): int
    {
        return $this->builder()
            ->where('asset_id', $assetId)
            ->countAllResults();
    }

    /**
     * Sanitasi nama asli sebelum disimpan.
     *
     * Dipakai untuk tampilan DAN untuk header Content-Disposition, jadi
     * harus bebas dari CR/LF (header injection) dan karakter kontrol.
     */
    public static function sanitizeName(string $name): string
    {
        return AttachmentStorage::sanitizeName($name);
    }

    /**
     * Versi ASCII aman untuk header Content-Disposition.
     */
    public static function asciiFilename(string $name): string
    {
        return AttachmentStorage::asciiFilename($name);
    }
}
