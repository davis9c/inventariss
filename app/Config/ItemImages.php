<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi gambar barang (aset dan barang stok).
 *
 * Gambar ini untuk identifikasi cepat, jadi jenisnya hanya gambar raster.
 * Dokumen (PDF dan lainnya) ditangani Config\AssetDocuments yang terpisah,
 * karena tujuannya berbeda: dokumen adalah bukti, gambar adalah alat pengenalan.
 */
class ItemImages extends BaseConfig
{
    /**
     * MIME yang diizinkan. Divalidasi ulang di server lewat finfo — nilai
     * di sini hanya allowlist, bukan sumber kebenaran.
     */
    public array $allowedMimes = [
        'image/jpeg',
        'image/png',
    ];

    /**
     * Ekstensi yang diizinkan. Harus konsisten dengan allowedMimes: kedua
     * sisi dicek, berkas ditolak bila hanya satu yang cocok.
     */
    public array $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * Batas ukuran per gambar, dalam byte, SETELAH dikecilkan di browser.
     * Default 2 MB.
     *
     * Batas ini berlaku untuk berkas yang benar-benar diterima server.
     * Berkas asli yang lebih besar tidak masalah: JavaScript mengecilkannya
     * dulu (lihat Inventaris.resizeImage di public/js/inventaris.js).
     */
    public int $maxSizeBytes = 2_097_152;

    /**
     * Batas ukuran berkas ASLI sebelum dikecilkan, dalam byte. Default 15 MB.
     *
     * Ini batas di sisi browser. Tujuannya menolak foto bertensyon
     * megapixel, bukan membatasi pengguna.
     */
    public int $maxOriginalBytes = 15_728_640;

    /**
     * Jumlah gambar maksimum per barang.
     */
    public int $maxImages = 5;

    /**
     * Sisi terpanjang gambar hasil resize, dalam piksel.
     *
     * Cukup untuk mengenali barang dari jarak layar, tapi tidak membebani
     * bandwidth saat tabel daftar memuat puluhan gambar.
     */
    public int $maxDimension = 1280;

    /**
     * Kualitas JPEG hasil resize, 0..1. 0.82 menghasilkan gambar yang
     * masih tajam untuk foto barang dengan ukuran sekitar 150-250 KB.
     */
    public float $jpegQuality = 0.82;

    /**
     * Folder di bawah writable/uploads/ tempat gambar disimpan.
     */
    public string $directory = 'item-images';

    /**
     * MIME yang aman ditampilkan inline di browser (dipakai untuk
     * Content-Disposition pada endpoint unduh). Semua tipe lain dipaksa
     * attachment supaya tidak pernah dieksekusi sebagai dokumen.
     */
    public array $inlineMimes = [
        'image/jpeg',
        'image/png',
    ];
}
