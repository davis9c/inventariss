<?php

namespace App\Traits;

use App\Libraries\AttachmentStorage;
use App\Models\ItemImageModel;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;
use Config\ItemImages;

/*
 * Kontrak untuk pemakai trait ini -- properti berikut WAJIB dideklarasikan
 * oleh kelas yang memakai trait:
 *
 *   protected string $imageItemType;    // ItemImageModel::TYPE_*
 *   protected string $imageBasePath;    // 'assets' atau 'stock-items'
 *   protected string $imageOwnerLabel;  // 'Barang' atau 'Barang stok'
 *
 * Sengaja tidak dideklarasikan di sini: nilai defaultnya berbeda antar
 * modul, dan PHP menganggap properti trait yang punya default berbeda dari
 * kelas sebagai tidak kompatibel.
 */

/**
 * Enam endpoint lampiran per jenis, dipakai bersama oleh Aset, Barang Stok,
 * dan Catatan Pergerakan.
 *
 * Setiap modul punya aturan yang persis sama (unggah, lihat, hapus) dan
 * hanya berbeda pada tabel induk, path route, dan cara hak akses lokasi
 * dihitung. Yang berubah diatur lewat hook di bawah; sisanya tidak perlu
 * ditulis ulang.
 *
 * "Jenis" di sini berarti dokumen atau gambar -- keduanya punya kode yang
 * sama persis, hanya berbeda tabel, config, nama field, dan kata benda di
 * pesannya. Nama method-nya sengaja memuat kata jenis supaya rutenya jelas
 * dan tidak perlu method terpisah per jenis.
 */
trait HandlesItemImages
{
    use HandlesAttachments;

    /*
     * Kontrak untuk pemakai trait ini -- properti berikut WAJIB
     * dideklarasikan oleh kelas yang memakai trait:
     *
     *   protected string $imageItemType;    // ItemImageModel::TYPE_*
     *   protected string $imageBasePath;    // 'assets' atau 'stock-items'
     *   protected string $imageOwnerLabel;  // 'Barang' atau 'Barang stok'
     *
     * Sengaja tidak dideklarasikan di sini: nilai defaultnya berbeda antar
     * modul, dan PHP menganggap properti trait yang punya default berbeda
     * dari kelas sebagai tidak kompatibel.
     */

    /** Kind yang dikenali trait ini. */
    public const KIND_IMAGE = 'image';

    /** Kind yang dikenali trait ini. */
    public const KIND_DOCUMENT = 'document';

    // ── Hook yang wajib diisi pemakai ──────────────────────────

    /**
     * Muat baris induk (aset / barang stok / catatan pergerakan) atau null.
     *
     * Endpoint memanggilnya lalu memanggil attachmentOwnerAccessible() untuk
     * memeriksa hak akses.
     *
     * @return array<string,mixed>|null
     */
    abstract protected function findAttachmentOwner(int $id): ?array;

    /**
     * Apakah pemanggil berhak menyentuh lampiran milik induk ini.
     *
     * Default memakai can_access_location() dengan asumsi induk punya satu
     * kolom location_id -- benar untuk assets dan stock_items.
     *
     * Catatan pergerakan tidak punya satu lokasi tunggal (dari A ke B), jadi
     * controller-nya meng-override method ini memakai
     * can_access_transaction(). Aturan lampiran HARUS sama dengan aturan
     * induknya: kalau lebih longgar, lampiran bisa dibaca dengan menebak id;
     * kalau lebih ketat, lampiran milik sendiri tidak bisa dibuka.
     *
     * @param array $owner baris induk
     */
    protected function attachmentOwnerAccessible(array $owner): bool
    {
        return can_access_location($owner['location_id'] ?? null);
    }

    /**
     * Model untuk sebuah jenis lampiran.
     *
     * Default hanya melayani gambar, yang dipakai Aset dan Barang Stok.
     * Modul lain (Catatan Pergerakan) meng-override ini karena dokumennya
     * hidup di tabel lain.
     *
     * Wajib punya method: find(), insert(), delete(), getInsertID(),
     * countFor($type, $id) dan forItem($type, $id).
     */
    protected function attachmentModel(string $kind): Model
    {
        $this->assertKindSupported($kind);

        return new ItemImageModel();
    }

    /**
     * Konfigurasi untuk sebuah jenis lampiran.
     */
    protected function attachmentConfig(string $kind): BaseConfig
    {
        $this->assertKindSupported($kind);

        return new ItemImages();
    }

