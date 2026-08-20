<?php

namespace App\Controllers;

use App\Models\InventoryTransactionModel;
use App\Models\LocationModel;
use App\Models\TransactionEvidenceModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class InventoryTransaction extends BaseController
{
    protected InventoryTransactionModel $transactionModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->transactionModel = new InventoryTransactionModel();
        $this->locationModel    = new LocationModel();
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
            $response = $this->datatableResponse(
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
                        fu.name as from_unit_name,
                        tu.name as to_unit_name,
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
                            'units fu',
                            'fu.id = inventory_transactions.from_unit_id',
                            'left'
                        )
                        ->join(
                            'units tu',
                            'tu.id = inventory_transactions.to_unit_id',
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
                        $b->where('inventory_transactions.transaction_date >=', waktu_wib_to_utc($dateFrom));
                    }

                    if ($dateTo) {
                        $b->where('inventory_transactions.transaction_date <=', waktu_wib_to_utc($dateTo . ' 23:59:59'));
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
                    'fu.name',
                    'tu.name',
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
                    7 => 'fu.name',
                    8 => 'tl.name',
                    9 => 'tu.name',
                    10 => 'users.name',
                ],
                'inventory_transactions.transaction_date',
                'DESC'
            );

            foreach ($response['data'] as &$row) {
                $row['transaction_date'] = waktu_utc7($row['transaction_date']);
                $row['created_at']        = waktu_utc7($row['created_at']);
            }
            unset($row);

            return $this->respondAjax($response);
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
                fu.name as from_unit_name,
                tu.name as to_unit_name,
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
                'units fu',
                'fu.id = inventory_transactions.from_unit_id',
                'left'
            )
            ->join(
                'units tu',
                'tu.id = inventory_transactions.to_unit_id',
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
            $builder->where('inventory_transactions.transaction_date >=', waktu_wib_to_utc($dateFrom));
        }

        if ($dateTo) {
            $builder->where('inventory_transactions.transaction_date <=', waktu_wib_to_utc($dateTo . ' 23:59:59'));
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
                fu.name as from_unit_name,
                tu.name as to_unit_name,
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
                'units fu',
                'fu.id = inventory_transactions.from_unit_id',
                'left'
            )
            ->join(
                'units tu',
                'tu.id = inventory_transactions.to_unit_id',
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
         */
        if (has_location_restriction()) {
            $involvedLocations = array_filter([
                $transaction['from_location_id'],
                $transaction['to_location_id'],
            ]);

            if (
                empty($involvedLocations)
                || !array_intersect($involvedLocations, user_location_ids())
            ) {
                return redirect()
                    ->to('/stock-movements')
                    ->with(
                        'error',
                        'Anda tidak memiliki akses ke transaksi tersebut.'
                    );
            }
        }

        return view('stock_movements/show', [
            'title'       => 'Detail Transaksi',
            'transaction' => $transaction,
            'evidence'    => (new TransactionEvidenceModel())->where('transaction_id', $id)->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }
}
