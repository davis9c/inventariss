<?php

namespace App\Controllers;

use App\Models\LocationModel;
use App\Models\AssetModel;
use App\Models\UnitModel;
use App\Models\UnitLocationModel;

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
        // DataTables server-side.
        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'locations',
                static fn ($b) => $b->select('locations.*'),
                ['name', 'building', 'description'],
                [0 => 'name', 1 => 'building', 2 => 'description', 3 => 'is_active'],
                'name'
            ));
        }

        return view('locations/index', [
            'title' => 'Lokasi',
            // Dipakai oleh checkbox "unit" di modal create/edit.
            'units' => $this->unitModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    /**
     * Satu lokasi sebagai JSON, untuk mengisi modal edit DAN modal detail
     * dari halaman index.
     *
     * Bentuknya mengikuti Location::show() supaya modal detail
     * menampilkan isi yang identik dengan halaman detail.
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

        $assets = db_connect()
            ->table('assets')
            ->select('assets.id, assets.asset_code, assets.name, assets.asset_status, units.name as unit_name')
            ->join('units', 'units.id = assets.unit_id', 'left')
            ->where('assets.location_id', $id)
            ->orderBy('assets.name', 'ASC')
            ->get()
            ->getResultArray();

        $location['units']  = $units;
        $location['assets'] = $assets;

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

        $this->locationModel->delete($id);

        if ($isAjax) {
            return $this->respondSuccess('Lokasi berhasil dihapus.', [
                'id' => (int) $id,
            ]);
        }

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
        ]);
    }
}
