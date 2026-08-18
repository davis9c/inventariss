<?php

namespace App\Controllers;

use App\Models\InventoryDocumentModel;
use App\Models\InventoryPhotoModel;
use App\Models\TransactionEvidenceModel;
use App\Models\InventoryTransactionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Attachment extends BaseController
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    public function storeDocument(string $ownerType, int $ownerId)
    {
        $this->assertOwner($ownerType, $ownerId);
        $file = $this->validatedFile(['application/pdf', 'image/jpeg', 'image/png', 'image/webp']);
        if (!$file) return redirect()->back()->with('error', 'File wajib berupa PDF/JPG/PNG/WebP dan maksimal 10 MB.');
        $originalName = $file->getClientName();
        $mimeType = $file->getMimeType();
        $path = $this->storeFile($file, 'documents');
        $model = new InventoryDocumentModel();
        $model->insert(['owner_type' => $ownerType, 'owner_id' => $ownerId, 'document_type' => $this->request->getPost('document_type') ?: 'Lainnya', 'document_number' => $this->request->getPost('document_number'), 'document_date' => $this->request->getPost('document_date') ?: null, 'valid_from' => $this->request->getPost('valid_from') ?: null, 'valid_until' => $this->request->getPost('valid_until') ?: null, 'file_path' => $path, 'original_name' => $originalName, 'mime_type' => $mimeType, 'notes' => $this->request->getPost('notes'), 'created_by' => session()->get('user_id')]);
        $this->audit('CREATE', 'document', $model->getInsertID(), null, ['owner_type' => $ownerType, 'owner_id' => $ownerId]);
        return redirect()->back()->with('success', 'Dokumen ditambahkan ke riwayat.');
    }

    public function storePhoto(string $ownerType, int $ownerId)
    {
        $this->assertOwner($ownerType, $ownerId);
        $file = $this->validatedFile(['image/jpeg', 'image/png', 'image/webp']);
        if (!$file) return redirect()->back()->with('error', 'Foto wajib JPG/PNG/WebP dan maksimal 10 MB.');
        $originalName = $file->getClientName();
        $mimeType = $file->getMimeType();
        $path = $this->storeFile($file, 'photos');
        $model = new InventoryPhotoModel();
        $model->insert(['owner_type' => $ownerType, 'owner_id' => $ownerId, 'file_path' => $path, 'original_name' => $originalName, 'mime_type' => $mimeType, 'caption' => $this->request->getPost('caption'), 'created_by' => session()->get('user_id')]);
        $this->audit('CREATE', 'photo', $model->getInsertID(), null, ['owner_type' => $ownerType, 'owner_id' => $ownerId]);
        return redirect()->back()->with('success', 'Foto ditambahkan ke histori.');
    }

    public function storeEvidence(int $transactionId)
    {
        $transaction = (new InventoryTransactionModel())->find($transactionId);
        if (!$transaction) throw PageNotFoundException::forPageNotFound();
        $locations = array_filter([$transaction['from_location_id'], $transaction['to_location_id']]);
        if (has_location_restriction() && (!array_intersect($locations, user_location_ids()))) return redirect()->back()->with('error', 'Anda tidak memiliki akses ke transaksi ini.');
        $file = $this->validatedFile(['application/pdf', 'image/jpeg', 'image/png', 'image/webp']);
        if (!$file) return redirect()->back()->with('error', 'Bukti wajib PDF/JPG/PNG/WebP dan maksimal 10 MB.');
        $originalName = $file->getClientName();
        $mimeType = $file->getMimeType();
        $path = $this->storeFile($file, 'evidence');
        $model = new TransactionEvidenceModel();
        $model->insert(['transaction_id' => $transactionId, 'evidence_type' => $this->request->getPost('evidence_type') ?: 'Dokumen', 'file_path' => $path, 'original_name' => $originalName, 'mime_type' => $mimeType, 'notes' => $this->request->getPost('notes'), 'created_by' => session()->get('user_id')]);
        $this->audit('CREATE', 'transaction_evidence', $model->getInsertID(), null, ['transaction_id' => $transactionId]);
        return redirect()->back()->with('success', 'Bukti transaksi ditambahkan.');
    }

    public function deletePhoto(int $id)
    {
        if (!isSuperAdmin()) return redirect()->back()->with('error', 'Hanya Super Admin dapat menghapus foto.');
        $model = new InventoryPhotoModel(); $photo = $model->find($id); if (!$photo) throw PageNotFoundException::forPageNotFound();
        $model->delete($id); $this->audit('SOFT_DELETE', 'photo', $id, $photo);
        return redirect()->back()->with('success', 'Foto dihapus secara soft delete.');
    }

    public function file(string $kind, int $id)
    {
        $models = ['document' => InventoryDocumentModel::class, 'photo' => InventoryPhotoModel::class, 'evidence' => TransactionEvidenceModel::class];
        if (!isset($models[$kind])) throw PageNotFoundException::forPageNotFound();
        $record = (new $models[$kind]())->find($id);
        if (!$record) throw PageNotFoundException::forPageNotFound();
        $path = WRITEPATH . 'uploads/' . $record['file_path'];
        if (!is_file($path)) throw PageNotFoundException::forPageNotFound();
        return $this->response->setHeader('Content-Type', $record['mime_type'])->setHeader('Content-Disposition', 'inline; filename="' . str_replace('"', '', $record['original_name']) . '"')->setBody(file_get_contents($path));
    }

    private function assertOwner(string $type, int $id): void
    {
        $models = ['asset' => \App\Models\AssetModel::class, 'location' => \App\Models\LocationModel::class, 'stock_item' => \App\Models\StockItemModel::class];
        if (!isset($models[$type]) || !($owner = (new $models[$type]())->find($id))) throw PageNotFoundException::forPageNotFound();
        if (isset($owner['location_id']) && !can_access_location((int) $owner['location_id'])) throw PageNotFoundException::forPageNotFound();
    }
    private function validatedFile(array $allowed): ?\CodeIgniter\HTTP\Files\UploadedFile
    {
        $file = $this->request->getFile('file');
        return $file && $file->isValid() && !$file->hasMoved() && $file->getSize() <= self::MAX_FILE_SIZE && in_array($file->getMimeType(), $allowed, true) ? $file : null;
    }
    private function storeFile(\CodeIgniter\HTTP\Files\UploadedFile $file, string $group): string
    {
        $directory = WRITEPATH . 'uploads/inventory/' . $group;
        if (!is_dir($directory)) mkdir($directory, 0750, true);
        $name = $file->getRandomName(); $file->move($directory, $name);
        return 'inventory/' . $group . '/' . $name;
    }
}