    /**
     * Bentuk lampiran untuk sebuah induk.
     *
     * Default membaca properti $imageItemType, yang tetap sama untuk satu
     * modul. Catatan Pergerakan meng-override ini karena satu controller
     * melayani dua bentuk sekaligus (stok dan aset), jadi jenisnya ikut
     * transaksi yang sedang diproses.
     *
     * @param array $owner baris induk
     */
    protected function attachmentOwnerType(array $owner): string
    {
        return $this->imageItemType;
    }

    /**
     * Gagal keras kalau sebuah jenis tidak didukung modul ini.
     *
     * Tanpa ini, salah tulis route untuk dokumen akan diam-diam menulis ke
     * tabel gambar -- sulit sekali ditemukan karena tidak ada error sama
     * sekali.
     */
    private function assertKindSupported(string $kind): void
    {
        if ($kind !== self::KIND_IMAGE) {
            throw new \LogicException(
                static::class . ' hanya melayani lampiran gambar, bukan "'
                . $kind . '". Dokumen punya jalurnya sendiri.'
            );
        }
    }

    /**
     * Kata benda untuk pesan, mis. 'gambar' atau 'dokumen'.
     */
    protected function attachmentNoun(string $kind): string
    {
        return $kind === self::KIND_DOCUMENT ? 'dokumen' : 'gambar';
    }

    /**
     * Nama field multipart untuk sebuah jenis, mis. 'image' atau 'document'.
     */
    protected function attachmentField(string $kind): string
    {
        return $kind;
    }

    /**
     * Batas jumlah lampiran per induk untuk sebuah jenis.
     */
    protected function attachmentCap(BaseConfig $cfg): int
    {
        return (int) ($cfg->maxImages ?? $cfg->maxFiles ?? 5);
    }

    // ── Yang dipakai bersama ────────────────────────────────────

    /**
     * Unggah satu lampiran untuk satu induk.
     *
     * Satu berkas per unggahan karena tiap lampiran punya judul sendiri --
     * judul itulah yang membuatnya dapat dicari tanpa harus membukanya.
     *
     * @param string $kind self::KIND_IMAGE atau self::KIND_DOCUMENT
     */
    protected function uploadAttachment($id, string $kind): ResponseInterface
    {
        $cfg = $this->attachmentConfig($kind);
        $id  = (int) $id;

        $owner = $this->findAttachmentOwner($id);

        if ($owner === null) {
            return $this->attachFail(
                $this->imageOwnerLabel . ' tidak ditemukan.',
                404
            );
        }

        if (! $this->attachmentOwnerAccessible($owner)) {
            return $this->attachFail(
                'Anda tidak memiliki akses ke ' . lcfirst($this->imageOwnerLabel) . ' tersebut.',
                403
            );
        }

        if ($limit = $this->attachServerLimitMessage($cfg)) {
            return $this->attachFail($limit);
        }

        $model = $this->attachmentModel($kind);
        $cap   = $this->attachmentCap($cfg);

        if ($model->countFor($this->attachmentOwnerType($owner), $id) >= $cap) {
            return $this->attachFail(
                'Jumlah ' . $this->attachmentNoun($kind) . ' sudah mencapai batas '
                . $cap . ' per ' . lcfirst($this->imageOwnerLabel) . '.'
            );
        }

        $noun  = $this->attachmentNoun($kind);
        $title = trim((string) ($this->request->getPost('title') ?? ''));

        if ($title === '') {
            return $this->attachFail('Judul ' . $noun . ' wajib diisi.', 422);
        }

        if (mb_strlen($title) > 150) {
            return $this->attachFail('Judul ' . $noun . ' maksimal 150 karakter.', 422);
        }

        $file = $this->request->getFile($this->attachmentField($kind));

        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return $this->attachFail('Tidak ada berkas ' . $noun . ' yang dipilih.');
        }

        $result = $this->attachmentStore($file, $cfg);

        if (! $result['ok']) {
            return $this->attachFail('Gagal menyimpan ' . $noun . ': ' . $result['error']);
        }

        $description = trim((string) ($this->request->getPost('description') ?? ''));

