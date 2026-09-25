<?php

/**
 * Helper untuk dokumen aset (garansi, bukti pembelian, sertifikat).
 */

if (! function_exists('document_size_label')) {
    /**
     * Ukuran berkas dalam bentuk yang enak dibaca.
     *
     * Byte di bawah 1 KB ditampilkan sebagai byte, bukan "0 KB" — bulatkan
     * ke KB terlalu awal membuat berkas kecil tampak nol.
     *
     * @param int $bytes
     */
    function document_size_label(int $bytes): string
    {
        if ($bytes < 0) {
            return '-';
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        // Bulatkan ke MB kalau hasil pembulatan KB sudah 1024, supaya tidak
        // pernah muncul "1024 KB".
        if ($bytes >= 1048576 || round($bytes / 1024) >= 1024) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        return round($bytes / 1024, 1) . ' KB';
    }
}
