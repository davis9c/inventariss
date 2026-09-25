<?php

namespace App\Controllers;

use App\Libraries\AttachmentStorage;
use App\Models\AssetDocumentModel;
use App\Models\AssetModel;
use App\Models\CategoryModel;
use App\Models\InventoryTransactionModel;
use App\Models\ItemImageModel;
use App\Models\LocationModel;
use App\Models\StockOpnameDetailModel;
use App\Models\UnitModel;
use App\Traits\HandlesAttachments;
use App\Traits\HandlesItemImages;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\AssetDocuments;
use Config\ItemImages;

class Asset extends BaseController
{
    // Enam endpoint gambar (unggah/lihat/hapus) dan seluruh balasan
    // dual-mode milik dokumen berasal dari trait, supaya StockItem punya
    // kode yang sama persis tanpa perlu disalin.
    use HandlesAttachments;
    use HandlesItemImages;

    protected string $imageItemType   = ItemImageModel::TYPE_ASSET;
    protected string $imageBasePath   = 'assets';
    protected string $imageOwnerLabel = 'Barang';

    protected AssetModel $assetModel;
    protected CategoryModel $categoryModel;
    protected UnitModel $unitModel;
    protected LocationModel $locationModel;
    protected InventoryTransactionModel $transactionModel;
    protected StockOpnameDetailModel $stockOpnameDetailModel;
    protected AssetDocumentModel $assetDocumentModel;

    public function __construct()
    {
        $this->assetModel             = new AssetModel();
        $this->categoryModel          = new CategoryModel();
        $this->unitModel              = new UnitModel();
        $this->locationModel          = new LocationModel();
        $this->transactionModel       = new InventoryTransactionModel();
        $this->stockOpnameDetailModel = new StockOpnameDetailModel();
        $this->assetDocumentModel     = new AssetDocumentModel();
    }

