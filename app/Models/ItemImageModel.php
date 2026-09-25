<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Gambar barang dan catatan pergerakan.
 *
 * Satu tabel untuk semuanya, jadi setiap query wajib menyebut item_type.
 * Lihat App\Libraries\AttachmentStorage untuk detail mekanisme upload.
 */
class ItemImageModel extends Model
{
    /** Gambar milik tabel assets. */
    public const TYPE_ASSET = 'asset';

    /** Gambar milik tabel stock_items. */
    public const TYPE_STOCK_ITEM = 'stock_item';

    /** Gambar milik catatan pergerakan barang stok. */
    public const TYPE_STOCK_MOVEMENT = 'stock_movement';

    /** Gambar milik catatan pergerakan aset. */
    public const TYPE_ASSET_MOVEMENT = 'asset_movement';

    protected $table           = 'item_images';
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
    protected $skipValidation   = false;
    protected $validationRules  = [
        // item_id TIDAK boleh is_unique — satu barang boleh punya 5 gambar.
        'item_type'     => 'required|in_list[asset,stock_item,stock_movement,asset_movement]',
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
     * Gambar milik satu barang, lengkap dengan nama pengunggah.
     *
     * @return array<int, array>
     */
    public function forItem(string $itemType, int $itemId): array
    {
        return $this->builder()
            ->select('item_images.*, users.name AS uploader_name')
            ->join('users', 'users.id = item_images.uploaded_by', 'left')
            ->where('item_images.item_type', $itemType)
            ->where('item_images.item_id', $itemId)
            ->orderBy('item_images.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Jumlah gambar yang sudah ada untuk satu barang.
     */
    public function countFor(string $itemType, int $itemId): int
    {
        return $this->builder()
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->countAllResults();
    }

    /**
     * Id gambar pertama milik satu barang.
     *
     * Dipakai untuk kolom thumbnail di DataTable, jadi harus satu query
     * sederhana per baris.
     */
    public function firstIdFor(string $itemType, int $itemId): ?int
    {
        $id = $this->builder()
            ->select('id')
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRow('id');

        return $id === null ? null : (int) $id;
    }
}
