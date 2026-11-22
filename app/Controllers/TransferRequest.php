<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\InventoryTransactionModel;
use App\Models\LocationModel;
use App\Models\StockItemModel;
use App\Models\TransferRequestModel;
use App\Models\UnitModel;

class TransferRequest extends BaseController
{
    protected TransferRequestModel $requestModel;
    protected AssetModel $assetModel;
    protected StockItemModel $stockItemModel;
    protected InventoryTransactionModel $transactionModel;
    protected LocationModel $locationModel;
    protected UnitModel $unitModel;

    public function __construct()
    {
        $this->requestModel     = new TransferRequestModel();
        $this->assetModel       = new AssetModel();
        $this->stockItemModel   = new StockItemModel();
        $this->transactionModel = new InventoryTransactionModel();
        $this->locationModel    = new LocationModel();
        $this->unitModel        = new UnitModel();
    }

    public function index()
    {
        $locationIds = $this->myLocationIds();

        if (empty($locationIds)) {
            // Super Admin / tanpa pembatasan: semua lokasi
            $locationIds = array_column(
                $this->locationModel->where('is_active', 1)->findAll(),
                'id'
            );
        }

        $incoming = $this->requestModel->pendingIncoming($locationIds);
        $outgoing = $this->requestModel->pendingOutgoing($locationIds);

        // Enrich dengan detail item
        foreach ($incoming as &$r) {
            $r = $this->enrich($r);
        }
        unset($r);
        foreach ($outgoing as &$r) {
            $r = $this->enrich($r);
        }
        unset($r);

        return view('transfer_requests/index', [
            'title'     => 'Permintaan Mutasi',
            'incoming'  => $incoming,
            'outgoing'  => $outgoing,
        ]);
    }

