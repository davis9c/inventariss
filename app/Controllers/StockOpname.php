<?php

namespace App\Controllers;

use App\Models\StockOpnameModel;
use App\Models\StockOpnameDetailModel;
use App\Models\StockOpnameStockDetailModel;
use App\Models\StockItemModel;
use App\Models\AssetModel;
use App\Models\LocationModel;

class StockOpname extends BaseController
{
    protected StockOpnameModel $opnameModel;
    protected StockOpnameDetailModel $detailModel;
    protected StockOpnameStockDetailModel $stockDetailModel;
    protected StockItemModel $stockItemModel;
    protected AssetModel $assetModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->opnameModel      = new StockOpnameModel();
        $this->detailModel      = new StockOpnameDetailModel();
        $this->stockDetailModel = new StockOpnameStockDetailModel();
        $this->stockItemModel   = new StockItemModel();
        $this->assetModel       = new AssetModel();
        $this->locationModel    = new LocationModel();
    }

    public function index()
    {
        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'stock_opnames',
                function ($b) {
                    $b->select('
                        stock_opnames.*,
                        locations.name as location_name,
                        locations.building,
                        locations.room
                    ')
                        ->join(
                            'locations',
                            'locations.id = stock_opnames.location_id',
                            'left'
                        );
                },
                [
                    'stock_opnames.opname_code',
                    'locations.name',
                    'stock_opnames.status',
                    'stock_opnames.notes',
                ],
                [
                    0 => 'stock_opnames.opname_code',
                    1 => 'stock_opnames.opname_date',
                    2 => 'locations.name',
                    3 => 'stock_opnames.status',
                ],
                'stock_opnames.opname_date',
                'DESC'
            ));
        }

        $opnames = $this->opnameModel
            ->select('
                stock_opnames.*,
                locations.name as location_name,
                locations.building,
                locations.room
            ')
            ->join(
                'locations',
                'locations.id = stock_opnames.location_id',
                'left'
            )
            ->orderBy('stock_opnames.opname_date', 'DESC')
            ->findAll();

        return view('stock_opnames/index', [
            'title'   => 'Stock Opname',
            'opnames' => $opnames,
        ]);
    }

    public function create()
    {
        $locationBuilder = $this->locationModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC');

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        return view('stock_opnames/create', [
            'title' => 'Buat Stock Opname',

            'locations' => $locationBuilder
                ->findAll(),
        ]);
    }

    public function store()
    {
        $locationId = $this->request->getPost('location_id');

        // Jika memilih lokasi tertentu, cek akses user
        if ($locationId && !can_access_location($locationId)) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke lokasi tersebut.'
                );
        }

        $code = 'SO-' . date('YmdHis');

        $this->opnameModel->insert([
            'opname_code' => $code,
            'opname_date' => $this->request->getPost('opname_date'),
            'location_id' => $locationId ?: null,
            'status'      => 'Draft',
            'notes'       => $this->request->getPost('notes'),
            'created_by'  => session()->get('user_id'),
        ]);

        $opnameId = $this->opnameModel->getInsertID();

        /*
     * Ambil aset berdasarkan lokasi.
     */
        $builder = $this->assetModel
            ->where('asset_status !=', 'Tidak Digunakan');

        if ($locationId) {
            $builder->where('location_id', $locationId);
        } elseif (has_location_restriction()) {
            // User dengan pembatasan lokasi hanya boleh opname
            // terhadap lokasi yang menjadi hak aksesnya.
            $builder->whereIn(
                'location_id',
                user_location_ids()
            );
        }

        $assets = $builder->findAll();

        foreach ($assets as $asset) {
            $this->detailModel->insert([
                'stock_opname_id'  => $opnameId,
                'asset_id'         => $asset['id'],
                'is_found'         => true,
                'condition_status' => $asset['condition_status'],
            ]);
        }

        /*
         * Barang stok (non-identitas):
         * catat stok sistem saat opname dibuat untuk perhitungan fisik.
         */
        $stockBuilder = $this->stockItemModel
            ->where('is_active', 1);

        if ($locationId) {
            $stockBuilder->where('location_id', $locationId);
        } elseif (has_location_restriction()) {
            $stockBuilder->whereIn(
                'location_id',
                user_location_ids()
            );
        }

        $stockItems = $stockBuilder->findAll();

        foreach ($stockItems as $stockItem) {
            $this->stockDetailModel->insert([
                'stock_opname_id' => $opnameId,
                'stock_item_id'   => $stockItem['id'],
                'system_qty'      => $stockItem['quantity'],
                'physical_qty'    => null,
            ]);
        }

        return redirect()
            ->to('/stock-opnames/' . $opnameId)
            ->with('success', 'Stock opname berhasil dibuat.');
    }

    public function show($id)
    {
        $opname = $this->opnameModel
            ->select('
            stock_opnames.*,
            locations.name as location_name,
            locations.building,
            locations.room
        ')
            ->join(
                'locations',
                'locations.id = stock_opnames.location_id',
                'left'
            )
            ->find($id);

        if (!$opname) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Cek akses lokasi
        if (
            $opname['location_id']
            && !can_access_location($opname['location_id'])
        ) {
            return redirect()
                ->to('/stock-opnames')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke stock opname tersebut.'
                );
        }

        /*
     * Jika opname mencakup semua lokasi,
     * user dengan pembatasan lokasi tetap hanya boleh
     * melihat detail aset dari lokasi yang menjadi haknya.
     */
        $builder = $this->detailModel
            ->select('
            stock_opname_details.*,
            assets.asset_code,
            assets.name as asset_name,
            assets.serial_number
        ')
            ->join(
                'assets',
                'assets.id = stock_opname_details.asset_id'
            )
            ->where(
                'stock_opname_id',
                $id
            );

        if (
            !$opname['location_id']
            && has_location_restriction()
        ) {
            $builder->whereIn(
                'assets.location_id',
                user_location_ids()
            );
        }

        $details = $builder
            ->orderBy('assets.name', 'ASC')
            ->findAll();

        /*
         * Detail barang stok (non-identitas)
         */
        $stockBuilder = $this->stockDetailModel
            ->select('
                stock_opname_stock_details.*,
                stock_items.item_code,
                stock_items.name as item_name,
                stock_items.satuan,
                stock_items.location_id as stock_item_location_id
            ')
            ->join(
                'stock_items',
                'stock_items.id = stock_opname_stock_details.stock_item_id'
            )
            ->where(
                'stock_opname_stock_details.stock_opname_id',
                $id
            );

        if (
            !$opname['location_id']
            && has_location_restriction()
        ) {
            $stockBuilder->whereIn(
                'stock_items.location_id',
                user_location_ids()
            );
        }

        $stockDetails = $stockBuilder
            ->orderBy('stock_items.name', 'ASC')
            ->findAll();

        return view('stock_opnames/show', [
            'title'        => 'Detail Stock Opname',
            'opname'       => $opname,
            'details'      => $details,
            'stockDetails' => $stockDetails,
        ]);
    }

    public function updateDetail($detailId)
    {
        $isAjax = $this->request->isAJAX();

        $detail = $this->detailModel->find($detailId);

        if (!$detail) {
            if ($isAjax) {
                return $this->respondError('Detail tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $isFound = $this->request->getPost('is_found') ? true : false;

        $this->detailModel->update($detailId, [
            'is_found'         => $isFound,
            'condition_status' => $this->request->getPost('condition_status'),
            'notes'            => $this->request->getPost('notes'),
            'checked_at'       => date('Y-m-d H:i:s'),
        ]);

        if ($isAjax) {
            return $this->respondSuccess('Hasil pemeriksaan berhasil disimpan.', [
                'is_found'         => $isFound,
                'condition_status' => $this->request->getPost('condition_status'),
                'notes'            => $this->request->getPost('notes'),
                'checked_at'       => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Hasil pemeriksaan berhasil disimpan.');
    }

    public function updateStockDetail($detailId)
    {
        $isAjax = $this->request->isAJAX();

        $detail = $this->stockDetailModel
            ->select('
                stock_opname_stock_details.*,
                stock_items.location_id as stock_item_location_id
            ')
            ->join(
                'stock_items',
                'stock_items.id = stock_opname_stock_details.stock_item_id'
            )
            ->where('stock_opname_stock_details.id', $detailId)
            ->first();

        if (!$detail) {
            if ($isAjax) {
                return $this->respondError('Detail tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Cek akses lokasi barang stok
        if (!can_access_location($detail['stock_item_location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke barang stok tersebut.', 403);
            }

            return redirect()->back()
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke barang stok tersebut.'
                );
        }

        // Opname yang sudah selesai tidak boleh diubah
        $opname = $this->opnameModel->find($detail['stock_opname_id']);

        if ($opname['status'] === 'Selesai') {
            if ($isAjax) {
                return $this->respondError('Stock opname sudah diselesaikan.', 409);
            }

            return redirect()->back()
                ->with(
                    'error',
                    'Stock opname sudah diselesaikan.'
                );
        }

        if (!$this->validate([
            'physical_qty' => 'required|is_natural',
            'notes'        => 'permit_empty',
        ])) {
            if ($isAjax) {
                return $this->respondErrors('Data tidak valid.', $this->validator->getErrors());
            }

            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $physicalQty = (int) $this->request->getPost('physical_qty');

        $this->stockDetailModel->update($detailId, [
            'physical_qty' => $physicalQty,
            'notes'        => $this->request->getPost('notes'),
            'checked_at'   => date('Y-m-d H:i:s'),
        ]);

        if ($isAjax) {
            return $this->respondSuccess('Hasil hitung fisik berhasil disimpan.', [
                'physical_qty' => $physicalQty,
                'system_qty'   => (int) $detail['system_qty'],
                'diff'         => $physicalQty - (int) $detail['system_qty'],
                'notes'        => $this->request->getPost('notes'),
                'checked_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Hasil hitung fisik berhasil disimpan.');
    }

    public function finish($id)
    {
        $isAjax = $this->request->isAJAX();

        $opname = $this->opnameModel->find($id);

        if (!$opname) {
            if ($isAjax) {
                return $this->respondError('Stock opname tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->opnameModel->update($id, [
            'status' => 'Selesai',
        ]);

        if ($isAjax) {
            return $this->respondSuccess('Stock opname telah diselesaikan.', [
                'status' => 'Selesai',
            ]);
        }

        return redirect()->to('/stock-opnames/' . $id)
            ->with('success', 'Stock opname telah diselesaikan.');
    }
}
