<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi dokumen pada catatan pergerakan (surat jalan, nota, berita
 * acara, dan sejenisnya).
 *
 * Bentuk sengaja sama persis dengan Config\AssetDocuments supaya trait
 * HandlesAttachments bisa melayani keduanya tanpa cabang tambahan.
 */
class TransactionDocuments extends BaseConfig
{
    /**
     * MIME yang diizinkan. Divalidasi ulang di server lewat finfo — nilai
     * di sini hanya allowlist, bukan sumber kebenaran.
     */
    public array $allowedMimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    /**
     * Ekstensi yang diizinkan. Harus konsisten dengan allowedMimes: kedua
     * sisi dicek, berkas ditolak bila hanya satu yang cocok.
     */
    public array $allowedExtensions = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * Batas ukuran per berkas, dalam byte. Default 5 MB.
     *
     * CATATAN: batas ini TIDAK menggantikan batas PHP. Kalau
     * upload_max_filesize lebih kecil, PHP membuang berkas sebelum kode
     * aplikasi sempat berjalan. Lihat attachServerLimitMessage() di
     * App\Traits\HandlesAttachments yang mendeteksi kasus itu.
     */
    public int $maxSizeBytes = 5_242_880;

    /**
     * Jumlah dokumen maksimum per catatan pergerakan.
     */
    public int $maxFiles = 5;

    /**
     * Folder di bawah writable/uploads/ tempat berkas disimpan.
     */
    public string $directory = 'transaction-documents';

    /**
     * MIME yang aman ditampilkan inline di browser (dipakai untuk
     * Content-Disposition pada endpoint unduh). Semua tipe lain dipaksa
     * attachment supaya tidak pernah dieksekusi sebagai dokumen.
     */
    public array $inlineMimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];
}
