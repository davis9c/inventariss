<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\CategoryModel;
use App\Models\InventoryTransactionModel;
use App\Models\InventoryDocumentModel;
use App\Models\InventoryPhotoModel;
use App\Models\LocationModel;
use App\Models\StockOpnameDetailModel;
use App\Models\UnitModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Asset extends BaseController
{
    protected AssetModel $assetModel;
    protected CategoryModel $categoryModel;
    protected UnitModel $unitModel;
    protected LocationModel $locationModel;
    protected InventoryTransactionModel $transactionModel;
    protected StockOpnameDetailModel $stockOpnameDetailModel;

    public function __construct()
    {
        $this->assetModel             = new AssetModel();
        $this->categoryModel          = new CategoryModel();
        $this->unitModel              = new UnitModel();
        $this->locationModel          = new LocationModel();
        $this->transactionModel       = new InventoryTransactionModel();
        $this->stockOpnameDetailModel = new StockOpnameDetailModel();
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
        $locationBuilder = $this->locationModel
            ->where('is_active', 1);

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        return view('assets/create', [
            'title'      => 'Tambah Barang',
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
            'acquisition_source' => $this->request->getPost('acquisition_source'),
            'acquisition_date' => $this->request->getPost('acquisition_date') ?: null,
            'acquisition_document_number' => $this->request->getPost('acquisition_document_number'),
            'supplier_name' => $this->request->getPost('supplier_name'),
            'funding_source' => $this->request->getPost('funding_source'),
            'acquisition_notes' => $this->request->getPost('acquisition_notes'),
            'condition_status'  => $this->request->getPost('condition_status'),
            'asset_status'      => $this->request->getPost('asset_status'),
            'description'       => $this->request->getPost('description'),
        ]);

        // Catat perolehan barang
        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => $this->request->getPost('acquisition_date') ?: date('Y-m-d'),
            'transaction_type' => 'Perolehan',
            'item_type'        => 'Aset',
            'asset_id'         => $this->assetModel->getInsertID(),
            'quantity'         => 1,
            'to_location_id'   => $locationId,
            'reason'           => 'Perolehan barang: ' . ($this->request->getPost('acquisition_source') ?: 'Tidak dicatat'),
            'document_number'  => $this->request->getPost('acquisition_document_number'),
            'recipient_name'   => $this->request->getPost('supplier_name'),
            'notes'            => $this->request->getPost('acquisition_notes'),
            'created_by'       => session()->get('user_id'),
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

        $this->audit('CREATE', 'asset', $this->assetModel->getInsertID(), null, $this->assetModel->find($this->assetModel->getInsertID()));

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

        if (!$this->mayEdit($asset)) {
            return $this->editLockedResponse(false);
        }

        $locationBuilder = $this->locationModel
            ->where('is_active', 1);

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        return view('assets/edit', [
            'title'      => 'Edit Barang',
            'asset'      => $asset,
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

        if (!$this->mayEdit($asset)) {
            return $this->editLockedResponse($isAjax);
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

        $before = $asset;
        $wasLocked = !empty($before['created_at']) && strtotime($before['created_at']) + (96 * 3600) < time();
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
            'acquisition_source' => $this->request->getPost('acquisition_source'),
            'acquisition_date' => $this->request->getPost('acquisition_date') ?: null,
            'acquisition_document_number' => $this->request->getPost('acquisition_document_number'),
            'supplier_name' => $this->request->getPost('supplier_name'),
            'funding_source' => $this->request->getPost('funding_source'),
            'acquisition_notes' => $this->request->getPost('acquisition_notes'),
            'condition_status'  => $this->request->getPost('condition_status'),
            'asset_status'      => $this->request->getPost('asset_status'),
            'description'       => $this->request->getPost('description'),
        ]);
        $this->audit('UPDATE', 'asset', (int) $id, $before, $this->assetModel->find($id), isSuperAdmin() && $wasLocked ? 'Koreksi Super Admin setelah batas 96 jam.' : null);

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

        if (!$this->mayEdit($asset)) {
            return $this->editLockedResponse($isAjax);
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

        $this->assetModel->delete($id);
        $this->audit('SOFT_DELETE', 'asset', (int) $id, $asset);

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
            throw PageNotFoundException::forPageNotFound();
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
            'documents' => (new InventoryDocumentModel())->where(['owner_type' => 'asset', 'owner_id' => $id])->orderBy('created_at', 'DESC')->findAll(),
            'photos' => (new InventoryPhotoModel())->where(['owner_type' => 'asset', 'owner_id' => $id])->orderBy('created_at', 'DESC')->findAll(),
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
            'outbound_type'    => 'required|max_length[50]',
            'recipient_name'   => 'required|max_length[150]',
            'destination_unit' => 'permit_empty|max_length[150]',
            'document_number'  => 'permit_empty|max_length[100]',
            'handed_over_by'   => 'permit_empty|max_length[150]',
            'received_by'      => 'permit_empty|max_length[150]',
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
            'outbound_type'    => $this->request->getPost('outbound_type'),
            'recipient_name'   => $this->request->getPost('recipient_name'),
            'destination_unit' => $this->request->getPost('destination_unit'),
            'document_number'  => $this->request->getPost('document_number'),
            'handed_over_by'   => $this->request->getPost('handed_over_by'),
            'received_by'      => $this->request->getPost('received_by'),
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
        $this->audit('CREATE', 'inventory_transaction', $transactionId, null, $this->transactionModel->find($transactionId));

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

        $acquisitionSourceRule = $id
            ? 'permit_empty|max_length[50]'
            : 'required|max_length[50]';
        $acquisitionDateRule = $id
            ? 'permit_empty|valid_date'
            : 'required|valid_date';

        return [
            'asset_code'        => $assetCodeRule,
            'name'              => 'required',
            'category_id'       => 'required|is_natural_no_zero',
            'unit_id'           => 'required|is_natural_no_zero',
            'location_id'       => 'required|is_natural_no_zero',
            'acquisition_year'  => 'permit_empty|numeric|greater_than_equal_to[2000]|less_than_equal_to[' . ((int) date('Y') + 2) . ']',
            'acquisition_price' => 'permit_empty|decimal|greater_than_equal_to[1000]',
            'acquisition_source' => $acquisitionSourceRule,
            'acquisition_date' => $acquisitionDateRule,
            'acquisition_document_number' => 'permit_empty|max_length[100]',
            'supplier_name' => 'permit_empty|max_length[150]',
            'funding_source' => 'permit_empty|max_length[150]',
            'condition_status'  => 'required|in_list[Baik,Rusak Ringan,Rusak Berat]',
            'asset_status'      => 'required|in_list[Aktif,Dipinjam,Tidak Digunakan,Keluar Perusahaan]',
        ];
    }
}
