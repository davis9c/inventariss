<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi unggah dokumen (garansi, bukti pembelian, sertifikat) untuk
 * modul Aset.
 */
class AssetDocuments extends BaseConfig
{
    /**
     * MIME yang diizinkan. Divalidasi ulang di server lewat finfo
     * (lihat AssetDocumentModel::isAllowedMime()) — nilai di sini hanya
     * allowlist, bukan sumber kebenaran.
     */
    public array $allowedMimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    /**
     * Ekstensi yang diizinkan. Harus konsisten dengan allowedMimes: kedua
     * sisi dicek, file ditolak bila hanya satu yang cocok.
     */
    public array $allowedExtensions = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * Batas ukuran per file, dalam byte. Default 5 MB.
     *
     * CATATAN: batas ini TIDAK menggantikan batas PHP. Kalau
     * upload_max_filesize lebih kecil, PHP membuang file sebelum kode
     * aplikasi sempat berjalan. Docker sudah diset 64M; php.ini dev lokal
     * masih 2M. Lihat Asset::uploadDocuments() yang mendeteksi kasus itu.
     */
    public int $maxSizeBytes = 5_242_880;

    /**
     * Jumlah file maksimum per kali unggah.
     */
    public int $maxFiles = 5;

    /**
     * Folder di bawah writable/uploads/ tempat file disimpan.
     */
    public string $directory = 'asset-documents';

    /**
     * Berkas yang aman ditampilkan inline di browser (dipakai untuk
     * Content-Disposition pada endpoint unduh). Hanya tipe yang tidak
     * bisa membawa script. Semua tipe lain dipaksa attachment.
     */
    public array $inlineMimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];
}
