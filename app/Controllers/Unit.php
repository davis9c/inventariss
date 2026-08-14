<?php

namespace App\Controllers;

use App\Models\UnitModel;
use App\Models\AssetModel;
use App\Models\UnitLocationModel;
use App\Models\LocationModel;

class Unit extends BaseController
{
    protected UnitModel $unitModel;
    protected AssetModel $assetModel;
    protected UnitLocationModel $unitLocationModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->unitModel = new UnitModel();
        $this->assetModel = new AssetModel();
        $this->unitLocationModel = new UnitLocationModel();
        $this->locationModel = new LocationModel();
    }

    public function index()
    {
        return view('units/index', [
            'title' => 'Unit / Departemen',
            'units' => $this->unitModel
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function create()
    {
        return view('units/create', [
            'title' => 'Tambah Unit',
            'locations' => $this->locationModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function store()
    {
        $db = db_connect();

        $db->transStart();

        $this->unitModel->insert([
            'name'        => $this->request->getPost('name'),
            'code'        => $this->request->getPost('code'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        $unitId = $this->unitModel->getInsertID();

        $locations = $this->request->getPost('location_ids');

        if (!empty($locations) && is_array($locations)) {
            foreach ($locations as $locationId) {
                $this->unitLocationModel->insert([
                    'unit_id'     => $unitId,
                    'location_id' => $locationId,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Unit gagal ditambahkan.');
        }

        return redirect()
            ->to('/units')
            ->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $unitLocations = $this->unitLocationModel
            ->where('unit_id', $id)
            ->findAll();

        $locationIds = array_column(
            $unitLocations,
            'location_id'
        );

        return view('units/edit', [
            'title' => 'Edit Unit',
            'unit' => $unit,

            'locations' => $this->locationModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),

            'locationIds' => $locationIds,
        ]);
    }

    public function update($id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();

        $db->transStart();

        $this->unitModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'code'        => $this->request->getPost('code'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        // Hapus relasi lokasi lama
        $this->unitLocationModel
            ->where('unit_id', $id)
            ->delete();

        // Simpan relasi lokasi baru
        $locations = $this->request->getPost('location_ids');

        if (!empty($locations) && is_array($locations)) {
            foreach ($locations as $locationId) {
                $this->unitLocationModel->insert([
                    'unit_id'     => $id,
                    'location_id' => $locationId,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Unit gagal diperbarui.');
        }

        return redirect()
            ->to('/units')
            ->with('success', 'Unit berhasil diperbarui.');
    }

    public function delete($id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Jangan hapus unit jika masih digunakan aset
        $assetCount = $this->assetModel
            ->where('unit_id', $id)
            ->countAllResults();

        if ($assetCount > 0) {
            return redirect()
                ->to('/units')
                ->with(
                    'error',
                    'Unit tidak dapat dihapus karena masih digunakan oleh aset.'
                );
        }

        // Hapus relasi lokasi
        $this->unitLocationModel
            ->where('unit_id', $id)
            ->delete();

        // Hapus unit
        $this->unitModel->delete($id);

        return redirect()
            ->to('/units')
            ->with('success', 'Unit berhasil dihapus.');
    }

    public function show($id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Lokasi yang terkait dengan unit
        $locations = $this->unitLocationModel
            ->select('locations.*')
            ->join(
                'locations',
                'locations.id = unit_locations.location_id'
            )
            ->where('unit_locations.unit_id', $id)
            ->orderBy('locations.name', 'ASC')
            ->findAll();

        // Aset yang berada pada unit
        $assets = $this->assetModel
            ->select('
                assets.*,
                locations.name as location_name
            ')
            ->join(
                'locations',
                'locations.id = assets.location_id',
                'left'
            )
            ->where('assets.unit_id', $id)
            ->orderBy('assets.name', 'ASC')
            ->findAll();

        return view('units/show', [
            'title'     => 'Detail Unit',
            'unit'      => $unit,
            'locations' => $locations,
            'assets'    => $assets,
        ]);
    }
}