    public function create()
    {
        // Lokasi milik user (tujuan default)
        $myLocations = $this->myLocationList();
        // Semua lokasi aktif (sumber kandidat)
        $allLocations = $this->locationModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        // Sumber = lokasi yang bukan tanggung jawab user (untuk permintaan)
        $myIds = array_column($myLocations, 'id');
        $sourceLocations = array_values(array_filter(
            $allLocations,
            static fn (array $l): bool => !in_array((int) $l['id'], array_map('intval', $myIds), true)
        ));

        // Jika user tanpa pembatasan, semua lokasi bisa jadi sumber & tujuan
        if (empty($myLocations)) {
            $myLocations = $allLocations;
            $sourceLocations = $allLocations;
        }

        return view('transfer_requests/create', [
            'title'            => 'Buat Permintaan Mutasi',
            'myLocations'      => $myLocations,
            'sourceLocations'  => $sourceLocations,
            'units'            => $this->unitModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function storeRequest()
    {
        $isAjax = $this->request->isAJAX();

        $itemType   = $this->request->getPost('item_type'); // Aset / Barang Stok
        $sourceLoc  = (int) $this->request->getPost('from_location_id');
        $destLoc    = (int) $this->request->getPost('to_location_id');
        $assetId    = $this->request->getPost('asset_id');
        $stockId    = $this->request->getPost('stock_item_id');
        $quantity   = (int) ($this->request->getPost('quantity') ?: 1);
        $reason     = $this->request->getPost('reason');
        $notes      = $this->request->getPost('notes');
        $toUnitId   = $this->request->getPost('to_unit_id');

        // Validasi dasar
        if (!in_array($itemType, ['Aset', 'Barang Stok'], true)) {
            return $this->failRequest($isAjax, 'Jenis barang tidak valid.');
        }
        if (!$sourceLoc || !$destLoc || $sourceLoc === $destLoc) {
            return $this->failRequest($isAjax, 'Lokasi sumber dan tujuan harus berbeda.');
        }
        // Tujuan harus tanggung jawab peminta
        if (!$this->canAccess($destLoc)) {
            return $this->failRequest($isAjax, 'Lokasi tujuan harus termasuk tanggung jawab Anda.');
        }
        // Sumber harus bisa dibaca (kita izinkan semua user yang bisa kelola aset)
        if (!$this->canManageAsset()) {
            return $this->failRequest($isAjax, 'Anda tidak memiliki akses.', 403);
        }

        if ($itemType === 'Aset') {
            if (!$assetId) {
                return $this->failRequest($isAjax, 'Aset wajib dipilih.');
            }
            $asset = $this->assetModel->find($assetId);
            if (!$asset || (int) $asset['location_id'] !== $sourceLoc) {
                return $this->failRequest($isAjax, 'Aset tidak ditemukan di lokasi sumber.');
            }
            $quantity = 1;
        } else {
            if (!$stockId) {
                return $this->failRequest($isAjax, 'Barang stok wajib dipilih.');
            }
            $stock = $this->stockItemModel->find($stockId);
            if (!$stock || (int) $stock['location_id'] !== $sourceLoc) {
                return $this->failRequest($isAjax, 'Barang stok tidak ditemukan di lokasi sumber.');
            }
            if ($quantity < 1 || $quantity > (int) $stock['quantity']) {
                return $this->failRequest($isAjax, 'Jumlah tidak valid. Stok tersedia: ' . $stock['quantity'] . '.');
            }
        }

        $this->requestModel->insert([
            'request_kind'     => 'Permintaan',
            'item_type'        => $itemType,
            'asset_id'         => $itemType === 'Aset' ? $assetId : null,
            'stock_item_id'    => $itemType === 'Barang Stok' ? $stockId : null,
            'from_location_id' => $sourceLoc,
            'to_location_id'   => $destLoc,
            'from_unit_id'     => $itemType === 'Aset' ? ($asset['unit_id'] ?? null) : null,
            'to_unit_id'       => $toUnitId ?: null,
            'quantity'         => $quantity,
            'reason'           => $reason,
            'notes'            => $notes,
            'status'           => 'Pending',
            'requested_by'     => session()->get('user_id'),
        ]);

        $newId = (int) $this->requestModel->getInsertID();

        // Unggah bukti (opsional)
        $file = $this->request->getFile('evidence');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $cfg = new \Config\TransferRequestDocuments();
            $storage = \App\Libraries\AttachmentStorage::for($cfg);
            $result = $storage->store($file);
            if ($result['ok'] ?? false) {
                $this->requestModel->update($newId, [
                    'evidence_path'          => $cfg->directory . '/' . $result['data']['stored_name'],
                    'evidence_original_name' => $result['data']['original_name'],
                ]);
            }
        }

        $message = 'Permintaan mutasi berhasil dikirim, menunggu konfirmasi pengelola lokasi sumber.';

        if ($isAjax) {
            return $this->respondSuccess($message, ['id' => $this->requestModel->getInsertID()]);
        }

        return redirect()->to('/transfer-requests')->with('success', $message);
    }

    public function confirm($id)
    {
        $isAjax = $this->request->isAJAX();

        $req = $this->requestModel->find($id);
        if (!$req || $req['status'] !== 'Pending') {
            return $this->failRequest($isAjax, 'Permintaan tidak ditemukan atau sudah diproses.', 404);
        }

        // Penyetuju: lokasi yang BUKAN inisiator
        // Penawaran: inisiator = sumber, penyetuju = tujuan
        // Permintaan: inisiator = tujuan, penyetuju = sumber
        $approverLocationId = $req['request_kind'] === 'Penawaran'
            ? (int) $req['to_location_id']
            : (int) $req['from_location_id'];

        if (!$this->canAccess($approverLocationId)) {
            return $this->failRequest($isAjax, 'Anda tidak memiliki akses untuk menyetujui permintaan ini.', 403);
        }

        $db = db_connect();
        $db->transStart();

        try {
            if ($req['item_type'] === 'Aset') {
                $asset = $this->assetModel->find($req['asset_id']);
                if (!$asset || (int) $asset['location_id'] !== (int) $req['from_location_id']) {
                    throw new \RuntimeException('Aset tidak lagi berada di lokasi asal.');
                }

                $this->assetModel->update($req['asset_id'], [
                    'unit_id'     => $req['to_unit_id'] ?: $asset['unit_id'],
                    'location_id' => $req['to_location_id'],
                ]);

                $this->transactionModel->insert([
                    'transaction_code' => $this->transactionModel->generateCode(),
                    'transaction_date' => date('Y-m-d'),
                    'transaction_type' => 'Mutasi',
                    'item_type'        => 'Aset',
                    'asset_id'         => $req['asset_id'],
                    'quantity'         => 1,
                    'from_location_id' => $req['from_location_id'],
                    'to_location_id'   => $req['to_location_id'],
                    'reference_type'   => 'transfer_request',
                    'reference_id'     => $req['id'],
                    'reason'           => $req['reason'],
                    'notes'            => $req['notes'],
                    'created_by'       => session()->get('user_id'),
                ]);
            } else {
                $stock = $this->stockItemModel->find($req['stock_item_id']);
                if (!$stock || (int) $stock['location_id'] !== (int) $req['from_location_id']) {
                    throw new \RuntimeException('Barang stok tidak lagi berada di lokasi asal.');
                }

                $qty = (int) $req['quantity'];
                if ($qty < 1 || $qty > (int) $stock['quantity']) {
                    throw new \RuntimeException('Stok tidak mencukupi (tersedia: ' . $stock['quantity'] . ').');
                }

                // Kurangi stok sumber (atomik)
                $db->table('stock_items')
                    ->set('quantity', 'quantity - ' . $qty, false)
                    ->where('id', $req['stock_item_id'])
                    ->where('quantity >=', $qty)
                    ->update();

                if ($db->affectedRows() === 0) {
                    throw new \RuntimeException('Stok tidak mencukupi.');
                }

                // Tambah/buat stok tujuan
                $destStock = $db->table('stock_items')
                    ->where('location_id', $req['to_location_id'])
                    ->where('item_code', $stock['item_code'])
                    ->get()
                    ->getRowArray();

                if ($destStock) {
                    $db->table('stock_items')
                        ->set('quantity', 'quantity + ' . $qty, false)
                        ->where('id', $destStock['id'])
                        ->update();
                } else {
                    // Buat baris baru dengan kode unik turunan bila perlu
                    $code = $stock['item_code'];
                    $exists = $db->table('stock_items')->where('item_code', $code)->countAllResults();
                    if ($exists > 0) {
                        $code = $stock['item_code'] . '-' . $req['to_location_id'];
                        $exists2 = $db->table('stock_items')->where('item_code', $code)->countAllResults();
                        if ($exists2 > 0) {
                            $code = $stock['item_code'] . '-' . $req['to_location_id'] . '-' . time();
                        }
                    }

                    $this->stockItemModel->insert([
                        'item_code'   => $code,
                        'name'        => $stock['name'],
                        'category_id' => $stock['category_id'],
                        'unit_id'     => $req['to_unit_id'] ?: $stock['unit_id'],
                        'location_id' => $req['to_location_id'],
                        'satuan'      => $stock['satuan'],
                        'quantity'    => $qty,
                        'description' => $stock['description'],
                        'is_active'   => 1,
                    ]);
                }

                $this->transactionModel->insert([
                    'transaction_code' => $this->transactionModel->generateCode(),
                    'transaction_date' => date('Y-m-d'),
                    'transaction_type' => 'Pindah',
                    'item_type'        => 'Barang Stok',
                    'stock_item_id'    => $req['stock_item_id'],
                    'quantity'         => $qty,
                    'from_location_id' => $req['from_location_id'],
                    'to_location_id'   => $req['to_location_id'],
                    'reference_type'   => 'transfer_request',
                    'reference_id'     => $req['id'],
                    'reason'           => $req['reason'],
                    'notes'            => $req['notes'],
                    'created_by'       => session()->get('user_id'),
                ]);
            }

            $this->requestModel->update($id, [
                'status'       => 'Disetujui',
                'confirmed_by' => session()->get('user_id'),
                'confirmed_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->failRequest($isAjax, $e->getMessage(), 422);
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->failRequest($isAjax, 'Gagal memproses permintaan.', 500);
        }

        $message = 'Permintaan berhasil disetujui; barang telah dipindahkan.';
        if ($isAjax) {
            return $this->respondSuccess($message);
        }
        return redirect()->to('/transfer-requests')->with('success', $message);
    }

    public function reject($id)
    {
        $isAjax = $this->request->isAJAX();
        $req = $this->requestModel->find($id);
        if (!$req || $req['status'] !== 'Pending') {
            return $this->failRequest($isAjax, 'Permintaan tidak ditemukan atau sudah diproses.', 404);
        }

        $approverLocationId = $req['request_kind'] === 'Penawaran'
            ? (int) $req['to_location_id']
            : (int) $req['from_location_id'];

        if (!$this->canAccess($approverLocationId)) {
            return $this->failRequest($isAjax, 'Anda tidak memiliki akses.', 403);
        }

        $this->requestModel->update($id, [
            'status'       => 'Ditolak',
            'confirmed_by' => session()->get('user_id'),
            'confirmed_at' => date('Y-m-d H:i:s'),
        ]);

        $message = 'Permintaan ditolak.';
        if ($isAjax) {
            return $this->respondSuccess($message);
        }
        return redirect()->to('/transfer-requests')->with('success', $message);
    }

    public function cancel($id)
    {
        $isAjax = $this->request->isAJAX();
        $req = $this->requestModel->find($id);
        if (!$req || $req['status'] !== 'Pending') {
            return $this->failRequest($isAjax, 'Permintaan tidak ditemukan atau sudah diproses.', 404);
        }

        $userId = (int) session()->get('user_id');
        $isRequester = ((int) $req['requested_by'] === $userId);
        $isSourceManager = $this->canAccess((int) $req['from_location_id']);

        if (!$isRequester && !$isSourceManager) {
            return $this->failRequest($isAjax, 'Anda tidak memiliki akses untuk membatalkan.', 403);
        }

        $this->requestModel->update($id, [
            'status'       => 'Ditolak',
            'confirmed_by' => $userId,
            'confirmed_at' => date('Y-m-d H:i:s'),
            'notes'        => trim(($req['notes'] ?? '') . "\n[Dibatalkan]"),
        ]);

        $message = 'Permintaan dibatalkan.';
        if ($isAjax) {
            return $this->respondSuccess($message);
        }
        return redirect()->to('/transfer-requests')->with('success', $message);
    }

    public function uploadEvidence($id)
    {
        $isAjax = $this->request->isAJAX();
        $req = $this->requestModel->find($id);
        if (!$req) {
            return $this->failRequest($isAjax, 'Permintaan tidak ditemukan.', 404);
        }

        $userId = (int) session()->get('user_id');
        $canAccessSource = $this->canAccess((int) $req['from_location_id']);
        $canAccessDest   = $this->canAccess((int) $req['to_location_id']);
        if (!$canAccessSource && !$canAccessDest) {
            return $this->failRequest($isAjax, 'Anda tidak memiliki akses.', 403);
        }

        $file = $this->request->getFile('evidence');
        if (!$file || !$file->isValid()) {
            return $this->failRequest($isAjax, 'Berkas tidak valid atau tidak dipilih.');
        }

        $cfg = new \Config\TransferRequestDocuments();
        $storage = \App\Libraries\AttachmentStorage::for($cfg);

        // validasi mime/ext
        $mime = $file->getMimeType();
        if (!in_array($mime, $cfg->allowedMimes, true)) {
            return $this->failRequest($isAjax, 'Tipe berkas tidak diizinkan.');
        }
        if ($file->getSize() > $cfg->maxSizeBytes) {
            return $this->failRequest($isAjax, 'Ukuran berkas melebihi batas.');
        }

        $result = $storage->store($file);
        if (!($result['ok'] ?? false)) {
            return $this->failRequest($isAjax, $result['error'] ?? 'Gagal menyimpan berkas.');
        }

        $storedName = $result['data']['stored_name'];

        // hapus berkas lama
        if (!empty($req['evidence_path'])) {
            try {
                $storage->deleteFiles([['stored_name' => basename($req['evidence_path'])]]);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $this->requestModel->update($id, [
            'evidence_path'          => $cfg->directory . '/' . $storedName,
            'evidence_original_name' => $result['data']['original_name'],
        ]);

        $message = 'Bukti serah terima berhasil diunggah.';
        if ($isAjax) {
            return $this->respondSuccess($message, [
                'path' => $result['path'] ?? null,
            ]);
        }
        return redirect()->to('/transfer-requests')->with('success', $message);
    }

    public function viewEvidence($id)
    {
        $req = $this->requestModel->find($id);
        if (!$req || empty($req['evidence_path'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $canAccessSource = $this->canAccess((int) $req['from_location_id']);
        $canAccessDest   = $this->canAccess((int) $req['to_location_id']);
        if (!$canAccessSource && !$canAccessDest) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $cfg = new \Config\TransferRequestDocuments();
        $storage = \App\Libraries\AttachmentStorage::for($cfg);
        $storedName = basename($req['evidence_path']);
        $fullPath = $storage->dir(false) . DIRECTORY_SEPARATOR . $storedName;

        if (!is_file($fullPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        $this->response->setHeader('Content-Type', $mime);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . ($req['evidence_original_name'] ?: $storedName) . '"');
        $this->response->setBody(file_get_contents($fullPath));
        return $this->response;
    }

    // ---- Read-only endpoints untuk memilih item dari lokasi lain ----

    public function assetsByLocation($locationId)
    {
        if (!$this->canManageAsset()) {
            return $this->response->setStatusCode(403)->setJSON([]);
        }
        $rows = $this->assetModel
            ->select('assets.id, assets.asset_code, assets.name, assets.location_id')
            ->where('assets.location_id', (int) $locationId)
            ->where('assets.asset_status', 'Aktif')
            ->orderBy('assets.name', 'ASC')
            ->findAll();
        return $this->response->setContentType('application/json')->setJSON($rows);
    }

    public function stockByLocation($locationId)
    {
        if (!$this->canManageAsset()) {
            return $this->response->setStatusCode(403)->setJSON([]);
        }
        $rows = $this->stockItemModel
            ->select('stock_items.id, stock_items.item_code, stock_items.name, stock_items.quantity, stock_items.satuan, stock_items.location_id')
            ->where('stock_items.location_id', (int) $locationId)
            ->where('stock_items.is_active', 1)
            ->orderBy('stock_items.name', 'ASC')
            ->findAll();
        return $this->response->setContentType('application/json')->setJSON($rows);
    }

    // ---- helpers ----

    private function myLocationIds(): array
    {
        $ids = session()->get('location_ids') ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }
        return array_map('intval', $ids);
    }

    private function myLocationList(): array
    {
        $ids = $this->myLocationIds();
        if (empty($ids)) {
            return [];
        }
        return $this->locationModel
            ->where('is_active', 1)
            ->whereIn('id', $ids)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function canAccess($locationId): bool
    {
        $roles = session()->get('roles') ?? [];
        if (in_array('Super Admin', $roles, true)) {
            return true;
        }
        $ids = $this->myLocationIds();
        if (empty($ids)) {
            return true;
        }
        return in_array((int) $locationId, $ids, true);
    }

    private function canManageAsset(): bool
    {
        $roles = session()->get('roles') ?? [];
        return in_array('Super Admin', $roles, true)
            || in_array('Admin Inventaris', $roles, true)
            || in_array('Petugas Inventaris', $roles, true);
    }

    private function enrich(array $r): array
    {
        if ($r['item_type'] === 'Aset' && $r['asset_id']) {
            $a = $this->assetModel->find($r['asset_id']);
            $r['item_name'] = $a ? ($a['asset_code'] . ' - ' . $a['name']) : '-';
        } else {
            $s = $this->stockItemModel->find($r['stock_item_id']);
            $r['item_name'] = $s ? ($s['item_code'] . ' - ' . $s['name']) : '-';
        }
        return $r;
    }

    private function failRequest(bool $isAjax, string $message, int $code = 422)
    {
        if ($isAjax) {
            return $this->respondError($message, $code);
        }
        return redirect()->back()->withInput()->with('error', $message);
    }
}