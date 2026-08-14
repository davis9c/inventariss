<?php

namespace App\Controllers;

use App\Models\MaintenanceModel;
use App\Models\AssetModel;

class Maintenance extends BaseController
{
    protected MaintenanceModel $maintenanceModel;
    protected AssetModel $assetModel;

    public function __construct()
    {
        $this->maintenanceModel = new MaintenanceModel();
        $this->assetModel = new AssetModel();
    }

    public function index()
    {
        $maintenances = $this->maintenanceModel
            ->select('
                maintenances.*,
                assets.asset_code,
                assets.name as asset_name
            ')
            ->join(
                'assets',
                'assets.id = maintenances.asset_id'
            )
            ->orderBy(
                'maintenances.maintenance_date',
                'DESC'
            )
            ->findAll();

        return view('maintenances/index', [
            'title'        => 'Maintenance & Perbaikan',
            'maintenances' => $maintenances,
        ]);
    }

    public function show($id)
    {
        $maintenance = $this->maintenanceModel
            ->select('
            maintenances.*,
            assets.asset_code,
            assets.name as asset_name,
            users.name as approved_by_name
        ')
            ->join(
                'assets',
                'assets.id = maintenances.asset_id'
            )
            ->join(
                'users',
                'users.id = maintenances.approved_by',
                'left'
            )
            ->find($id);

        if (!$maintenance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('maintenances/show', [
            'title'       => 'Detail Maintenance',
            'maintenance' => $maintenance,
        ]);
    }

    public function create()
    {
        // Hanya Teknisi dan Super Admin
        if (!hasAnyRole(['Teknisi', 'Super Admin'])) {
            return redirect()
                ->to('/maintenances')
                ->with('error', 'Anda tidak memiliki akses untuk membuat maintenance.');
        }

        $selectedAssetId = $this->request->getGet('asset_id');

        return view('maintenances/create', [
            'title'          => 'Tambah Maintenance',
            'selectedAssetId' => $selectedAssetId,
            'assets'         => $this->assetModel
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function store()
    {
        // Cek role
        if (!hasAnyRole(['Teknisi', 'Super Admin'])) {
            return redirect()
                ->to('/maintenances')
                ->with('error', 'Anda tidak memiliki akses untuk melakukan maintenance.');
        }

        $userId   = session()->get('user_id');
        $userName = session()->get('name');

        $code = 'MNT-' . date('YmdHis');

        $this->maintenanceModel->insert([
            'maintenance_code' => $code,
            'asset_id'         => $this->request->getPost('asset_id'),
            'maintenance_date' => $this->request->getPost('maintenance_date'),
            'maintenance_type' => $this->request->getPost('maintenance_type'),
            'problem'          => $this->request->getPost('problem'),
            'action_taken'     => $this->request->getPost('action_taken'),

            // Teknisi otomatis dari user login
            'technician_type' => 'Internal',
            'technician_id'   => $userId,
            'technician_name' => $userName,

            'vendor_name'    => $this->request->getPost('vendor_name'),
            'cost'           => $this->request->getPost('cost') ?: 0,
            'status'         => $this->request->getPost('status') ?: 'Diajukan',
            'completed_date' => $this->request->getPost('completed_date') ?: null,
            'notes'          => $this->request->getPost('notes'),

            // User yang membuat data
            'created_by' => $userId,
        ]);

        return redirect()
            ->to('/maintenances')
            ->with(
                'success',
                'Data maintenance berhasil ditambahkan.'
            );
    }

    public function edit($id)
    {
        if (!hasAnyRole(['Teknisi', 'Super Admin'])) {
            return redirect()
                ->to('/maintenances')
                ->with('error', 'Anda tidak memiliki akses.');
        }

        $maintenance = $this->maintenanceModel->find($id);

        if (!$maintenance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('maintenances/edit', [
            'title'       => 'Edit Maintenance',
            'maintenance' => $maintenance,
            'assets'      => $this->assetModel
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function update($id)
    {
        if (!hasAnyRole(['Teknisi', 'Super Admin'])) {
            return redirect()
                ->to('/maintenances')
                ->with('error', 'Anda tidak memiliki akses.');
        }

        $maintenance = $this->maintenanceModel->find($id);

        if (!$maintenance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->maintenanceModel->update($id, [
            'asset_id'         => $this->request->getPost('asset_id'),
            'maintenance_date' => $this->request->getPost('maintenance_date'),
            'maintenance_type' => $this->request->getPost('maintenance_type'),
            'problem'          => $this->request->getPost('problem'),
            'action_taken'     => $this->request->getPost('action_taken'),
            'technician_type'  => $this->request->getPost('technician_type'),
            'technician_id'    => $this->request->getPost('technician_id'),
            'technician_name'  => $this->request->getPost('technician_name'),
            'vendor_name'      => $this->request->getPost('vendor_name'),
            'cost'             => $this->request->getPost('cost') ?: 0,
            'completed_date'   => $this->request->getPost('completed_date') ?: null,
            'notes'            => $this->request->getPost('notes'),
        ]);

        return redirect()
            ->to('/maintenances')
            ->with(
                'success',
                'Data maintenance berhasil diperbarui.'
            );
    }

    public function delete($id)
    {
        if (!isSuperAdmin()) {
            return redirect()
                ->to('/maintenances')
                ->with('error', 'Hanya Super Admin yang dapat menghapus maintenance.');
        }

        $this->maintenanceModel->delete($id);

        return redirect()
            ->to('/maintenances')
            ->with(
                'success',
                'Data maintenance berhasil dihapus.'
            );
    }

    public function approve($id)
    {
        $maintenance = $this->maintenanceModel->find($id);

        if (!$maintenance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($maintenance['status'] !== 'Diajukan') {
            return redirect()
                ->back()
                ->with('error', 'Maintenance tidak dapat disetujui.');
        }

        $this->maintenanceModel->update($id, [
            'status'      => 'Disetujui',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
            'approval_notes' => $this->request->getPost('approval_notes'),
        ]);

        return redirect()
            ->to('/maintenances')
            ->with('success', 'Maintenance berhasil disetujui.');
    }

    public function reject($id)
    {
        $maintenance = $this->maintenanceModel->find($id);

        if (!$maintenance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($maintenance['status'] !== 'Diajukan') {
            return redirect()
                ->back()
                ->with('error', 'Maintenance tidak dapat ditolak.');
        }

        $notes = $this->request->getPost('approval_notes');

        if (empty(trim($notes))) {
            return redirect()
                ->back()
                ->with('error', 'Alasan penolakan wajib diisi.');
        }

        $this->maintenanceModel->update($id, [
            'status'         => 'Ditolak',
            'approved_by'    => session()->get('user_id'),
            'approved_at'    => date('Y-m-d H:i:s'),
            'approval_notes' => $notes,
        ]);

        return redirect()
            ->to('/maintenances')
            ->with('success', 'Maintenance ditolak.');
    }

    public function start($id)
    {
        $maintenance = $this->maintenanceModel->find($id);

        if (!$maintenance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($maintenance['status'] !== 'Disetujui') {
            return redirect()->back()
                ->with('error', 'Maintenance belum disetujui.');
        }

        $this->maintenanceModel->update($id, [
            'status' => 'Diproses',
        ]);

        return redirect()->to('/maintenances')
            ->with('success', 'Maintenance mulai diproses.');
    }

    public function complete($id)
    {
        $maintenance = $this->maintenanceModel->find($id);

        if (!$maintenance) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($maintenance['status'] !== 'Diproses') {
            return redirect()->back()
                ->with('error', 'Maintenance belum dalam proses.');
        }

        $this->maintenanceModel->update($id, [
            'status' => 'Selesai',
            'completed_date' => date('Y-m-d'),
        ]);

        return redirect()->to('/maintenances')
            ->with('success', 'Maintenance berhasil diselesaikan.');
    }
}
