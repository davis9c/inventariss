<?php

namespace App\Libraries;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\ResponseInterface;
use Config\AssetDocuments;
use Config\ItemImages;

/**
 * Penyimpanan lampiran (dokumen aset dan gambar barang).
 *
 * Dipakai dua fitur yang mekanikanya sama persis: letakkan berkas di luar
 * docroot dengan nama acak, menyajikannya lewat controller, lalu hapus
 * berkas dan barisnya bersama-sama. Semua aturan keamanannya hidup di sini
 * supaya tidak ada salinan yang bisa berbeda di satu tempat.
 *
 * Berkas disimpan di writable/uploads/<dir>. APACHE_DOCUMENT_ROOT menunjuk
 * ke public/, jadi folder ini TIDAK bisa diakses lewat URL — satu-satunya
 * jalan menuju isi berkas adalah send().
 */
class AttachmentStorage
{
    /** @var array<string, self> */
    private static array $instances = [];

    private BaseConfig $cfg;

    private function __construct(BaseConfig $cfg)
    {
        $this->cfg = $cfg;
    }

    /**
     * Penyimpanan untuk salah satu konfigurasi lampiran.
     */
    public static function for(BaseConfig|string $cfg): self
    {
        if (is_string($cfg)) {
            $cfg = new $cfg();
        }

        $key = $cfg::class;

        return self::$instances[$key] ??= new self($cfg);
    }

    public static function documents(): self
    {
        return self::for(new AssetDocuments());
    }

    public static function images(): self
    {
        return self::for(new ItemImages());
    }

    public function config(): BaseConfig
    {
        return $this->cfg;
    }

    /**
     * Folder penyimpanan, dibuat kalau belum ada.
     */
    public function dir(bool $create = true): string
    {
        $path = rtrim(WRITEPATH . 'uploads/' . $this->cfg->directory, DIRECTORY_SEPARATOR);

        if ($create && ! is_dir($path) && ! mkdir($path, 0755, true) && ! is_dir($path)) {
            throw new \RuntimeException("Tidak bisa membuat folder penyimpanan: {$path}");
        }

        return $path;
    }

    /**
     * Simpan satu berkas yang sudah lolos pengecekan ukuran dan error PHP.
     *
     * Pengecekan tipe TIDAK diulang di sini: pustaka ini sudah memegang
     * konfigurasi sendiri, jadi allowlist-nya selalu sama dengan yang
     * dipakai pemanggil saat memeriksa batas.
     *
     * @return array{ok:bool, data?:array<string,mixed>, error?:string}
     */
    public function store(File $file): array
    {
        $original = self::sanitizeName($file->getClientName());
        $ext      = strtolower($file->getExtension() ?: '');

        // MIME dibaca dari ISI berkas, bukan dari klaim klien. Inilah yang
        // menolak berkas .png yang sebenarnya berisi PHP.
        $mime = $file->getMimeType() ?? '';

        if (! in_array($mime, $this->cfg->allowedMimes, true)
            || ! in_array($ext, $this->cfg->allowedExtensions, true)
        ) {
            return [
                'ok'    => false,
                'error' => $original . ' (tipe tidak diizinkan: ' . ($mime ?: 'tidak dikenal') . ')',
            ];
        }

        $size = (int) $file->getSize();

        if ($size > $this->cfg->maxSizeBytes) {
            return [
                'ok'    => false,
                'error' => $original . ' (melebihi ' . document_size_label($this->cfg->maxSizeBytes) . ')',
            ];
        }

        // Nama acak di disk. Nama dari klien tidak pernah jadi path, jadi
        // "../../" atau titik-titik pada nama asli tidak bisa mengarahkan
        // penyimpanan ke luar folder.
        $stored = $file->getRandomName() . '.' . $ext;

        if (! $file->move($this->dir(), $stored)) {
            return ['ok' => false, 'error' => $original . ' (gagal menyimpan)'];
        }

        return [
            'ok'   => true,
            'data' => [
                'stored_name'   => $stored,
                'original_name' => $original,
                'mime_type'     => $mime,
                'extension'     => $ext,
                'size_bytes'    => $size,
            ],
        ];
    }

