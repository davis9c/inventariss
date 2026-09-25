<?php

namespace App\Controllers;

use App\Models\InventoryTransactionModel;
use App\Models\ItemImageModel;
use App\Models\LocationModel;
use App\Models\TransactionDocumentModel;
use App\Traits\HandlesItemImages;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Model;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\ItemImages;
use Config\TransactionDocuments;

class InventoryTransaction extends BaseController
{
    // Lampiran (dokumen + gambar) pada catatan pergerakan. Enam endpoint-nya
    // dikendalikan trait; yang berbeda dari Aset dan Barang Stok hanya
    // cara hak akses lokasi dihitung -- lihat attachmentOwnerAccessible().
    use HandlesItemImages;

    // $imageItemType sengaja TIDAK dideklarasikan di sini: satu controller
    // melayani transaksi barang stok dan aset sekaligus, jadi bentuk
    // lampirannya ikut transaksi yang sedang diproses -- lihat
    // attachmentOwnerType().
    protected string $imageOwnerLabel = 'Catatan pergerakan';

    protected InventoryTransactionModel $transactionModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->transactionModel = new InventoryTransactionModel();
        $this->locationModel    = new LocationModel();
    }

    // ── Hook trait HandlesItemImages ────────────────────────────

    /**
     * Bentuk lampiran untuk sebuah transaksi.
     *
     * Diturunkan dari item_type transaksi supaya lampiran barang stok dan
     * aset tidak bisa saling dicampur lewat jalur yang sama.
     */
    private function attachmentTypeFor(array $transaction): string
    {
        return ($transaction['item_type'] ?? '') === 'Aset'
            ? ItemImageModel::TYPE_ASSET_MOVEMENT
            : ItemImageModel::TYPE_STOCK_MOVEMENT;
    }

    protected function findAttachmentOwner(int $id): ?array
    {
        $transaction = $this->transactionModel->find($id);

        if ($transaction === null) {
            return null;
        }

        $transaction['attachment_type'] = $this->attachmentTypeFor($transaction);

        return $transaction;
    }

    /**
     * Catatan pergerakan tidak punya satu lokasi tunggal: satu transaksi
     * bisa berjalan dari lokasi A ke lokasi B. Jadi aturanlampirannya
     * diturunkan dari aturan geraknya (can_access_transaction), bukan dari
     * can_access_location seperti barang.
     *
     * Penting: keduanya harus tetap sama. Kalau lampiran lebih longgar dari
     * gerakannya, user yang tidak berhak membuka transaksi tetap bisa
     * membaca lampirannya dengan menebak id.
     */
    protected function attachmentOwnerAccessible(array $owner): bool
    {
        return can_access_transaction($owner);
    }

    /**
     * imageItemType dibaca trait dari "induk" yang ditemukan, jadi selalu
     * ikut jenis transaksinya -- tidak ada nilai tetap per controller.
     */
    protected function attachmentOwnerType(array $owner): string
    {
        return $owner['attachment_type'] ?? ItemImageModel::TYPE_STOCK_MOVEMENT;
    }

    protected function attachmentModel(string $kind): Model
    {
        return $kind === self::KIND_DOCUMENT
            ? new TransactionDocumentModel()
            : new ItemImageModel();
    }

    protected function attachmentConfig(string $kind): BaseConfig
    {
        return $kind === self::KIND_DOCUMENT
            ? new TransactionDocuments()
            : new ItemImages();
    }

    protected function attachmentNoun(string $kind): string
    {
        return $kind === self::KIND_DOCUMENT ? 'dokumen' : 'gambar';
    }

    public function index()
    {
        /*
         * Filter pencarian
         */
        $dateFrom = $this->request->getGet('date_from');
        $dateTo   = $this->request->getGet('date_to');
        $types    = $this->request->getGet('transaction_type');
        $types    = $types ? (is_array($types) ? $types : [$types]) : [];
        $itemType = $this->request->getGet('item_type');
        $locationId = $this->request->getGet('location_id');

        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'inventory_transactions',
                function ($b) use ($dateFrom, $dateTo, $types, $itemType, $locationId) {
                    $b->select('
                        inventory_transactions.*,
                        assets.asset_code,
                        assets.name as asset_name,
                        stock_items.item_code,
                        stock_items.name as item_name,
                        stock_items.satuan,
                        fl.name as from_location_name,
                        tl.name as to_location_name,
                        users.name as created_by_name
                    ')
                        ->join(
                            'assets',
                            'assets.id = inventory_transactions.asset_id',
                            'left'
                        )
                        ->join(
                            'stock_items',
                            'stock_items.id = inventory_transactions.stock_item_id',
                            'left'
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
                        );

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

                    if ($dateFrom) {
                        $b->where('inventory_transactions.transaction_date >=', $dateFrom);
                    }

                    if ($dateTo) {
                        $b->where('inventory_transactions.transaction_date <=', $dateTo);
                    }

                    if ($types) {
                        $b->whereIn('inventory_transactions.transaction_type', $types);
                    }

                    if ($itemType) {
                        $b->where('inventory_transactions.item_type', $itemType);
                    }

                    if ($locationId) {
                        $b->groupStart()
                            ->where('inventory_transactions.from_location_id', $locationId)
                            ->orWhere('inventory_transactions.to_location_id', $locationId)
                            ->groupEnd();
                    }
                },
                [
                    'inventory_transactions.transaction_code',
                    'assets.asset_code',
                    'assets.name',
                    'stock_items.item_code',
                    'stock_items.name',
                    'fl.name',
                    'tl.name',
                    'users.name',
                    'inventory_transactions.notes',
                    'inventory_transactions.reason',
                ],
                [
                    0 => 'inventory_transactions.transaction_date',
                    1 => 'inventory_transactions.transaction_code',
                    2 => 'inventory_transactions.transaction_type',
                    3 => 'inventory_transactions.item_type',
                    5 => 'inventory_transactions.quantity',
                    6 => 'fl.name',
                    7 => 'tl.name',
                    8 => 'users.name',
                ],
                'inventory_transactions.transaction_date',
                'DESC'
            ));
        }

        $builder = $this->transactionModel
            ->select('
                inventory_transactions.*,
                assets.asset_code,
                assets.name as asset_name,
                stock_items.item_code,
                stock_items.name as item_name,
                stock_items.satuan,
                fl.name as from_location_name,
                tl.name as to_location_name,
                users.name as created_by_name
            ')
            ->join(
                'assets',
                'assets.id = inventory_transactions.asset_id',
                'left'
            )
            ->join(
                'stock_items',
                'stock_items.id = inventory_transactions.stock_item_id',
                'left'
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
            );

        /*
         * Filter hak akses lokasi
         */
        if (has_location_restriction()) {
            $locationIds = user_location_ids();

            $builder->groupStart()
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

        if ($dateFrom) {
            $builder->where('inventory_transactions.transaction_date >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('inventory_transactions.transaction_date <=', $dateTo);
        }

        if ($types) {
            $builder->whereIn('inventory_transactions.transaction_type', $types);
        }

        if ($itemType) {
            $builder->where('inventory_transactions.item_type', $itemType);
        }

        if ($locationId) {
            $builder->groupStart()
                ->where('inventory_transactions.from_location_id', $locationId)
                ->orWhere('inventory_transactions.to_location_id', $locationId)
                ->groupEnd();
        }

        $transactions = $builder
            ->orderBy('inventory_transactions.transaction_date', 'DESC')
            ->orderBy('inventory_transactions.id', 'DESC')
            ->findAll();

        return view('stock_movements/index', [
            'title'        => 'Stock Movement',
            'transactions' => $transactions,
            'locations'    => $this->locationModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'filters' => [
                'date_from'        => $dateFrom,
                'date_to'          => $dateTo,
                'transaction_type' => $types,
                'item_type'        => $itemType,
                'location_id'      => $locationId,
            ],
        ]);
    }

    /**
     * Satu transaksi sebagai JSON, untuk mengisi modal detail dari
     * halaman index.
     *
     * Query-nya sama dengan show() supaya modal dan halaman menampilkan
     * kolom yang identik, dan penjagaan pembatasan lokasi juga
     * diulang di sini.
     */
    public function data($id)
    {
        $transaction = $this->transactionModel
            ->select('
                inventory_transactions.*,
                assets.asset_code,
                assets.name as asset_name,
                stock_items.item_code,
                stock_items.name as item_name,
                stock_items.satuan,
                fl.name as from_location_name,
                tl.name as to_location_name,
                users.name as created_by_name
            ')
            ->join('assets', 'assets.id = inventory_transactions.asset_id', 'left')
            ->join('stock_items', 'stock_items.id = inventory_transactions.stock_item_id', 'left')
            ->join('locations fl', 'fl.id = inventory_transactions.from_location_id', 'left')
            ->join('locations tl', 'tl.id = inventory_transactions.to_location_id', 'left')
            ->join('users', 'users.id = inventory_transactions.created_by', 'left')
            ->where('inventory_transactions.id', $id)
            ->first();

        if (!$transaction) {
            return $this->respondError('Transaksi tidak ditemukan.', 404);
        }

        if (! can_access_transaction($transaction)) {
            return $this->respondError(
                'Anda tidak memiliki akses ke transaksi tersebut.',
                403
            );
        }

        return $this->respondAjax($transaction);
    }

    public function show($id)
    {
        $transaction = $this->transactionModel
            ->select('
                inventory_transactions.*,
                assets.asset_code,
                assets.name as asset_name,
                stock_items.item_code,
                stock_items.name as item_name,
                stock_items.satuan,
                fl.name as from_location_name,
                tl.name as to_location_name,
                users.name as created_by_name
            ')
            ->join(
                'assets',
                'assets.id = inventory_transactions.asset_id',
                'left'
            )
            ->join(
                'stock_items',
                'stock_items.id = inventory_transactions.stock_item_id',
                'left'
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
            ->where('inventory_transactions.id', $id)
            ->first();

        if (!$transaction) {
            throw PageNotFoundException::forPageNotFound();
        }

        /*
         * User dengan pembatasan lokasi hanya boleh melihat
         * transaksi yang melibatkan lokasi yang menjadi hak aksesnya.
         * Aturan yang sama dipakai endpoint lampiran di bawah, supaya tidak
         * ada celah di mana lampiran lebih longgar dari transaksinya.
         */
        if (! can_access_transaction($transaction)) {
            return redirect()
                ->to('/stock-movements')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke transaksi tersebut.'
                );
        }

        // Bentuk lampiran mengikuti item_type transaksi, jadi lampiran
        // barang stok dan aset tidak saling bercampur.
        $txType = $this->attachmentTypeFor($transaction);

        return view('stock_movements/show', [
            'title'            => 'Detail Transaksi',
            'transaction'      => $transaction,
            'transactionType'  => $txType,
            'documents'        => (new TransactionDocumentModel())
                ->forItem($txType, (int) $id),
            'images'           => (new ItemImageModel())
                ->forItem($txType, (int) $id),
            'documentConfig'   => new TransactionDocuments(),
            'imageConfig'      => new ItemImages(),
        ]);
    }
}
