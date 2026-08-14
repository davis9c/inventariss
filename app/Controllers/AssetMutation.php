<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\AssetMutationModel;
use App\Models\UnitModel;
use App\Models\LocationModel;
use App\Models\UnitLocationModel;

class AssetMutation extends BaseController
{
    protected AssetMutationModel $mutationModel;
    protected AssetModel $assetModel;
    protected UnitModel $unitModel;
    protected LocationModel $locationModel;
    protected UnitLocationModel $unitLocationModel;

    public function __construct()
    {
        $this->mutationModel = new AssetMutationModel();
        $this->assetModel    = new AssetModel();
        $this->unitModel     = new UnitModel();
        $this->locationModel = new LocationModel();
    }

    public function index()
    {
        $builder = $this->mutationModel
            ->select('
            asset_mutations.*,
            assets.asset_code,
            assets.name as asset_name,
            fu.name as from_unit_name,
            tu.name as to_unit_name,
            fl.name as from_location_name,
            tl.name as to_location_name
        ')
            ->join(
                'assets',
                'assets.id = asset_mutations.asset_id'
            )
            ->join(
                'units fu',
                'fu.id = asset_mutations.from_unit_id',
                'left'
            )
            ->join(
                'units tu',
                'tu.id = asset_mutations.to_unit_id',
                'left'
            )
            ->join(
                'locations fl',
                'fl.id = asset_mutations.from_location_id',
                'left'
            )
            ->join(
                'locations tl',
                'tl.id = asset_mutations.to_location_id',
                'left'
            );

        /*
    |--------------------------------------------------------------------------
    | Filter berdasarkan lokasi user
    |--------------------------------------------------------------------------
    */

        if (has_location_restriction()) {

            $locationIds = user_location_ids();

            $builder->groupStart()
                ->whereIn(
                    'asset_mutations.from_location_id',
                    $locationIds
                )
                ->orWhereIn(
                    'asset_mutations.to_location_id',
                    $locationIds
                )
                ->groupEnd();
        }

        /*
    |--------------------------------------------------------------------------
    | Ambil data
    |--------------------------------------------------------------------------
    */

        $mutations = $builder
            ->orderBy(
                'asset_mutations.mutation_date',
                'DESC'
            )
            ->findAll();

        return view('asset_mutations/index', [
            'title'     => 'Mutasi Aset',
            'mutations' => $mutations,
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
        $assetId = $this->request->getPost('asset_id');

        $asset = $this->assetModel->find($assetId);

        if (!$asset) {
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

        // Simpan histori mutasi
        $this->mutationModel->insert([
            'asset_id'         => $assetId,
            'from_unit_id'     => $asset['unit_id'],
            'to_unit_id'       => $this->request->getPost('to_unit_id'),
            'from_location_id' => $asset['location_id'],
            'to_location_id'   => $toLocationId,
            'mutation_date'    => $this->request->getPost('mutation_date'),
            'reason'           => $this->request->getPost('reason'),
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => session()->get('user_id'),
        ]);

        // Update posisi barang
        $this->assetModel->update($assetId, [
            'unit_id'     => $this->request->getPost('to_unit_id'),
            'location_id' => $toLocationId,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Mutasi gagal disimpan.');
        }

        return redirect()
            ->to('/asset-mutations')
            ->with('success', 'Mutasi aset berhasil disimpan.');
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
