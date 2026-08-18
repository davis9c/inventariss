<?php

namespace App\Controllers;

use App\Models\LocationModel;
use App\Models\AssetModel;
use App\Models\UnitModel;
use App\Models\UnitLocationModel;
use App\Models\InventoryPhotoModel;

class Location extends BaseController
{
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
        return view('locations/index', [
            'title'     => 'Lokasi',
            'locations' => $this->locationModel
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function create()
    {
        return view('locations/create', [
            'title' => 'Tambah Lokasi',
        ]);
    }

    public function store()
    {
        $this->locationModel->insert([
            'name'        => $this->request->getPost('name'),
            'building'    => $this->request->getPost('building'),
            'floor'       => $this->request->getPost('floor'),
            'room'        => $this->request->getPost('room'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? true : false,
        ]);

        return redirect()->to('/locations')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $location = $this->locationModel->find($id);

        if (!$location) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$this->mayEdit($location)) {
            return $this->editLockedResponse(false);
        }

        $units = $this->unitModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $selectedUnits = $this->unitLocationModel
            ->where('location_id', $id)
            ->findAll();

        $selectedUnitIds = array_column($selectedUnits, 'unit_id');

        return view('locations/edit', [
            'title'          => 'Edit Lokasi',
            'location'       => $location,
            'units'          => $units,
            'selectedUnitIds' => $selectedUnitIds,
        ]);
    }

    public function update($id)
    {
        $location = $this->locationModel->find($id);

        if (!$location) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $unitIds = $this->request->getPost('unit_ids') ?? [];

        $db = db_connect();

        $db->transStart();

        // Update lokasi
        $before = $location;
        $this->locationModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'building'    => $this->request->getPost('building'),
            'floor'       => $this->request->getPost('floor'),
            'room'        => $this->request->getPost('room'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);
        $this->audit('UPDATE', 'location', (int) $id, $before, $this->locationModel->find($id));

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

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Lokasi gagal diperbarui.'
                );
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
        $location = $this->locationModel->find($id);
        if (!$location) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if (!$this->mayEdit($location)) return $this->editLockedResponse(false);
        $this->locationModel->delete($id);
        $this->audit('SOFT_DELETE', 'location', (int) $id, $location);

        return redirect()->to('/locations')
            ->with('success', 'Lokasi berhasil dihapus.');
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
            'photos'   => (new InventoryPhotoModel())->where(['owner_type' => 'location', 'owner_id' => $id])->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }
}