    /**
     * Layani satu berkas ke browser dengan header yang mengeraskan
     * unduhan.
     *
     * @throws PageNotFoundException bila nama berkas mencurigakan atau
     *                             berkasnya tidak ada
     */
    public function send(string $storedName, string $mime, string $originalName): ResponseInterface
    {
        $real = $this->resolve($storedName);

        if ($real === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $inline = in_array($mime, $this->cfg->inlineMimes, true);

        return service('response')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            // Cegah berkas yang disisipkan script dieksekusi di origin app.
             // Directive ini hanya berlaku untuk dokumen, jadi tidak menghalangi
             // <img src> yang menunjuk endpoint yang sama.
            ->setHeader('Content-Security-Policy', "default-src 'none'; sandbox")
            ->setHeader(
                'Content-Disposition',
                ($inline ? 'inline' : 'attachment') . '; filename="' . self::asciiFilename($originalName) . '"'
            )
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Length', (string) filesize($real))
            ->setBody((string) file_get_contents($real));
    }

    /**
     * Hapus berkas fisik untuk sekumpulan baris.
     *
     * Dipakai juga saat barang dihapus: cascade database menghapus barisnya,
     * tapi cascade itu tidak menyentuh filesystem, jadi tanpa pemanggilan ini
     * berkasnya tertinggal sebagai yatim.
     *
     * @param  array<int, array<string,mixed>> $rows
     * @return int jumlah berkas yang benar-benar terhapus
     */
    public function deleteFiles(array $rows): int
    {
        $base = realpath($this->dir(false));

        if ($base === false) {
            return 0;
        }

        $removed = 0;

        foreach ($rows as $row) {
            $name = (string) ($row['stored_name'] ?? '');

            if ($name === '') {
                continue;
            }

            $file = realpath($this->dir(false) . DIRECTORY_SEPARATOR . $name);

            // stored_name berasal dari database, tapi tetap dicek supaya file
            // di luar folder tidak bisa terhapus atau terbaca ketika baris
            // pernah dikutak atau disunting manual.
            if ($file === false || ! str_starts_with($file, $base) || ! is_file($file)) {
                continue;
            }

            if (@unlink($file)) {
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * Ubah stored_name menjadi path absolut yang sudah dipastikan berada di
     * dalam folder penyimpanan, atau null kalau tidak aman / tidak ada.
     */
    public function resolve(string $storedName): ?string
    {
        if ($storedName === '' || str_contains($storedName, '/') || str_contains($storedName, '\\')) {
            return null;
        }

        $base = realpath($this->dir(false));
        $real = realpath($this->dir(false) . DIRECTORY_SEPARATOR . $storedName);

        if ($base === false || $real === false || ! str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return is_file($real) ? $real : null;
    }

    /**
     * Sanitasi nama asli sebelum disimpan.
     *
     * Dipakai untuk tampilan DAN untuk header Content-Disposition, jadi harus
     * bebas dari CR/LF (header injection) dan karakter kontrol.
     */
    public static function sanitizeName(string $name): string
    {
        // Buang karakter kontrol termasuk CR/LF yang bisa memecah header.
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';

        // Buang pemisah path supaya nama tidak pernah mengandung "../../".
        $name = str_replace(['/', '\\', "\0"], '', $name);

        $name = trim($name);

        if ($name === '' || $name === '.' || $name === '..') {
            return 'dokumen';
        }

        // Batasi panjang agar tidak memotong header.
        if (mb_strlen($name) > 200) {
            $name = mb_substr($name, 0, 200);
        }

        return $name;
    }

    /**
     * Versi ASCII aman untuk header Content-Disposition.
     *
     * Nama asli bisa mengandung karakter non-ASCII (mis. "Sertifikat —
     * 2026.pdf") yang tidak boleh mentah masuk ke header.
     */
    public static function asciiFilename(string $name): string
    {
        $ascii = preg_replace('/[^\x20-\x7E]/', '_', $name) ?? 'dokumen';
        $ascii = str_replace(['"', '\\'], '_', $ascii);

        return $ascii !== '' ? $ascii : 'dokumen';
    }
}
