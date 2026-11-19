<?php

namespace App\Controllers;

use App\Libraries\AttachmentStorage;
use App\Models\LocationModel;
use App\Models\AssetModel;
use App\Models\ItemImageModel;
use App\Models\UnitModel;
use App\Models\UnitLocationModel;
use App\Traits\HandlesAttachments;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\ItemImages;

class Location extends BaseController
{
    // Foto ruangan memakai penyimpanan gambar yang sama dengan aset dan
    // barang stok (AttachmentStorage + Config\ItemImages).
    use HandlesAttachments;

    protected LocationModel $locationModel;
    protected AssetModel $assetModel;
    protected UnitModel $unitModel;

    protected UnitLocationModel $unitLocationModel;

    public function __construct()
    {
        $this->locationModel = new LocationModel();
        $this->assetModel = new AssetModel();
        $this->unitModel = new UnitModel();
        $this->unitLocationModel = new UnitLocationModel();
    }

    public function index()
    {
        // DataTables server-side.
        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'locations',
                static fn ($b) => $b->select('
                    locations.*,
                    (SELECT ii.id FROM item_images ii
                      WHERE ii.item_type = \'location\'
                        AND ii.item_id = locations.id
                      ORDER BY ii.id ASC LIMIT 1) AS image_id
                '),
                ['name', 'building', 'floor', 'description'],
                [1 => 'name', 2 => 'building', 3 => 'floor', 4 => 'description', 5 => 'is_active'],
                'name'
            ));
        }

        return view('locations/index', [
            'title' => 'Lokasi',
            // Dipakai oleh checkbox "unit" di modal create/edit.
            'units' => $this->unitModel->orderBy('name', 'ASC')->findAll(),
            // Batas resize/upload foto ruangan di modal detail.
            'imageConfig' => new ItemImages(),
        ]);
    }

    /**
     * Satu lokasi sebagai JSON, untuk mengisi modal edit DAN modal detail
     * dari halaman index.
     *
     * Modal detail hanya butuh ringkasan jumlah aset dan barang stok,
     * jadi baris lengkapnya tidak dikirim. Foto ruangan dikirim sebagai
     * image_id (gambar pertama) untuk ditampilkan di modal.
     */
    public function data($id)
    {
        $location = $this->locationModel->find($id);

        if (!$location) {
            return $this->respondError('Lokasi tidak ditemukan.', 404);
        }

        // Unit yang terkait ikut dikirim supaya modal edit bisa
        // mencentang checkbox yang benar tanpa fetch kedua.
        $location['unit_ids'] = array_map(
            'intval',
            array_column(
                $this->unitLocationModel->where('location_id', $id)->findAll(),
                'unit_id'
            )
        );

        $units = db_connect()
            ->table('unit_locations')
            ->select('units.id, units.name, units.code, units.is_active')
            ->join('units', 'units.id = unit_locations.unit_id')
            ->where('unit_locations.location_id', $id)
            ->orderBy('units.name', 'ASC')
            ->get()
            ->getResultArray();

        $location['asset_count'] = (int) $this->assetModel
            ->where('location_id', $id)
            ->countAllResults();

        // Jumlah jenis barang stok, bukan total kuantitas: satuannya
        // bisa berbeda-beda sehingga penjumlahan quantity menyesatkan.
        $location['stock_item_count'] = (int) db_connect()
            ->table('stock_items')
            ->where('location_id', $id)
            ->countAllResults();

        $location['image_id'] = (new ItemImageModel())
            ->firstIdFor(ItemImageModel::TYPE_LOCATION, (int) $id);

        $location['units'] = $units;

        return $this->respondAjax($location);
    }

    public function create()
    {
        // Form create berada di modal pada halaman index.
        return redirect()->to('/locations?create=1');
    }

    public function store()
    {
        $isAjax = $this->request->isAJAX();

        $this->locationModel->insert([
            'name'        => $this->request->getPost('name'),
            'building'    => $this->request->getPost('building'),
            'floor'       => $this->request->getPost('floor'),
            'room'        => $this->request->getPost('room'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? true : false,
        ]);

        if ($isAjax) {
            return $this->respondSuccess('Lokasi berhasil ditambahkan.', [
                'id' => $this->locationModel->getInsertID(),
            ]);
        }

        return redirect()->to('/locations')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        // Form edit berada di modal pada halaman index. Keberadaan
        // record divalidasi di data().
        return redirect()->to('/locations?edit=' . (int) $id);
    }

    public function update($id)
    {
        $isAjax = $this->request->isAJAX();

        $location = $this->locationModel->find($id);

        if (!$location) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $unitIds = $this->request->getPost('unit_ids') ?? [];

        $db = db_connect();

        $db->transStart();

        // Update lokasi
        $this->locationModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'building'    => $this->request->getPost('building'),
            'floor'       => $this->request->getPost('floor'),
            'room'        => $this->request->getPost('room'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        // Hapus relasi unit lama
        $this->unitLocationModel
            ->where('location_id', $id)
            ->delete();

        // Simpan relasi unit baru
        foreach ($unitIds as $unitId) {

            $this->unitLocationModel->insert([
                'unit_id'     => $unitId,
                'location_id' => $id,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Lokasi gagal diperbarui.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Lokasi gagal diperbarui.'
                );
        }

        if ($isAjax) {
            return $this->respondSuccess('Lokasi berhasil diperbarui.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()
            ->to('/locations')
            ->with(
                'success',
                'Lokasi berhasil diperbarui.'
            );
    }

    public function delete($id)
    {
        $isAjax = $this->request->isAJAX();

        $location = $this->locationModel->find($id);

        if (!$location) {
            if ($isAjax) {
                return $this->respondError('Lokasi tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->locationModel->delete($id)) {
            // item_images tidak punya cascade ke locations, jadi file dan
            // barisnya harus dibersihkan sendiri setelah induk terhapus.
            $this->purgeLocationImage((int) $id);
        }

        if ($isAjax) {
            return $this->respondSuccess('Lokasi berhasil dihapus.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()->to('/locations')
            ->with('success', 'Lokasi berhasil dihapus.');
    }

    /**
     * Unggah/ganti foto ruangan.
     *
     * Satu lokasi hanya menyimpan satu foto utama. Foto lama baru dihapus
     * setelah foto baru berhasil disimpan, supaya kegagalan upload tidak
     * menghilangkan foto yang sudah ada.
     */
    public function uploadPhoto($id)
    {
        $id = (int) $id;

        $location = $this->locationModel->find($id);

        if (!$location) {
            return $this->attachFail('Lokasi tidak ditemukan.', 404);
        }

        if (!can_access_location($id)) {
            return $this->attachFail(
                'Anda tidak memiliki akses ke lokasi tersebut.',
                403
            );
        }

        $config = new ItemImages();

        if ($limit = $this->attachServerLimitMessage($config)) {
            return $this->attachFail($limit);
        }

        $file = $this->request->getFile('image');

        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return $this->attachFail('Tidak ada gambar yang dipilih.');
        }

        $stored = $this->attachmentStore($file, $config);

        if (! $stored['ok']) {
            return $this->attachFail(
                'Gagal menyimpan gambar: ' . $stored['error']
            );
        }

        $imageModel = new ItemImageModel();
        $oldImages  = $imageModel->forItem(ItemImageModel::TYPE_LOCATION, $id);

        // Ganti foto secara atomik: baris lama dihapus dan baris baru
        // ditulis dalam satu transaksi. File lama baru dihapus setelah
        // commit supaya kegagalan database tidak meninggalkan baris yang
        // menunjuk ke file yang sudah hilang.
        $db = db_connect();
        $db->transStart();

        $imageModel
            ->where('item_type', ItemImageModel::TYPE_LOCATION)
            ->where('item_id', $id)
            ->delete();

        $inserted = $imageModel->insert([
            'item_type'     => ItemImageModel::TYPE_LOCATION,
            'item_id'       => $id,
            'title'         => 'Foto lokasi',
            'description'   => null,
            'stored_name'   => $stored['data']['stored_name'],
            'original_name' => $stored['data']['original_name'],
            'mime_type'     => $stored['data']['mime_type'],
            'extension'     => $stored['data']['extension'],
            'size_bytes'    => $stored['data']['size_bytes'],
            'uploaded_by'   => (int) session()->get('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $imageId = (int) $imageModel->getInsertID();

        $db->transComplete();

        if (! $inserted || $db->transStatus() === false) {
            // Jangan tinggalkan file yatim kalau baris database gagal dibuat.
            AttachmentStorage::for($config)->deleteFiles([$stored['data']]);

            return $this->attachFail('Gagal menyimpan data foto lokasi.', 500);
        }

        // Foto lama sudah tidak dipakai; hapus filenya setelah commit.
        if ($oldImages !== []) {
            AttachmentStorage::for($config)->deleteFiles($oldImages);
        }

        return $this->attachOk('Foto lokasi berhasil diunggah.', [
            'id' => $imageId,
        ]);
    }

    /**
     * Tampilkan foto ruangan. Berkas berada di luar docroot, jadi satu-satunya
     * jalan menuju isinya adalah endpoint ini.
     */
    public function showPhoto($imageId)
    {
        $image = (new ItemImageModel())->find((int) $imageId);

        if (! $image || $image['item_type'] !== ItemImageModel::TYPE_LOCATION) {
            throw PageNotFoundException::forPageNotFound();
        }

        $location = $this->locationModel->find((int) $image['item_id']);

        if (! $location || !can_access_location((int) $location['id'])) {
            // 404, bukan 403: jangan bocorkan bahwa foto ini ada tetapi
            // tidak boleh diakses.
            throw PageNotFoundException::forPageNotFound();
        }

        return AttachmentStorage::for(new ItemImages())->send(
            $image['stored_name'],
            $image['mime_type'],
            $image['original_name']
        );
    }

    public function deletePhoto($imageId)
    {
        $imageModel = new ItemImageModel();
        $image      = $imageModel->find((int) $imageId);

        if (! $image || $image['item_type'] !== ItemImageModel::TYPE_LOCATION) {
            return $this->attachFail('Foto lokasi tidak ditemukan.', 404);
        }

        $location = $this->locationModel->find((int) $image['item_id']);

        if (! $location || !can_access_location((int) $location['id'])) {
            return $this->attachFail(
                'Anda tidak memiliki akses ke foto lokasi tersebut.',
                403
            );
        }

        AttachmentStorage::for(new ItemImages())->deleteFiles([$image]);
        $imageModel->delete((int) $image['id']);

        return $this->attachOk('Foto lokasi berhasil dihapus.', [
            'id' => (int) $image['id'],
        ]);
    }

    /**
     * Hapus foto (file + baris) milik satu lokasi.
     */
    private function purgeLocationImage(int $locationId): void
    {
        $imageModel = new ItemImageModel();
        $images     = $imageModel->forItem(ItemImageModel::TYPE_LOCATION, $locationId);

        if ($images === []) {
            return;
        }

        AttachmentStorage::for(new ItemImages())->deleteFiles($images);

        $imageModel
            ->where('item_type', ItemImageModel::TYPE_LOCATION)
            ->where('item_id', $locationId)
            ->delete();
    }

    public function show($id)
    {
        $location = $this->locationModel->find($id);

        if (!$location) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Unit yang terkait dengan lokasi
        $units = $this->unitLocationModel
            ->select('units.id, units.name, units.code')
            ->join(
                'units',
                'units.id = unit_locations.unit_id'
            )
            ->where(
                'unit_locations.location_id',
                $id
            )
            ->orderBy(
                'units.name',
                'ASC'
            )
            ->findAll();

        // Aset yang berada di lokasi
        $assets = $this->assetModel
            ->select('
            assets.*,
            units.name as unit_name
        ')
            ->join(
                'units',
                'units.id = assets.unit_id',
                'left'
            )
            ->where(
                'assets.location_id',
                $id
            )
            ->orderBy(
                'assets.name',
                'ASC'
            )
            ->findAll();

        return view('locations/show', [
            'title'    => 'Detail Lokasi',
            'location' => $location,
            'units'    => $units,
            'assets'   => $assets,
        ]);
    }
}
