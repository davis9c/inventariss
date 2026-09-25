<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dokumen pada catatan pergerakan (stok maupun aset).
 *
 * Bentuknya sengaja sama persis dengan ItemImageModel supaya trait
 * HandlesItemImages bisa melayani keduanya tanpa cabang tambahan: yang
 * berbeda hanya tabel dan config-nya.
 */
class TransactionDocumentModel extends Model
{
    /** Lampiran milik transaksi barang stok. */
    public const TYPE_STOCK_MOVEMENT = 'stock_movement';

    /** Lampiran milik transaksi aset. */
    public const TYPE_ASSET_MOVEMENT = 'asset_movement';

    protected $table           = 'transaction_documents';
    protected $returnType      = 'array';
    protected $useSoftDeletes  = false;
    protected $useTimestamps   = false;
    protected $allowedFields   = [
        'item_type',
        'item_id',
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
    protected $skipValidation  = false;
    protected $validationRules = [
        // item_id TIDAK boleh is_unique -- satu catatan pergerakan boleh
        // punya banyak dokumen.
        'item_type'     => 'required|in_list[stock_movement,asset_movement]',
        'item_id'       => 'required|is_natural_no_zero',
        'title'         => 'required|max_length[150]',
        'description'   => 'permit_empty|max_length[1000]',
        'stored_name'   => 'required|max_length[255]',
        'original_name' => 'required|max_length[255]',
        'mime_type'     => 'required|max_length[100]',
        'extension'     => 'required|max_length[10]',
        'size_bytes'    => 'required|is_natural_no_zero',
    ];

    /**
     * Dokumen milik satu catatan pergerakan.
     *
     * @return array<int, array>
     */
    public function forItem(string $itemType, int $itemId): array
    {
        return $this->builder()
            ->select('transaction_documents.*, users.name AS uploader_name')
            ->join('users', 'users.id = transaction_documents.uploaded_by', 'left')
            ->where('transaction_documents.item_type', $itemType)
            ->where('transaction_documents.item_id', $itemId)
            ->orderBy('transaction_documents.created_at', 'DESC')
            ->orderBy('transaction_documents.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Jumlah dokumen milik satu catatan pergerakan.
     */
    public function countFor(string $itemType, int $itemId): int
    {
        return $this->builder()
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->countAllResults();
    }
}
