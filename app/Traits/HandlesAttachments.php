<?php

namespace App\Traits;

use App\Libraries\AttachmentStorage;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\ResponseInterface;
use Config\AssetDocuments;
use Config\ItemImages;

/**
 * Potongan bersama untuk controller yang menangani lampiran.
 *
 * Menyediakan tiga hal yang selalu sama bentuk antara dokumen aset dan gambar
 * barang:
 *
 *  1. balasan dual-mode (JSON untuk AJAX, redirect + flash untuk form biasa)
 *  2. deteksi "PHP sudah membuang berkas" supaya pesannya tidak membingungkan
 *  3. penghapusan berkas fisik
 *
 * Sisanya (guard lokasi, cek batas jumlah) tetap milik controller karena
 * tabel dan modelnya berbeda.
 */
trait HandlesAttachments
{
    /**
     * @return array{ok:bool, error?:string}
     */
    protected function attachmentStore(File $file, BaseConfig|string $cfg): array
    {
        $storage = AttachmentStorage::for($cfg);

        return $storage->store($file);
    }

    /**
     * Pesan gagal dalam bentuk yang sesuai dengan mode permintaan.
     *
     * @param ResponseInterface|null $ajax     dibalas apa adanya kalau AJAX
     * @param string|null            $redirect tujuan redirect kalau non-AJAX
     */
    protected function attachFail(
        string $message,
        int $status = 422,
        ?string $redirect = null
    ): ResponseInterface {
        if ($this->request->isAJAX()) {
            return $this->respondError($message, $status);
        }

        return redirect()
            ->to($redirect ?? previous_url() ?? '/')
            ->with('error', $message);
    }

    /**
     * Pesan sukses dalam bentuk yang sesuai dengan mode permintaan.
     */
    protected function attachOk(
        string $message,
        $data = null,
        ?string $redirect = null
    ): ResponseInterface {
        if ($this->request->isAJAX()) {
            return $this->respondSuccess($message, $data);
        }

        return redirect()
            ->to($redirect ?? previous_url() ?? '/')
            ->with('success', $message);
    }

    /**
     * Deteksi kasus ketika PHP membuang berkas sebelum aplikasi sempat
     * berjalan, karena upload_max_filesize lebih kecil daripada batas
     * aplikasi.
     *
     * Tanpa deteksi ini, pesan errornya akan terlihat seperti "tidak ada
     * berkas yang dipilih" padahal pengguna jelas sudah memilih berkas.
     */
    protected function attachServerLimitMessage(BaseConfig $cfg): ?string
    {
        $file = $this->request->getFile('document') ?? $this->request->getFile('image');

        if ($file === null) {
            return null;
        }

        if (! in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return null;
        }

        return 'Ukuran berkas melebihi batas server ('
            . ini_get('upload_max_filesize') . '). Batas server lebih kecil '
            . 'daripada batas aplikasi ('
            . document_size_label($cfg->maxSizeBytes) . ').';
    }

    /**
     * Hapus berkas fisik untuk sekumpulan baris lampiran.
     *
     * @param  array<int, array<string,mixed>> $rows
     * @return int
     */
    protected function attachDeleteFiles(BaseConfig|string $cfg, array $rows): int
    {
        return AttachmentStorage::for($cfg)->deleteFiles($rows);
    }

    protected function documentStorage(): AttachmentStorage
    {
        return AttachmentStorage::for(new AssetDocuments());
    }

    protected function imageStorage(): AttachmentStorage
    {
        return AttachmentStorage::for(new ItemImages());
    }
}
