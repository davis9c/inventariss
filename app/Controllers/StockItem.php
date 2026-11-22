<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\CategoryModel;
use App\Models\InventoryTransactionModel;
use App\Models\ItemImageModel;
use App\Models\LocationModel;
use App\Models\StockItemModel;
use App\Models\UnitModel;
use App\Traits\HandlesAttachments;
use App\Traits\HandlesItemImages;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\ItemImages;

class StockItem extends BaseController
{
    // Enam endpoint gambar yang identik dengan milik Aset -- lihat
    // HandlesItemImages. Yang berbeda hanya tabel induk dan nama barang.
    use HandlesAttachments;
    use HandlesItemImages;

    protected string $imageItemType   = ItemImageModel::TYPE_STOCK_ITEM;
    protected string $imageBasePath   = 'stock-items';
    protected string $imageOwnerLabel = 'Barang stok';

    protected AssetModel $assetModel;
    protected StockItemModel $itemModel;
    protected InventoryTransactionModel $transactionModel;
    protected CategoryModel $categoryModel;
    protected UnitModel $unitModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->assetModel       = new AssetModel();
        $this->itemModel        = new StockItemModel();
        $this->transactionModel = new InventoryTransactionModel();
        $this->categoryModel    = new CategoryModel();
        $this->unitModel        = new UnitModel();
        $this->locationModel    = new LocationModel();
    }

    /**
     * Dipakai trait HandlesItemImages untuk mencari induk lampirannya.
     */
    protected function findAttachmentOwner(int $id): ?array
    {
        $item = $this->itemModel->find($id);

        return $item === null ? null : $item;
    }

    public function index()
    {
        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'stock_items',
                function ($b) {
                    $b->select('
                        stock_items.*,
                        categories.name as category_name,
                        units.name as unit_name,
                        locations.name as location_name,
                        (SELECT ii.id FROM item_images ii
                          WHERE ii.item_type = \'stock_item\'
                            AND ii.item_id = stock_items.id
                          ORDER BY ii.id ASC LIMIT 1) AS image_id
                    ')
                        ->join(
                            'categories',
                            'categories.id = stock_items.category_id',
                            'left'
                        )
                        ->join(
                            'units',
                            'units.id = stock_items.unit_id',
                            'left'
                        )
                        ->join(
                            'locations',
                            'locations.id = stock_items.location_id',
                            'left'
                        );

                    if (has_location_restriction()) {
                        $b->whereIn('stock_items.location_id', user_location_ids());
                    }
                },
                [
                    'stock_items.item_code',
                    'stock_items.name',
                    'categories.name',
                    'units.name',
                    'locations.name',
                ],
                [
                    1 => 'stock_items.item_code',
                    2 => 'stock_items.name',
                    3 => 'categories.name',
                    4 => 'units.name',
                    5 => 'stock_items.quantity',
                    6 => 'locations.name',
                    7 => 'stock_items.is_active',
                    8 => 'stock_items.created_at',
                ],
                'stock_items.created_at',
                'DESC'
            ));
        }

        $builder = $this->itemModel
            ->select('
                stock_items.*,
                categories.name as category_name,
                units.name as unit_name,
                locations.name as location_name
            ')
            ->join(
                'categories',
                'categories.id = stock_items.category_id',
                'left'
            )
            ->join(
                'units',
                'units.id = stock_items.unit_id',
                'left'
            )
            ->join(
                'locations',
                'locations.id = stock_items.location_id',
                'left'
            );

        if (has_location_restriction()) {
            $builder->whereIn(
                'stock_items.location_id',
                user_location_ids()
            );
        }

        $items = $builder
            ->orderBy('stock_items.name', 'ASC')
            ->findAll();

        $locationBuilder = $this->locationModel
            ->where('is_active', 1);

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        return view('stock_items/index', [
            'title'      => 'Barang Stok',
            'items'      => $items,
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
        return redirect()->to('/stock-items?create=1');
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

        $itemCode = make_unique_code(
            'stock_items',
            'item_code',
            (string) $this->request->getPost('item_code')
        );
        $codeChanged = $itemCode !== (string) $this->request->getPost('item_code');

        $this->itemModel->insert([
            'item_code'   => $itemCode,
            'name'        => $this->request->getPost('name'),
            'category_id' => $this->request->getPost('category_id'),
            'unit_id'     => $this->request->getPost('unit_id'),
            'location_id' => $locationId,
            'satuan'      => $this->request->getPost('satuan'),
            'quantity'    => 0,
            'description' => $this->request->getPost('description'),
            'is_active'   => 1,
        ]);

        $successMessage = 'Barang stok berhasil ditambahkan.';
        if ($codeChanged) {
            $successMessage .= ' Kode barang sudah ada, otomatis diubah menjadi ' . $itemCode . '.';
        }

        if ($isAjax) {
            return $this->respondSuccess($successMessage, [
                'id'        => $this->itemModel->getInsertID(),
                'item_code' => $itemCode,
            ]);
        }

        return redirect()
            ->to('/stock-items')
            ->with('success', $successMessage);
    }

    public function edit($id)
    {
        // Form edit berada di modal pada halaman index. Penjagaan akses
        // lokasi TIDAK dihapus; show()?format=json menjaganya lagi saat
        // modal diisi.
        $item = $this->itemModel->find($id);

        if (!$item) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        return redirect()->to('/stock-items?edit=' . (int) $id);
    }

    public function update($id)
    {
        $isAjax = $this->request->isAJAX();

        $item = $this->itemModel->find($id);

        if (!$item) {
            if ($isAjax) {
                return $this->respondError('Barang stok tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke barang stok tersebut.', 403);
            }

            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
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

        $itemCode = make_unique_code(
            'stock_items',
            'item_code',
            (string) $this->request->getPost('item_code'),
            (int) $id
        );
        $codeChanged = $itemCode !== (string) $this->request->getPost('item_code');

        // Stok hanya berubah melalui transaksi masuk/keluar
        $this->itemModel->update($id, [
            'item_code'   => $itemCode,
            'name'        => $this->request->getPost('name'),
            'category_id' => $this->request->getPost('category_id'),
            'unit_id'     => $this->request->getPost('unit_id'),
            'location_id' => $locationId,
            'satuan'      => $this->request->getPost('satuan'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        $successMessage = 'Barang stok berhasil diperbarui.';
        if ($codeChanged) {
            $successMessage .= ' Kode barang sudah ada, otomatis diubah menjadi ' . $itemCode . '.';
        }

        if ($isAjax) {
            return $this->respondSuccess($successMessage);
        }

        return redirect()
            ->to('/stock-items')
            ->with('success', $successMessage);
    }

    public function show($id)
    {
        $item = $this->itemModel
            ->select('
                stock_items.*,
                categories.name as category_name,
                units.name as unit_name,
                locations.name as location_name
            ')
            ->join(
                'categories',
                'categories.id = stock_items.category_id',
                'left'
            )
            ->join(
                'units',
                'units.id = stock_items.unit_id',
                'left'
            )
            ->join(
                'locations',
                'locations.id = stock_items.location_id',
                'left'
            )
            ->find($id);

        if (!$item) {
            if ($this->request->getGet('format') === 'json') {
                return $this->respondError('Barang stok tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        // Satu barang stok sebagai JSON, untuk mengisi modal edit dari
        // ?edit=<id>. Penjagaan akses lokasi tetap berlaku.
        if ($this->request->getGet('format') === 'json') {
            if (!can_access_location($item['location_id'])) {
                return $this->respondError(
                    'Anda tidak memiliki akses ke barang stok tersebut.',
                    403
                );
            }

            return $this->respondAjax($item);
        }

        if (!can_access_location($item['location_id'])) {
            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        $transactions = $this->transactionModel
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
            ->where('inventory_transactions.stock_item_id', $id)
            ->orderBy('inventory_transactions.transaction_date', 'ASC')
            ->orderBy('inventory_transactions.id', 'ASC')
            ->findAll();

        // Hitung saldo berjalan dari histori transaksi
        $balance = 0;
        $history = [];

        foreach ($transactions as $transaction) {
            switch ($transaction['transaction_type']) {
                case 'Masuk':
                case 'Penyesuaian Naik':
                    $balance += (int) $transaction['quantity'];
                    break;

                case 'Keluar':
                case 'Penyesuaian Turun':
                    $balance -= (int) $transaction['quantity'];
                    break;
            }

            $transaction['balance'] = $balance;
            $history[]              = $transaction;
        }

        $locationBuilder = $this->locationModel
            ->where('is_active', 1);

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        $locations = $locationBuilder
            ->orderBy('name', 'ASC')
            ->findAll();

        // Modal pindah lokasi boleh ke semua lokasi aktif (termasuk
        // di luar tanggung jawab user; akan jadi permintaan/pending).
        $allLocations = $this->locationModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $transferLocations = array_values(array_filter(
            $allLocations,
            static fn (array $location): bool => (int) $location['id'] !== (int) $item['location_id']
        ));

        return view('stock_items/show', [
            'title'             => 'Detail Barang Stok',
            'item'              => $item,
            'history'           => $history,
            'images'            => (new ItemImageModel())->forItem(
                ItemImageModel::TYPE_STOCK_ITEM,
                (int) $id
            ),
            'imageConfig'       => new ItemImages(),
            'categories'        => $this->categoryModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'units'             => $this->unitModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'locations'         => $locations,
            'transferLocations' => $transferLocations,
        ]);
    }

    public function stockIn($id)
    {
        $item = $this->itemModel->find($id);

        if (!$item) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        return view('stock_items/stock_in', [
            'title' => 'Stok Masuk',
            'item'  => $item,
        ]);
    }

    public function storeStockIn($id)
    {
        $isAjax = $this->request->isAJAX();

        $item = $this->itemModel->find($id);

        if (!$item) {
            if ($isAjax) {
                return $this->respondError('Barang stok tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke barang stok tersebut.', 403);
            }

            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        if (!$this->validate($this->transactionRules())) {
            if ($isAjax) {
                return $this->respondErrors('Data tidak valid.', $this->validator->getErrors());
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $quantity = (int) $this->request->getPost('quantity');

        $db = db_connect();

        $db->transStart();

        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_type' => 'Masuk',
            'item_type'        => 'Barang Stok',
            'stock_item_id'    => $id,
            'quantity'         => $quantity,
            'to_location_id'   => $item['location_id'],
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => session()->get('user_id'),
        ]);

        $transactionId = $this->transactionModel->getInsertID();

        $newQuantity = $item['quantity'] + $quantity;

        $this->itemModel->update($id, [
            'quantity' => $newQuantity,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Transaksi stok masuk gagal disimpan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Transaksi stok masuk gagal disimpan.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Stok masuk berhasil dicatat.', [
                'quantity'    => $newQuantity,
                'transaction' => $this->transactionRow($transactionId),
            ]);
        }

        return redirect()
            ->to('/stock-items/' . $id)
            ->with('success', 'Stok masuk berhasil dicatat.');
    }

    public function stockOut($id)
    {
        $item = $this->itemModel->find($id);

        if (!$item) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        return view('stock_items/stock_out', [
            'title' => 'Stok Keluar',
            'item'  => $item,
        ]);
    }

    public function storeStockOut($id)
    {
        $isAjax = $this->request->isAJAX();

        $item = $this->itemModel->find($id);

        if (!$item) {
            if ($isAjax) {
                return $this->respondError('Barang stok tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke barang stok tersebut.', 403);
            }

            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        if (!$this->validate($this->transactionRules())) {
            if ($isAjax) {
                return $this->respondErrors('Data tidak valid.', $this->validator->getErrors());
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $quantity = (int) $this->request->getPost('quantity');

        $db = db_connect();

        $db->transStart();

        // Update stok atomik: hanya berhasil jika stok mencukupi
        $db->table('stock_items')
            ->set('quantity', 'quantity - ' . $quantity, false)
            ->where('id', $id)
            ->where('quantity >=', $quantity)
            ->update();

        if ($db->affectedRows() === 0) {
            $db->transRollback();

            $available = $this->itemModel->find($id)['quantity'];

            if ($isAjax) {
                return $this->respondError(
                    'Stok tidak mencukupi. Stok tersedia: '
                    . $available . ' ' . $item['satuan'] . '.',
                    409
                );
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Stok tidak mencukupi. Stok tersedia: '
                    . $available . ' '
                    . $item['satuan'] . '.'
                );
        }

        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_type' => 'Keluar',
            'item_type'        => 'Barang Stok',
            'stock_item_id'    => $id,
            'quantity'         => $quantity,
            'from_location_id' => $item['location_id'],
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => session()->get('user_id'),
        ]);

        $transactionId = $this->transactionModel->getInsertID();

        $newQuantity = $item['quantity'] - $quantity;

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Transaksi stok keluar gagal disimpan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Transaksi stok keluar gagal disimpan.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Stok keluar berhasil dicatat.', [
                'quantity'    => $newQuantity,
                'transaction' => $this->transactionRow($transactionId),
            ]);
        }

        return redirect()
            ->to('/stock-items/' . $id)
            ->with('success', 'Stok keluar berhasil dicatat.');
    }

    public function transfer($id)
    {
        $item = $this->itemModel->find($id);

        if (!$item) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        $locationBuilder = $this->locationModel
            ->where('is_active', 1)
            ->where('id !=', $item['location_id']);

        return view('stock_items/transfer', [
            'title'     => 'Pindah Stok',
            'item'      => $item,
            'locations' => $locationBuilder
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function storeTransfer($id)
    {
        $isAjax = $this->request->isAJAX();

        $item = $this->itemModel->find($id);

        if (!$item) {
            if ($isAjax) {
                return $this->respondError('Barang stok tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke barang stok tersebut.', 403);
            }

            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        $toLocationId = (int) $this->request->getPost('to_location_id');

        if (!$toLocationId) {
            if ($isAjax) {
                return $this->respondErrors('Lokasi tujuan wajib dipilih.', ['to_location_id' => 'Lokasi tujuan wajib dipilih.']);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'errors',
                    ['to_location_id' => 'Lokasi tujuan wajib dipilih.']
                );
        }

        if ($toLocationId === (int) $item['location_id']) {
            if ($isAjax) {
                return $this->respondError('Lokasi tujuan tidak boleh sama dengan lokasi asal.', 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Lokasi tujuan tidak boleh sama dengan lokasi asal.'
                );
        }

        if (!can_access_location($toLocationId)) {
            (new \App\Models\TransferRequestModel())->insert([
                'request_kind'     => 'Penawaran',
                'item_type'        => 'Barang Stok',
                'asset_id'         => null,
                'stock_item_id'    => $id,
                'from_location_id' => $item['location_id'],
                'to_location_id'   => $toLocationId,
                'from_unit_id'     => $item['unit_id'],
                'to_unit_id'       => null,
                'quantity'         => $item['quantity'],
                'reason'           => $this->request->getPost('reason'),
                'notes'            => $this->request->getPost('notes'),
                'status'           => 'Pending',
                'requested_by'     => session()->get('user_id'),
            ]);

            $message = 'Perpindahan ke lokasi di luar tanggung jawab Anda dibuat sebagai permintaan; menunggu konfirmasi pengelola lokasi tujuan.';

            if ($isAjax) {
                return $this->respondSuccess($message, ['pending' => true]);
            }

            return redirect()
                ->to('/stock-items/' . $id)
                ->with('success', $message);
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

        // Pindahkan seluruh stok ke lokasi tujuan
        $this->itemModel->update($id, [
            'location_id' => $toLocationId,
        ]);

        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_type' => 'Pindah',
            'item_type'        => 'Barang Stok',
            'stock_item_id'    => $id,
            'quantity'         => $item['quantity'],
            'from_location_id' => $item['location_id'],
            'to_location_id'   => $toLocationId,
            'reason'           => $this->request->getPost('reason'),
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => session()->get('user_id'),
        ]);

        $transactionId = $this->transactionModel->getInsertID();

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Perpindahan stok gagal disimpan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Perpindahan stok gagal disimpan.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Stok berhasil dipindahkan ke lokasi tujuan.', [
                'location_id' => $toLocationId,
                'transaction' => $this->transactionRow($transactionId),
            ]);
        }

        return redirect()
            ->to('/stock-items/' . $id)
            ->with('success', 'Stok berhasil dipindahkan ke lokasi tujuan.');
    }

    public function adjustment($id)
    {
        $item = $this->itemModel->find($id);

        if (!$item) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        return view('stock_items/adjustment', [
            'title' => 'Penyesuaian Stok',
            'item'  => $item,
        ]);
    }

    public function storeAdjustment($id)
    {
        $isAjax = $this->request->isAJAX();

        $item = $this->itemModel->find($id);

        if (!$item) {
            if ($isAjax) {
                return $this->respondError('Barang stok tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke barang stok tersebut.', 403);
            }

            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        if (!$this->validate([
            'adjustment_type'  => 'required|in_list[Naik,Turun]',
            'quantity'         => 'required|is_natural_no_zero',
            'transaction_date' => 'required|valid_date',
            'reason'           => 'required|max_length[255]',
        ])) {
            if ($isAjax) {
                return $this->respondErrors('Data tidak valid.', $this->validator->getErrors());
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $quantity = (int) $this->request->getPost('quantity');
        $type     = $this->request->getPost('adjustment_type') === 'Naik'
            ? 'Penyesuaian Naik'
            : 'Penyesuaian Turun';

        $db = db_connect();

        $db->transStart();

        if ($type === 'Penyesuaian Turun') {
            // Update stok atomik: hanya berhasil jika stok mencukupi
            $db->table('stock_items')
                ->set('quantity', 'quantity - ' . $quantity, false)
                ->where('id', $id)
                ->where('quantity >=', $quantity)
                ->update();

            if ($db->affectedRows() === 0) {
                $db->transRollback();

                $available = $this->itemModel->find($id)['quantity'];

                if ($isAjax) {
                    return $this->respondError(
                        'Stok tidak mencukupi. Stok tersedia: '
                        . $available . ' ' . $item['satuan'] . '.',
                        409
                    );
                }

                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Stok tidak mencukupi. Stok tersedia: '
                        . $available . ' '
                        . $item['satuan'] . '.'
                    );
            }
        } else {
            $this->itemModel->update($id, [
                'quantity' => $item['quantity'] + $quantity,
            ]);
        }

        $newQuantity = $type === 'Penyesuaian Turun'
            ? $item['quantity'] - $quantity
            : $item['quantity'] + $quantity;

        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_type' => $type,
            'item_type'        => 'Barang Stok',
            'stock_item_id'    => $id,
            'quantity'         => $quantity,
            'from_location_id' => $type === 'Penyesuaian Turun' ? $item['location_id'] : null,
            'to_location_id'   => $type === 'Penyesuaian Naik' ? $item['location_id'] : null,
            'reason'           => $this->request->getPost('reason'),
            'created_by'       => session()->get('user_id'),
        ]);

        $transactionId = $this->transactionModel->getInsertID();

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Penyesuaian stok gagal disimpan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Penyesuaian stok gagal disimpan.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Penyesuaian stok berhasil dicatat.', [
                'quantity'    => $newQuantity,
                'transaction' => $this->transactionRow($transactionId),
            ]);
        }

        return redirect()
            ->to('/stock-items/' . $id)
            ->with('success', 'Penyesuaian stok berhasil dicatat.');
    }

    public function toAsset($id)
    {
        $item = $this->itemModel->find($id);

        if (!$item) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        $locationBuilder = $this->locationModel
            ->where('is_active', 1);

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        return view('stock_items/to_asset', [
            'title'      => 'Konversi ke Aset',
            'item'       => $item,
            'categories' => $this->categoryModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'units'      => $this->unitModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'locations'  => $locationBuilder
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function storeToAsset($id)
    {
        $isAjax = $this->request->isAJAX();

        $item = $this->itemModel->find($id);

        if (!$item) {
            if ($isAjax) {
                return $this->respondError('Barang stok tidak ditemukan.', 404);
            }

            throw PageNotFoundException::forPageNotFound();
        }

        if (!can_access_location($item['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke barang stok tersebut.', 403);
            }

            return redirect()
                ->to('/stock-items')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        $rules = [
            'quantity'         => 'required|is_natural_no_zero',
            'asset_code'       => 'required|min_length[2]|max_length[50]',
            'name'             => 'required|min_length[2]|max_length[150]',
            'category_id'      => 'required|is_natural_no_zero',
            'unit_id'          => 'required|is_natural_no_zero',
            'location_id'      => 'required|is_natural_no_zero',
            'condition_status' => 'required|in_list[Baik,Rusak Ringan,Rusak Berat]',
            'asset_status'     => 'required|in_list[Aktif,Dipinjam,Tidak Digunakan,Keluar Perusahaan]',
            'acquisition_year' => 'permit_empty|numeric|greater_than_equal_to[1900]|less_than_equal_to[' . date('Y') . ']',
            'acquisition_price' => 'permit_empty|decimal',
            'transaction_date' => 'required|valid_date',
            'notes'            => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            if ($isAjax) {
                return $this->respondErrors('Data tidak valid.', $this->validator->getErrors());
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $quantity = (int) $this->request->getPost('quantity');
        $locationId = (int) $this->request->getPost('location_id');

        if ($quantity < 1 || $quantity > (int) $item['quantity']) {
            $message = 'Jumlah konversi tidak valid. Stok tersedia: '
                . $item['quantity'] . ' ' . $item['satuan'] . '.';

            if ($isAjax) {
                return $this->respondError($message, 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $message);
        }

        if (!can_access_location($locationId)) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke lokasi tujuan.', 403);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Anda tidak memiliki akses ke lokasi tujuan.');
        }

        $baseCode = trim((string) $this->request->getPost('asset_code'));
        $transactionDate = (string) $this->request->getPost('transaction_date');
        $notes = trim((string) ($this->request->getPost('notes') ?? ''));

        $db = db_connect();
        $db->transStart();

        // Kurangi stok secara atomik.
        $db->table('stock_items')
            ->set('quantity', 'quantity - ' . $quantity, false)
            ->where('id', $id)
            ->where('quantity >=', $quantity)
            ->update();

        if ($db->affectedRows() === 0) {
            $db->transRollback();

            $available = (int) $this->itemModel->find($id)['quantity'];
            $message = 'Stok tidak mencukupi. Stok tersedia: '
                . $available . ' ' . $item['satuan'] . '.';

            if ($isAjax) {
                return $this->respondError($message, 409);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $message);
        }

        // Catat histori keluar barang stok.
        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => $transactionDate,
            'transaction_type' => 'Keluar',
            'item_type'        => 'Barang Stok',
            'stock_item_id'    => $id,
            'quantity'         => $quantity,
            'from_location_id' => $item['location_id'],
            'reason'           => 'Konversi ke aset',
            'notes'            => $notes ?: null,
            'created_by'       => session()->get('user_id'),
        ]);

        $stockTransactionId = $this->transactionModel->getInsertID();

        $assetIds = [];
        $newQuantity = (int) $item['quantity'] - $quantity;

        for ($i = 1; $i <= $quantity; $i++) {
            $assetCode = $quantity === 1
                ? $baseCode
                : $baseCode . '-' . $i;

            $assetId = $this->assetModel->insert([
                'asset_code'        => $assetCode,
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

            if (!$assetId) {
                $db->transRollback();

                $message = 'Gagal membuat aset. Kode barang mungkin sudah digunakan.';

                if ($isAjax) {
                    return $this->respondError($message, 409);
                }

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', $message);
            }

            $assetIds[] = (int) $assetId;

            $this->transactionModel->insert([
                'transaction_code' => $this->transactionModel->generateCode(),
                'transaction_date' => $transactionDate,
                'transaction_type' => 'Perolehan',
                'item_type'        => 'Aset',
                'asset_id'         => $assetId,
                'quantity'         => 1,
                'to_location_id'   => $locationId,
                'reference_type'   => 'stock_transaction',
                'reference_id'     => $stockTransactionId,
                'reason'           => 'Konversi dari barang stok',
                'notes'            => $notes ?: null,
                'created_by'       => session()->get('user_id'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Konversi stok ke aset gagal disimpan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Konversi stok ke aset gagal disimpan.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Konversi stok ke aset berhasil dicatat.', [
                'quantity'       => $quantity,
                'asset_ids'      => $assetIds,
                'asset_id'       => $assetIds[0] ?? null,
                'stock_quantity' => $newQuantity,
                'transaction'    => $this->transactionRow($stockTransactionId),
            ]);
        }

        return redirect()
            ->to('/assets')
            ->with('success', 'Konversi ' . $quantity . ' unit stok ke aset berhasil dicatat.');
    }

    /**
     * Baris transaksi lengkap untuk refresh riwayat via AJAX.
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
        $itemCodeRule = 'required|min_length[2]|max_length[50]';

        return [
            'item_code'   => $itemCodeRule,
            'name'        => 'required|min_length[2]|max_length[150]',
            'category_id' => 'required|is_natural_no_zero',
            'unit_id'     => 'required|is_natural_no_zero',
            'location_id' => 'required|is_natural_no_zero',
            'satuan'      => 'required|max_length[50]',
            'description' => 'permit_empty',
        ];
    }

    private function transactionRules(): array
    {
        return [
            'quantity'         => 'required|is_natural_no_zero',
            'transaction_date' => 'required|valid_date',
            'notes'            => 'permit_empty',
        ];
    }
}
