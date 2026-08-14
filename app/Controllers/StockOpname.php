<?php

namespace App\Controllers;

use App\Models\StockOpnameModel;
use App\Models\StockOpnameDetailModel;
use App\Models\AssetModel;
use App\Models\LocationModel;

class StockOpname extends BaseController
{
    protected StockOpnameModel $opnameModel;
    protected StockOpnameDetailModel $detailModel;
    protected AssetModel $assetModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->opnameModel   = new StockOpnameModel();
        $this->detailModel   = new StockOpnameDetailModel();
        $this->assetModel    = new AssetModel();
        $this->locationModel = new LocationModel();
    }

    public function index()
    {
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

        return view('stock_opnames/show', [
            'title'   => 'Detail Stock Opname',
            'opname'  => $opname,
            'details' => $details,
        ]);
    }

    public function updateDetail($detailId)
    {
        $detail = $this->detailModel->find($detailId);

        if (!$detail) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->detailModel->update($detailId, [
            'is_found'         => $this->request->getPost('is_found') ? true : false,
            'condition_status' => $this->request->getPost('condition_status'),
            'notes'            => $this->request->getPost('notes'),
            'checked_at'       => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()
            ->with('success', 'Hasil pemeriksaan berhasil disimpan.');
    }

    public function finish($id)
    {
        $opname = $this->opnameModel->find($id);

        if (!$opname) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->opnameModel->update($id, [
            'status' => 'Selesai',
        ]);

        return redirect()->to('/stock-opnames/' . $id)
            ->with('success', 'Stock opname telah diselesaikan.');
    }
}