    public function index()
    {
        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'assets',
                function ($b) {
                    $b->select('
                        assets.*,
                        categories.name as category_name,
                        units.name as unit_name,
                        locations.name as location_name,
                        (SELECT ii.id FROM item_images ii
                          WHERE ii.item_type = \'asset\'
                            AND ii.item_id = assets.id
                          ORDER BY ii.id ASC LIMIT 1) AS image_id
                    ')
                        ->join(
                            'categories',
                            'categories.id = assets.category_id',
                            'left'
                        )
                        ->join(
                            'units',
                            'units.id = assets.unit_id',
                            'left'
                        )
                        ->join(
                            'locations',
                            'locations.id = assets.location_id',
                            'left'
                        );

                    if (has_location_restriction()) {
                        $b->whereIn('assets.location_id', user_location_ids());
                    }
                },
                [
                    'assets.asset_code',
                    'assets.name',
                    'assets.brand',
                    'assets.model',
                    'assets.serial_number',
                    'categories.name',
                    'units.name',
                    'locations.name',
                ],
                [
                    0 => 'assets.asset_code',
                    1 => 'assets.name',
                    2 => 'categories.name',
                    3 => 'units.name',
                    4 => 'locations.name',
                    5 => 'assets.condition_status',
                    6 => 'assets.asset_status',
                ],
                'assets.name'
            ));
        }

        $builder = $this->assetModel
            ->select('
                assets.*,
                categories.name as category_name,
                units.name as unit_name,
                locations.name as location_name
            ')
            ->join(
                'categories',
                'categories.id = assets.category_id',
                'left'
            )
            ->join(
                'units',
                'units.id = assets.unit_id',
                'left'
            )
            ->join(
                'locations',
                'locations.id = assets.location_id',
                'left'
            );

        if (has_location_restriction()) {
            $builder->whereIn(
                'assets.location_id',
                user_location_ids()
            );
        }

        $assets = $builder
            ->orderBy('assets.name', 'ASC')
            ->findAll();

        $locationBuilder = $this->locationModel
            ->where('is_active', 1);

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        return view('assets/index', [
            'title'      => 'Barang / Aset',
            'assets'     => $assets,
            'categories' => $this->categoryModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'units' => $this->unitModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'locations' => $locationBuilder
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function create()
    {
        // Form create berada di modal pada halaman index.
        return redirect()->to('/assets?create=1');
    }

    public function store()
    {
        $isAjax = $this->request->isAJAX();

        if (!$this->validate($this->validationRules())) {
            if ($isAjax) {
                return $this->respondErrors('Data tidak valid.', $this->validator->getErrors());
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $locationId = (int) $this->request->getPost('location_id');

        if (!can_access_location($locationId)) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke lokasi tersebut.', 403);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'errors',
                    ['location_id' => 'Anda tidak memiliki akses ke lokasi tersebut.']
                );
        }

        $db = db_connect();

        $db->transStart();

        $this->assetModel->insert([
            'asset_code'        => $this->request->getPost('asset_code'),
            'name'              => $this->request->getPost('name'),
            'category_id'       => $this->request->getPost('category_id'),
            'unit_id'           => $this->request->getPost('unit_id'),
            'location_id'       => $locationId,
            'brand'             => $this->request->getPost('brand'),
            'model'             => $this->request->getPost('model'),
            'serial_number'     => $this->request->getPost('serial_number'),
            'acquisition_year'  => $this->request->getPost('acquisition_year') ?: null,
            'acquisition_price' => $this->request->getPost('acquisition_price') ?: 0,
            'condition_status'  => $this->request->getPost('condition_status'),
            'asset_status'      => $this->request->getPost('asset_status'),
            'description'       => $this->request->getPost('description'),
        ]);

        // Catat perolehan barang
        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => date('Y-m-d'),
            'transaction_type' => 'Perolehan',
            'item_type'        => 'Aset',
            'asset_id'         => $this->assetModel->getInsertID(),
            'quantity'         => 1,
            'to_location_id'   => $locationId,
            'reason'           => 'Perolehan barang',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Barang gagal disimpan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Barang gagal disimpan.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Barang berhasil ditambahkan.', [
                'id' => $this->assetModel->getInsertID(),
            ]);
        }

        return redirect()
            ->to('/assets')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit($id)
    {
        // Form edit berada di modal pada halaman index. Penjagaan akses
        // lokasi TIDAK dihapus: redirect ditolak lebih dulu kalau asetnya
        // di lokasi yang tidak boleh diakses, dan show()?format=json
        // (yang mengisi modal) menjaganya lagi.
        $asset = $this->assetModel->find($id);

        if (!$asset) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($asset['location_id'])) {
            return redirect()
                ->to('/assets')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke aset tersebut.'
                );
        }

        return redirect()->to('/assets?edit=' . (int) $id);
    }

    public function update($id)
    {
        $isAjax = $this->request->isAJAX();

        $asset = $this->assetModel->find($id);

        if (!$asset) {
            if ($isAjax) {
                return $this->respondError('Barang tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($asset['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke aset tersebut.', 403);
            }

            return redirect()
                ->to('/assets')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke aset tersebut.'
                );
        }

        if (!$this->validate($this->validationRules($id))) {
            if ($isAjax) {
                return $this->respondErrors('Data tidak valid.', $this->validator->getErrors());
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $locationId = (int) $this->request->getPost('location_id');

        if (!can_access_location($locationId)) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke lokasi tersebut.', 403);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'errors',
                    ['location_id' => 'Anda tidak memiliki akses ke lokasi tersebut.']
                );
        }

        $this->assetModel->update($id, [
            'asset_code'        => $this->request->getPost('asset_code'),
            'name'              => $this->request->getPost('name'),
            'category_id'       => $this->request->getPost('category_id'),
            'unit_id'           => $this->request->getPost('unit_id'),
            'location_id'       => $locationId,
            'brand'             => $this->request->getPost('brand'),
            'model'             => $this->request->getPost('model'),
            'serial_number'     => $this->request->getPost('serial_number'),
            'acquisition_year'  => $this->request->getPost('acquisition_year') ?: null,
            'acquisition_price' => $this->request->getPost('acquisition_price') ?: 0,
            'condition_status'  => $this->request->getPost('condition_status'),
            'asset_status'      => $this->request->getPost('asset_status'),
            'description'       => $this->request->getPost('description'),
        ]);

        if ($isAjax) {
            return $this->respondSuccess('Barang berhasil diperbarui.');
        }

        return redirect()
            ->to('/assets')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function delete($id)
    {
        $isAjax = $this->request->isAJAX();

        $asset = $this->assetModel->find($id);

        if (!$asset) {
            if ($isAjax) {
                return $this->respondError('Barang tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($asset['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke aset tersebut.', 403);
            }

            return redirect()
                ->to('/assets')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke aset tersebut.'
                );
        }

        // Jangan hapus barang jika masih memiliki riwayat pengelolaan
        $usedCount = $this->transactionModel
            ->where('asset_id', $id)
            ->countAllResults();

        $usedCount += $this->stockOpnameDetailModel
            ->where('asset_id', $id)
            ->countAllResults();

        if ($usedCount > 0) {
            $message = 'Barang tidak dapat dihapus karena memiliki riwayat pergerakan atau stock opname.';

            if ($isAjax) {
                return $this->respondError($message, 409);
            }

            return redirect()
                ->to('/assets')
                ->with('error', $message);
        }

        // Hapus semua lampiran (dokumen + gambar) SEBELUM baris aset
        // dihapus. Cascade DB membersihkan baris asset_documents, tapi
        // cascade itu tidak menyentuh filesystem; item_images tidak punya
        // cascade sama sekali. Tanpa pemanggilan ini berkas tertinggal.
        $this->purgeAssetAttachments((int) $id);

        $this->assetModel->delete($id);

        if ($isAjax) {
            return $this->respondSuccess('Barang berhasil dihapus.');
        }

        return redirect()
            ->to('/assets')
            ->with('success', 'Barang berhasil dihapus.');
    }

    public function show($id)
    {
        $asset = $this->assetModel
            ->select('
                assets.*,
                categories.name as category_name,
                units.name as unit_name,
                locations.name as location_name
            ')
            ->join(
                'categories',
                'categories.id = assets.category_id',
                'left'
            )
            ->join(
                'units',
                'units.id = assets.unit_id',
                'left'
            )
            ->join(
                'locations',
                'locations.id = assets.location_id',
                'left'
            )
            ->find($id);

        if (!$asset) {
            if ($this->request->getGet('format') === 'json') {
                return $this->respondError('Barang tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        // Satu barang sebagai JSON, untuk mengisi modal edit dari
        // ?edit=<id>. Penjagaan akses lokasi tetap berlaku di sini,
        // sama seperti di halaman edit yang dulu.
        if ($this->request->getGet('format') === 'json') {
            if (!can_access_location($asset['location_id'])) {
                return $this->respondError(
                    'Anda tidak memiliki akses ke aset tersebut.',
                    403
                );
            }

            return $this->respondAjax($asset);
        }

        // Cek akses lokasi
        if (!can_access_location($asset['location_id'])) {
            return redirect()
                ->to('/assets')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke lokasi barang tersebut.'
                );
        }

        /*
         * Riwayat pergerakan aset (satu sumber histori)
         */
        $movements = $this->transactionModel
            ->select('
                inventory_transactions.*,
                fl.name as from_location_name,
                tl.name as to_location_name,
                users.name as created_by_name
            ')
            ->join(
                'locations fl',
                'fl.id = inventory_transactions.from_location_id',
                'left'
            )
            ->join(
                'locations tl',
                'tl.id = inventory_transactions.to_location_id',
                'left'
            )
            ->join(
                'users',
                'users.id = inventory_transactions.created_by',
                'left'
            )
            ->where('inventory_transactions.asset_id', $id)
            ->orderBy('inventory_transactions.transaction_date', 'DESC')
            ->orderBy('inventory_transactions.id', 'DESC')
            ->findAll();

        /*
         * Riwayat stock opname
         */
        $stockOpnames = $this->stockOpnameDetailModel
            ->select('
                stock_opname_details.*,
                stock_opnames.opname_date,
                CASE WHEN stock_opname_details.is_found = 1
                     THEN \'Ditemukan\'
                     ELSE \'Tidak Ditemukan\'
                END as result
            ')
            ->join(
                'stock_opnames',
                'stock_opnames.id = stock_opname_details.stock_opname_id'
            )
            ->where('stock_opname_details.asset_id', $id)
            ->orderBy('stock_opnames.opname_date', 'DESC')
            ->findAll();

        $locationBuilder = $this->locationModel
            ->where('is_active', 1);

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        return view('assets/show', [
            'title'        => 'Detail Barang',
            'asset'        => $asset,
            'movements'    => $movements,
            'stockOpnames' => $stockOpnames,
            'documents'    => $this->assetDocumentModel->forAsset((int) $id),
            'images'       => (new ItemImageModel())->forItem(
                ItemImageModel::TYPE_ASSET,
                (int) $id
            ),
            'imageConfig'  => new ItemImages(),
            'documentConfig' => new AssetDocuments(),
            'categories'   => $this->categoryModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'units' => $this->unitModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'locations' => $locationBuilder
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function assetOut($id)
    {
        $asset = $this->assetModel->find($id);

        if (!$asset) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($asset['location_id'])) {
            return redirect()
                ->to('/assets')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke aset tersebut.'
                );
        }

        if ($asset['asset_status'] === 'Keluar Perusahaan') {
            return redirect()
                ->to('/assets/' . $id)
                ->with(
                    'error',
                    'Barang sudah berada di luar tanggung jawab perusahaan.'
                );
        }

        return view('assets/asset_out', [
            'title' => 'Barang Keluar Perusahaan',
            'asset' => $asset,
        ]);
    }

    public function storeAssetOut($id)
    {
        $isAjax = $this->request->isAJAX();

        $asset = $this->assetModel->find($id);

        if (!$asset) {
            if ($isAjax) {
                return $this->respondError('Barang tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($asset['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke aset tersebut.', 403);
            }

            return redirect()
                ->to('/assets')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke aset tersebut.'
                );
        }

        if (!$this->validate([
            'transaction_date' => 'required|valid_date',
            'reason'           => 'permit_empty|max_length[255]',
            'notes'            => 'permit_empty',
        ])) {
            if ($isAjax) {
                return $this->respondErrors('Data tidak valid.', $this->validator->getErrors());
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = db_connect();

        $db->transStart();

        $this->assetModel->update($id, [
            'asset_status' => 'Keluar Perusahaan',
        ]);

        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_type' => 'Keluar Perusahaan',
            'item_type'        => 'Aset',
            'asset_id'         => $id,
            'quantity'         => 1,
            'from_location_id' => $asset['location_id'],
            'reason'           => $this->request->getPost('reason'),
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => session()->get('user_id'),
        ]);

        $transactionId = $this->transactionModel->getInsertID();

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Transaksi gagal disimpan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Transaksi gagal disimpan.');
        }

        if ($isAjax) {
            return $this->respondSuccess(
                'Barang dicatat keluar dari tanggung jawab perusahaan.',
                [
                    'asset_status' => 'Keluar Perusahaan',
                    'transaction'  => $this->transactionRow($transactionId),
                ]
            );
        }

        return redirect()
            ->to('/assets/' . $id)
            ->with('success', 'Barang dicatat keluar dari tanggung jawab perusahaan.');
    }

    public function assetReturn($id)
    {
        $asset = $this->assetModel->find($id);

        if (!$asset) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($asset['location_id'])) {
            return redirect()
                ->to('/assets')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke aset tersebut.'
                );
        }

        if ($asset['asset_status'] !== 'Keluar Perusahaan') {
            return redirect()
                ->to('/assets/' . $id)
                ->with(
                    'error',
                    'Barang masih berada dalam tanggung jawab perusahaan.'
                );
        }

        return view('assets/asset_return', [
            'title' => 'Pengembalian Barang',
            'asset' => $asset,
        ]);
    }

    public function storeAssetReturn($id)
    {
        $isAjax = $this->request->isAJAX();

        $asset = $this->assetModel->find($id);

        if (!$asset) {
            if ($isAjax) {
                return $this->respondError('Barang tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($asset['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke aset tersebut.', 403);
            }

            return redirect()
                ->to('/assets')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke aset tersebut.'
                );
        }

        if (!$this->validate([
            'transaction_date' => 'required|valid_date',
            'notes'            => 'permit_empty',
        ])) {
            if ($isAjax) {
                return $this->respondErrors('Data tidak valid.', $this->validator->getErrors());
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = db_connect();

        $db->transStart();

        $this->assetModel->update($id, [
            'asset_status' => 'Aktif',
        ]);

        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_type' => 'Pengembalian',
            'item_type'        => 'Aset',
            'asset_id'         => $id,
            'quantity'         => 1,
            'to_location_id'   => $asset['location_id'],
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => session()->get('user_id'),
        ]);

        $transactionId = $this->transactionModel->getInsertID();

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Transaksi gagal disimpan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Transaksi gagal disimpan.');
        }

        if ($isAjax) {
            return $this->respondSuccess(
                'Barang kembali ke dalam tanggung jawab perusahaan.',
                [
                    'asset_status' => 'Aktif',
                    'transaction'  => $this->transactionRow($transactionId),
                ]
            );
        }

        return redirect()
            ->to('/assets/' . $id)
            ->with('success', 'Barang kembali ke dalam tanggung jawab perusahaan.');
    }

    /**
     * Baris transaksi lengkap (dengan nama lokasi & pembuat) untuk refresh
     * riwayat via AJAX.
     */
    private function transactionRow(int $transactionId): ?array
    {
        return $this->transactionModel
            ->select('
                inventory_transactions.*,
                fl.name as from_location_name,
                tl.name as to_location_name,
                users.name as created_by_name
            ')
            ->join(
                'locations fl',
                'fl.id = inventory_transactions.from_location_id',
                'left'
            )
            ->join(
                'locations tl',
                'tl.id = inventory_transactions.to_location_id',
                'left'
            )
            ->join(
                'users',
                'users.id = inventory_transactions.created_by',
                'left'
            )
            ->where('inventory_transactions.id', $transactionId)
            ->first();
    }

    private function validationRules(?int $id = null): array
    {
        $assetCodeRule = $id
            ? "required|is_unique[assets.asset_code,id,{$id}]"
            : 'required|is_unique[assets.asset_code]';

        return [
            'asset_code'        => $assetCodeRule,
            'name'              => 'required',
            'category_id'       => 'required|is_natural_no_zero',
            'unit_id'           => 'required|is_natural_no_zero',
            'location_id'       => 'required|is_natural_no_zero',
            'acquisition_year'  => 'permit_empty|numeric|greater_than_equal_to[1900]|less_than_equal_to[' . date('Y') . ']',
            'acquisition_price' => 'permit_empty|decimal',
            'condition_status'  => 'required|in_list[Baik,Rusak Ringan,Rusak Berat]',
            'asset_status'      => 'required|in_list[Aktif,Dipinjam,Tidak Digunakan,Keluar Perusahaan]',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Dokumen (garansi, bukti pembelian, sertifikat)
    // ─────────────────────────────────────────────────────────────
    //
    //  Semua mekanisme berkas -- validasi finfo, nama acak, header
    //  Content-Disposition, penghapusan -- ada di AttachmentStorage.
    //  Bagian di sini hanya soal aturan dokumen: judul wajib, batas
    //  jumlah, dan siapa yang boleh mengakses.

    /**
     * Dipakai trait HandlesItemImages untuk mencari induk lampirannya.
     */
    protected function findAttachmentOwner(int $id): ?array
    {
        $asset = $this->assetModel->find($id);

        return $asset === null ? null : $asset;
    }

    /**
     * Muat aset + pastikan pemanggil berhak mengakses lokasinya.
     *
     * Mengembalikan [asset, null] kalau aman, [null, response] kalau tidak.
     * Guard ini wajib ada di SEMUA endpoint lampiran, bukan cuma di upload:
     * tanpa itu user tanpa akses lokasi bisa menebak id dan mengunduh
     * lampiran milik orang lain.
     *
     * @return array{0:array|null, 1:mixed}
     */
    private function guardAsset(int $id): array
    {
        $isAjax = $this->request->isAJAX();
        $asset  = $this->assetModel->find($id);

        if (! $asset) {
            if ($isAjax) {
                return [null, $this->respondError('Barang tidak ditemukan.', 404)];
            }

            return [null, throw PageNotFoundException::forPageNotFound()];
        }

        if (! can_access_location($asset['location_id'])) {
            if ($isAjax) {
                return [null, $this->respondError('Anda tidak memiliki akses ke aset tersebut.', 403)];
            }

            return [null, redirect()->to('/assets')
                ->with('error', 'Anda tidak memiliki akses ke aset tersebut.')];
        }

        return [$asset, null];
    }

    /**
     * Unggah satu dokumen untuk sebuah aset.
     *
     * Satu berkas per unggahan: tiap dokumen punya judulnya sendiri, dan
     * judul itulah yang membuat dokumen dapat dicari tanpa harus membuka
     * berkasnya.
     */
    public function uploadDocuments($id)
    {
        $cfg = new AssetDocuments();
        $id  = (int) $id;

        [$asset, $blocked] = $this->guardAsset($id);

        if ($blocked !== null) {
            return $blocked;
        }

        if ($this->assetDocumentModel->countForAsset($id) >= $cfg->maxFiles) {
            return $this->attachFail(
                'Jumlah dokumen sudah mencapai batas ' . $cfg->maxFiles . ' per barang.'
            );
        }

        if ($limit = $this->attachServerLimitMessage($cfg)) {
            return $this->attachFail($limit);
        }

        $title = trim((string) ($this->request->getPost('title') ?? ''));

        if ($title === '') {
            return $this->attachFail('Judul dokumen wajib diisi.');
        }

        if (mb_strlen($title) > 150) {
            return $this->attachFail('Judul dokumen maksimal 150 karakter.');
        }

        $file = $this->request->getFile('document');

        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return $this->attachFail('Tidak ada berkas yang dipilih.');
        }

        $result = $this->attachmentStore($file, $cfg);

        if (! $result['ok']) {
            return $this->attachFail('Ditolak: ' . $result['error']);
        }

        $this->assetDocumentModel->insert([
            'asset_id'      => $id,
            'title'         => $title,
            'description'   => trim((string) ($this->request->getPost('description') ?? '')) ?: null,
            'stored_name'   => $result['data']['stored_name'],
            'original_name' => $result['data']['original_name'],
            'mime_type'     => $result['data']['mime_type'],
            'extension'     => $result['data']['extension'],
            'size_bytes'    => $result['data']['size_bytes'],
            'uploaded_by'   => (int) session()->get('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return $this->attachOk('Dokumen berhasil diunggah.', [
            'id' => (int) $this->assetDocumentModel->getInsertID(),
        ]);
    }

    /**
     * Kirim satu dokumen ke browser.
     *
     * Wajib melewati auth + can_access_location. File dikirim dari luar
     * docroot, jadi ini satu-satunya jalan menuju berkas tersimpan.
     */
    public function downloadDocument($docId)
    {
        $doc = $this->assetDocumentModel->find((int) $docId);

        if (! $doc) {
            throw PageNotFoundException::forPageNotFound();
        }

        $asset = $this->assetModel->find((int) $doc['asset_id']);

        if (! $asset || ! can_access_location($asset['location_id'])) {
            // 404, bukan 403: Jangan bocorkan bahwa dokumen ini ADA tapi
            // tidak boleh diakses.
            throw PageNotFoundException::forPageNotFound();
        }

        return AttachmentStorage::documents()->send(
            $doc['stored_name'],
            $doc['mime_type'],
            $doc['original_name']
        );
    }

    /**
     * Hapus satu dokumen: berkas di disk lalu barisnya.
     */
    public function deleteDocument($docId)
    {
        $doc = $this->assetDocumentModel->find((int) $docId);

        if (! $doc) {
            return $this->attachFail('Dokumen tidak ditemukan.', 404);
        }

        $asset = $this->assetModel->find((int) $doc['asset_id']);

        if (! $asset || ! can_access_location($asset['location_id'])) {
            return $this->attachFail('Anda tidak memiliki akses ke dokumen tersebut.', 403);
        }

        // Berkas dulu, baru barisnya: kalau urannya dibalik dan unlink
        // gagal, berkasnya jadi yatim yang tidak bisa dilacak lewat DB.
        AttachmentStorage::documents()->deleteFiles([$doc]);

        $this->assetDocumentModel->delete((int) $doc['id']);

        return $this->attachOk('Dokumen berhasil dihapus.', ['id' => (int) $doc['id']]);
    }

    /**
     * Hapus seluruh lampiran milik sebuah aset: dokumen dan gambarnya.
     *
     * Dipanggil dari delete() sebelum baris aset dihapus. Cascade database
     * membersihkan baris asset_documents, tapi cascade itu tidak menyentuh
     * filesystem; item_images tidak punya cascade sama sekali.
     */
    private function purgeAssetAttachments(int $assetId): void
    {
        AttachmentStorage::documents()->deleteFiles(
            $this->assetDocumentModel->forAsset($assetId)
        );

        $this->purgeOwnedAttachments(
            self::KIND_IMAGE,
            ItemImageModel::TYPE_ASSET,
            $assetId
        );
    }
}
