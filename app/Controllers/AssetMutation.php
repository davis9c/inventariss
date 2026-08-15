<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\InventoryTransactionModel;
use App\Models\UnitModel;
use App\Models\LocationModel;

class AssetMutation extends BaseController
{
    protected InventoryTransactionModel $transactionModel;
    protected AssetModel $assetModel;
    protected UnitModel $unitModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->transactionModel = new InventoryTransactionModel();
        $this->assetModel       = new AssetModel();
        $this->unitModel        = new UnitModel();
        $this->locationModel    = new LocationModel();
    }

    public function index()
    {
        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'inventory_transactions',
                function ($b) {
                    $b->select('
                        inventory_transactions.*,
                        assets.asset_code,
                        assets.name as asset_name,
                        fl.name as from_location_name,
                        tl.name as to_location_name,
                        users.name as created_by_name
                    ')
                        ->join(
                            'assets',
                            'assets.id = inventory_transactions.asset_id'
                        )
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
                        ->where('inventory_transactions.item_type', 'Aset')
                        ->where('inventory_transactions.transaction_type', 'Mutasi');

                    if (has_location_restriction()) {
                        $locationIds = user_location_ids();

                        $b->groupStart()
                            ->whereIn(
                                'inventory_transactions.from_location_id',
                                $locationIds
                            )
                            ->orWhereIn(
                                'inventory_transactions.to_location_id',
                                $locationIds
                            )
                            ->groupEnd();
                    }
                },
                [
                    'inventory_transactions.transaction_code',
                    'assets.asset_code',
                    'assets.name',
                    'fl.name',
                    'tl.name',
                    'inventory_transactions.reason',
                    'inventory_transactions.notes',
                    'users.name',
                ],
                [
                    0 => 'inventory_transactions.transaction_date',
                    1 => 'assets.name',
                    2 => 'fl.name',
                    3 => 'tl.name',
                ],
                'inventory_transactions.transaction_date',
                'DESC'
            ));
        }

        $assetBuilder = $this->assetModel
            ->select('
            assets.*,
            units.name as unit_name,
            locations.name as location_name
        ')
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
            ->orderBy('assets.name', 'ASC');

        if (has_location_restriction()) {
            $assetBuilder->whereIn(
                'assets.location_id',
                user_location_ids()
            );
        }

        $locationBuilder = $this->locationModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC');

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        return view('asset_mutations/index', [
            'title'     => 'Mutasi Aset',
            'assets'    => $assetBuilder->findAll(),
            'units'     => $this->unitModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'locations' => $locationBuilder->findAll(),
        ]);
    }

    public function create()
    {
        $assetBuilder = $this->assetModel
            ->select('
            assets.*,
            units.name as unit_name,
            locations.name as location_name
        ')
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
            ->orderBy('assets.name', 'ASC');

        if (has_location_restriction()) {
            $assetBuilder->whereIn(
                'assets.location_id',
                user_location_ids()
            );
        }

        $assets = $assetBuilder->findAll();


        $locationBuilder = $this->locationModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC');

        if (has_location_restriction()) {
            $locationBuilder->whereIn(
                'id',
                user_location_ids()
            );
        }

        $locations = $locationBuilder->findAll();


        return view('asset_mutations/create', [
            'title'     => 'Mutasi Aset',
            'assets'    => $assets,
            'units'     => $this->unitModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'locations' => $locations,
        ]);
    }

    public function store()
    {
        $isAjax = $this->request->isAJAX();

        $assetId = $this->request->getPost('asset_id');

        $asset = $this->assetModel->find($assetId);

        if (!$asset) {
            if ($isAjax) {
                return $this->respondError('Barang tidak ditemukan.', 404);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Barang tidak ditemukan.');
        }

        /*
    |--------------------------------------------------------------------------
    | Cek akses lokasi asal
    |--------------------------------------------------------------------------
    */

        if (! can_access_location($asset['location_id'])) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke lokasi barang tersebut.', 403);
            }

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke lokasi barang tersebut.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | Cek akses lokasi tujuan
    |--------------------------------------------------------------------------
    */

        $toLocationId = $this->request->getPost('to_location_id');

        if (! can_access_location($toLocationId)) {
            if ($isAjax) {
                return $this->respondError('Anda tidak memiliki akses ke lokasi tujuan.', 403);
            }

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke lokasi tujuan.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | Transaksi database
    |--------------------------------------------------------------------------
    */

        $db = db_connect();

        $db->transStart();

        // Simpan histori pergerakan (satu sumber: inventory_transactions)
        $this->transactionModel->insert([
            'transaction_code' => $this->transactionModel->generateCode(),
            'transaction_date' => $this->request->getPost('mutation_date'),
            'transaction_type' => 'Mutasi',
            'item_type'        => 'Aset',
            'asset_id'         => $assetId,
            'quantity'         => 1,
            'from_location_id' => $asset['location_id'],
            'to_location_id'   => $toLocationId,
            'reason'           => $this->request->getPost('reason'),
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => session()->get('user_id'),
        ]);

        $transactionId = $this->transactionModel->getInsertID();

        // Update posisi barang
        $this->assetModel->update($assetId, [
            'unit_id'     => $this->request->getPost('to_unit_id'),
            'location_id' => $toLocationId,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Mutasi gagal disimpan.', 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Mutasi gagal disimpan.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Mutasi aset berhasil disimpan.', [
                'unit_id'     => $this->request->getPost('to_unit_id'),
                'location_id' => $toLocationId,
                'transaction' => $this->transactionRow($transactionId),
            ]);
        }

        return redirect()
            ->to('/asset-mutations')
            ->with('success', 'Mutasi aset berhasil disimpan.');
    }

    /**
     * Baris transaksi lengkap untuk refresh riwayat via AJAX.
     */
    private function transactionRow(int $transactionId): ?array
    {
        return $this->transactionModel
            ->select('
                inventory_transactions.*,
                assets.asset_code,
                assets.name as asset_name,
                fl.name as from_location_name,
                tl.name as to_location_name,
                users.name as created_by_name
            ')
            ->join(
                'assets',
                'assets.id = inventory_transactions.asset_id'
            )
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

    public function unitsByLocation($locationId)
    {
        $db = db_connect();

        $units = $db->table('unit_locations ul')
            ->select('units.id, units.name, units.code')
            ->join(
                'units',
                'units.id = ul.unit_id'
            )
            ->where(
                'ul.location_id',
                $locationId
            )
            ->where(
                'units.is_active',
                1
            )
            ->orderBy(
                'units.name',
                'ASC'
            )
            ->get()
            ->getResultArray();

        return $this->response
            ->setContentType('application/json')
            ->setJSON($units);
    }
}
