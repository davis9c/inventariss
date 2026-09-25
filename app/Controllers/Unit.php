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
        // DataTables server-side.
        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'units',
                static fn ($b) => $b->select('units.*'),
                ['code', 'name', 'description'],
                [0 => 'code', 1 => 'name', 2 => 'description', 3 => 'is_active'],
                'name'
            ));
        }

        return view('units/index', [
            'title' => 'Unit / Departemen',
            // Dipakai oleh checkbox "lokasi" di modal create/edit.
            'locations' => $this->locationModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    /**
     * Satu unit sebagai JSON, untuk mengisi modal edit DAN modal detail
     * dari halaman index.
     *
     * Bentuknya mengikuti Unit::show() supaya modal detail menampilkan
     * isi yang identik dengan halaman detail.
     */
    public function data($id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return $this->respondError('Unit tidak ditemukan.', 404);
        }

        // Lokasi yang terkait ikut dikirim supaya modal edit bisa
        // mencentang checkbox yang benar tanpa fetch kedua.
        $unit['location_ids'] = array_map(
            'intval',
            array_column(
                $this->unitLocationModel->where('unit_id', $id)->findAll(),
                'location_id'
            )
        );

        $locations = db_connect()
            ->table('unit_locations')
            ->select('locations.id, locations.name, locations.building, locations.floor, locations.room, locations.is_active')
            ->join('locations', 'locations.id = unit_locations.location_id')
            ->where('unit_locations.unit_id', $id)
            ->orderBy('locations.name', 'ASC')
            ->get()
            ->getResultArray();

        $assets = db_connect()
            ->table('assets')
            ->select('assets.id, assets.asset_code, assets.name, assets.condition_status, assets.asset_status, locations.name as location_name')
            ->join('locations', 'locations.id = assets.location_id', 'left')
            ->where('assets.unit_id', $id)
            ->orderBy('assets.name', 'ASC')
            ->get()
            ->getResultArray();

        $unit['locations'] = $locations;
        $unit['assets']    = $assets;

        return $this->respondAjax($unit);
    }

    public function create()
    {
        // Form create berada di modal pada halaman index.
        return redirect()->to('/units?create=1');
    }

    public function store()
    {
        $isAjax = $this->request->isAJAX();

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
            if ($isAjax) {
                return $this->respondError('Unit gagal ditambahkan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Unit gagal ditambahkan.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Unit berhasil ditambahkan.', [
                'id' => $unitId,
            ]);
        }

        return redirect()
            ->to('/units')
            ->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit($id)
    {
        // Form edit berada di modal pada halaman index. Keberadaan
        // record divalidasi di data().
        return redirect()->to('/units?edit=' . (int) $id);
    }

    public function update($id)
    {
        $isAjax = $this->request->isAJAX();

        $unit = $this->unitModel->find($id);

        if (!$unit) {
            if ($isAjax) {
                return $this->respondError('Unit tidak ditemukan.', 404);
            }

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
            if ($isAjax) {
                return $this->respondError('Unit gagal diperbarui.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Unit gagal diperbarui.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Unit berhasil diperbarui.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()
            ->to('/units')
            ->with('success', 'Unit berhasil diperbarui.');
    }

    public function delete($id)
    {
        $isAjax = $this->request->isAJAX();

        $unit = $this->unitModel->find($id);

        if (!$unit) {
            if ($isAjax) {
                return $this->respondError('Unit tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Jangan hapus unit jika masih digunakan aset
        $assetCount = $this->assetModel
            ->where('unit_id', $id)
            ->countAllResults();

        if ($assetCount > 0) {
            if ($isAjax) {
                return $this->respondError(
                    'Unit tidak dapat dihapus karena masih digunakan oleh aset.',
                    422
                );
            }

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

        if ($isAjax) {
            return $this->respondSuccess('Unit berhasil dihapus.', [
                'id' => (int) $id,
            ]);
        }

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