        $model->insert([
            'item_type'     => $this->attachmentOwnerType($owner),
            'item_id'       => $id,
            'title'         => $title,
            'description'   => $description === '' ? null : $description,
            'stored_name'   => $result['data']['stored_name'],
            'original_name' => $result['data']['original_name'],
            'mime_type'     => $result['data']['mime_type'],
            'extension'     => $result['data']['extension'],
            'size_bytes'    => $result['data']['size_bytes'],
            'uploaded_by'   => (int) session()->get('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return $this->attachOk(
            ucfirst($noun) . ' berhasil diunggah.',
            ['id' => (int) $model->getInsertID()]
        );
    }

    /**
     * Kirim satu lampiran ke browser.
     *
     * Berkas disimpan di luar docroot, jadi ini satu-satunya jalan menuju
     * isinya -- karena itu guard lokasi tidak boleh dilewati.
     */
    protected function downloadAttachment($attachmentId, string $kind): ResponseInterface
    {
        $row = $this->attachmentModel($kind)->find((int) $attachmentId);

        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        $owner = $this->findAttachmentOwner((int) $row['item_id']);

        if (! $owner || ! $this->attachmentOwnerAccessible($owner)) {
            // 404, bukan 403: jangan bocorkan bahwa lampiran ini ADA tapi
            // tidak boleh diakses.
            throw PageNotFoundException::forPageNotFound();
        }

        // Bentuk lampiran harus cocok dengan bentuk induknya. Tanpa cek ini,
        // lampiran transaksi aset bisa diambil lewat jalur barang stok --
        // user tetap boleh membacanya, tapi lewat aturan modul yang salah.
        if ($row['item_type'] !== $this->attachmentOwnerType($owner)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return AttachmentStorage::for($this->attachmentConfig($kind))->send(
            $row['stored_name'],
            $row['mime_type'],
            $row['original_name']
        );
    }

    /**
     * Hapus satu lampiran: berkas di disk lalu barisnya.
     *
     * Urutannya penting. Kalau baris dihapus lebih dulu dan unlink gagal,
     * berkasnya jadi yatim yang tidak bisa dilacak lewat database.
     */
    protected function deleteAttachment($attachmentId, string $kind): ResponseInterface
    {
        $noun = $this->attachmentNoun($kind);
        $row  = $this->attachmentModel($kind)->find((int) $attachmentId);

        if (! $row) {
            return $this->attachFail(ucfirst($noun) . ' tidak ditemukan.', 404);
        }

        $owner = $this->findAttachmentOwner((int) $row['item_id']);

        if (! $owner || ! $this->attachmentOwnerAccessible($owner)) {
            return $this->attachFail(
                'Anda tidak memiliki akses ke ' . $noun . ' tersebut.',
                403
            );
        }

        if ($row['item_type'] !== $this->attachmentOwnerType($owner)) {
            return $this->attachFail(ucfirst($noun) . ' tidak ditemukan.', 404);
        }

        AttachmentStorage::for($this->attachmentConfig($kind))->deleteFiles([$row]);
        $this->attachmentModel($kind)->delete((int) $row['id']);

        return $this->attachOk(ucfirst($noun) . ' berhasil dihapus.', [
            'id' => (int) $row['id'],
        ]);
    }

    /**
     * Hapus semua lampiran milik satu induk, berkas dan barisnya.
     *
     * WAJIB dipanggil sebelum induk dihapus: tabel lampiran tidak punya
     * cascade, jadi tanpa ini lampiran akan tertinggal menunjuk induk yang
     * sudah tidak ada.
     *
     * @return int jumlah baris yang dihapus
     */
    protected function purgeOwnedAttachments(string $kind, string $itemType, int $itemId): int
    {
        $model = $this->attachmentModel($kind);
        $rows  = $model->forItem($itemType, $itemId);

        if ($rows === []) {
            return 0;
        }

        AttachmentStorage::for($this->attachmentConfig($kind))->deleteFiles($rows);

        $model->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->delete();

        return count($rows);
    }

    // ── Method publik yang dipanggil router ─────────────────────

    public function uploadItemImage($id): ResponseInterface
    {
        return $this->uploadAttachment($id, self::KIND_IMAGE);
    }

    public function downloadItemImage($imgId): ResponseInterface
    {
        return $this->downloadAttachment($imgId, self::KIND_IMAGE);
    }

    public function deleteItemImage($imgId): ResponseInterface
    {
        return $this->deleteAttachment($imgId, self::KIND_IMAGE);
    }

    public function uploadItemDocument($id): ResponseInterface
    {
        return $this->uploadAttachment($id, self::KIND_DOCUMENT);
    }

    public function downloadItemDocument($docId): ResponseInterface
    {
        return $this->downloadAttachment($docId, self::KIND_DOCUMENT);
    }

    public function deleteItemDocument($docId): ResponseInterface
    {
        return $this->deleteAttachment($docId, self::KIND_DOCUMENT);
    }
}
