<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Verifikasi pembaruan UX: respons JSON/AJAX, protokol DataTables
 * server-side, dan jalur non-AJAX yang tetap dipertahankan.
 *
 * @internal
 */
final class UiAjaxTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;

    protected $migrateOnce = true;

    protected $namespace = null;

    protected int $adminId = 0;
    protected array $ids = [
        'users'      => [],
        'categories' => [],
        'units'      => [],
        'locations'  => [],
        'assets'     => [],
        'stock_items' => [],
        'transactions' => [],
        'opnames'    => [],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();

        $this->insert('categories', [
            'name'      => 'TEST-CAT',
            'is_active' => 1,
        ]);

        $this->insert('units', [
            'name'      => 'TEST-UNIT',
            'code'      => 'TEST-UNIT-' . uniqid(),
            'is_active' => 1,
        ]);

        $this->insert('locations', [
            'name'      => 'TEST-LOK-1',
            'building'  => 'Gedung A',
            'room'      => 'Ruang 1',
            'is_active' => 1,
        ]);

        $this->insert('locations', [
            'name'      => 'TEST-LOK-2',
            'building'  => 'Gedung B',
            'room'      => 'Ruang 2',
            'is_active' => 1,
        ]);

        $this->insert('users', [
            'username'  => 'TEST-ADMIN-' . uniqid(),
            'password'  => password_hash('secret', PASSWORD_DEFAULT),
            'name'      => 'Test Admin',
            'is_active' => 1,
        ]);

        $this->adminId = $this->ids['users'][0];

        $this->insert('assets', [
            'asset_code'       => 'TEST-ASET-1-' . uniqid(),
            'name'             => 'TEST-ASET-NAMA',
            'category_id'      => $this->ids['categories'][0],
            'unit_id'          => $this->ids['units'][0],
            'location_id'      => $this->ids['locations'][0],
            'condition_status' => 'Baik',
            'asset_status'     => 'Aktif',
        ]);

        $this->insert('assets', [
            'asset_code'       => 'TEST-ASET-2-' . uniqid(),
            'name'             => 'TEST-ASET-HAPUS',
            'category_id'      => $this->ids['categories'][0],
            'unit_id'          => $this->ids['units'][0],
            'location_id'      => $this->ids['locations'][0],
            'condition_status' => 'Baik',
            'asset_status'     => 'Aktif',
        ]);

        $this->insert('stock_items', [
            'item_code'   => 'TEST-STK-1-' . uniqid(),
            'name'        => 'TEST-STK-NAMA',
            'category_id' => $this->ids['categories'][0],
            'unit_id'     => $this->ids['units'][0],
            'location_id' => $this->ids['locations'][0],
            'satuan'      => 'pcs',
            'quantity'    => 5,
            'is_active'   => 1,
        ]);

        $this->withSession([
            'user_id'      => $this->adminId,
            'username'     => 'testadmin',
            'location_ids' => [],
            'name'         => 'Test Admin',
            'role_ids'     => [1],
            'roles'        => ['Super Admin'],
            'permissions'  => ['*'],
            'isLoggedIn'   => true,
        ]);
    }

    private function insert(string $table, array $data): int
    {
        $db = db_connect();
        $db->table($table)->insert($data);
        $id = (int) $db->insertID();
        $this->ids[$table][] = $id;

        return $id;
    }

    protected function tearDown(): void
    {
        $db = db_connect();

        if (! empty($this->ids['opnames'])) {
            $db->table('stock_opname_stock_details')
                ->whereIn('stock_opname_id', $this->ids['opnames'])
                ->delete();
            $db->table('stock_opname_details')
                ->whereIn('stock_opname_id', $this->ids['opnames'])
                ->delete();
            $db->table('stock_opnames')
                ->whereIn('id', $this->ids['opnames'])
                ->delete();
        }

        // Hapus semua transaksi yang menyinggung aset/stok test
        // (termasuk yang dibuat oleh controller saat test berjalan)
        $tx = $db->table('inventory_transactions')->groupStart();
        $txCond = false;
        if (! empty($this->ids['assets'])) {
            $tx->whereIn('asset_id', $this->ids['assets']);
            $txCond = true;
        }
        if (! empty($this->ids['stock_items'])) {
            $tx->orWhereIn('stock_item_id', $this->ids['stock_items']);
            $txCond = true;
        }
        if (! empty($this->ids['transactions'])) {
            $tx->orWhereIn('id', $this->ids['transactions']);
            $txCond = true;
        }
        if ($txCond) {
            $tx->groupEnd()->delete();
        }

        if (! empty($this->ids['assets'])) {
            $db->table('asset_mutations')
                ->whereIn('asset_id', $this->ids['assets'])
                ->delete();
            $db->table('assets')
                ->whereIn('id', $this->ids['assets'])
                ->delete();
        }

        if (! empty($this->ids['stock_items'])) {
            $db->table('stock_items')
                ->whereIn('id', $this->ids['stock_items'])
                ->delete();
        }

        if (! empty($this->ids['users'])) {
            $db->table('users')
                ->whereIn('id', $this->ids['users'])
                ->delete();
        }

        if (! empty($this->ids['locations'])) {
            $db->table('locations')
                ->whereIn('id', $this->ids['locations'])
                ->delete();
        }

        if (! empty($this->ids['units'])) {
            $db->table('units')
                ->whereIn('id', $this->ids['units'])
                ->delete();
        }

        if (! empty($this->ids['categories'])) {
            $db->table('categories')
                ->whereIn('id', $this->ids['categories'])
                ->delete();
        }

        parent::tearDown();
    }

    private function ajaxHeaders(): array
    {
        return ['X-Requested-With' => 'XMLHttpRequest'];
    }

    private function json(CodeIgniter\Test\TestResponse $result): ?array
    {
        $this->assertStatus($result, 200);
        $decoded = json_decode($result->getJSON(), true);

        $this->assertIsArray($decoded, 'Respons bukan JSON: ' . substr($result->response()->getBody(), 0, 200));

        return $decoded;
    }

    private function assertStatus(CodeIgniter\Test\TestResponse $result, int $status): void
    {
        $this->assertSame($status, $result->response()->getStatusCode(), 'Status tidak sesuai. Body: ' . substr($result->response()->getBody(), 0, 300));
    }

    /* =========================================================
     * DataTables server-side
     * ========================================================= */

    public function testAssetsIndexReturnsDataTablesJson(): void
    {
        $result = $this->get('/assets?format=json&draw=1&start=0&length=25');

        $json = $this->json($result);

        $this->assertSame(1, $json['draw']);
        $this->assertGreaterThanOrEqual(1, $json['recordsTotal']);
        $this->assertLessThanOrEqual(25, count($json['data']));
        $this->assertArrayHasKey('asset_code', $json['data'][0]);
        $this->assertArrayHasKey('id', $json['data'][0]);
    }

    public function testAssetsIndexJsonSearchFilters(): void
    {
        $result = $this->get('/assets?format=json&search[value]=TEST-ASET-NAMA');

        $json = $this->json($result);

        $this->assertGreaterThanOrEqual(1, $json['recordsFiltered']);
    }

    public function testAssetStoreAjaxSuccess(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/assets/store', [
                'asset_code'       => 'TEST-ASET-BARU-' . uniqid(),
                'name'             => 'TEST-ASET-BARU-NAMA',
                'category_id'      => $this->ids['categories'][0],
                'unit_id'          => $this->ids['units'][0],
                'location_id'      => $this->ids['locations'][0],
                'condition_status' => 'Baik',
                'asset_status'     => 'Aktif',
                'acquisition_source' => 'Pembelian',
                'acquisition_date' => date('Y-m-d'),
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertIsInt((int) $json['data']['id']);
        $this->ids['assets'][] = (int) $json['data']['id'];

        $row = db_connect()->table('assets')->where('id', $json['data']['id'])->get()->getRowArray();
        $this->assertSame('TEST-ASET-BARU-NAMA', $row['name']);
    }

    public function testAssetStoreAjaxValidationErrors(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/assets/store', [
                'asset_code' => '',
                'name'       => '',
            ]);

        $this->assertStatus($result, 422);

        $json = json_decode($result->getJSON(), true);
        $this->assertFalse($json['success']);
        $this->assertArrayHasKey('name', $json['errors']);
    }

    public function testStockItemsIndexReturnsDataTablesJson(): void
    {
        $result = $this->get('/stock-items?format=json&draw=1&start=0&length=25');

        $json = $this->json($result);

        $this->assertSame(1, $json['draw']);
        $this->assertGreaterThanOrEqual(1, $json['recordsTotal']);
        $this->assertArrayHasKey('item_code', $json['data'][0]);
        $this->assertArrayHasKey('quantity', $json['data'][0]);
    }

    public function testStockMovementsIndexReturnsDataTablesJson(): void
    {
        $db = db_connect();
        $db->table('inventory_transactions')->insert([
            'transaction_code' => 'TRX-TEST-1',
            'transaction_date' => date('Y-m-d\\TH:i'),
            'transaction_type' => 'Masuk',
            'item_type'        => 'Barang Stok',
            'stock_item_id'    => $this->ids['stock_items'][0],
            'quantity'         => 3,
            'to_location_id'   => $this->ids['locations'][0],
            'created_by'       => $this->adminId,
        ]);
        $this->ids['transactions'][] = $db->insertID();

        $result = $this->get('/stock-movements?format=json&draw=1&start=0&length=25');

        $json = $this->json($result);

        $this->assertGreaterThanOrEqual(1, $json['recordsTotal']);
        $this->assertSame(1, $json['draw']);
    }

    public function testStockMovementsJsonFilterByItemType(): void
    {
        $result = $this->get('/stock-movements?format=json&item_type=Aset');

        $json = $this->json($result);

        $this->assertIsArray($json['data']);
    }

    public function testAssetMutationsIndexReturnsDataTablesJson(): void
    {
        $db = db_connect();
        $db->table('inventory_transactions')->insert([
            'transaction_code' => 'TRX-TEST-2',
            'transaction_date' => date('Y-m-d\\TH:i'),
            'transaction_type' => 'Mutasi',
            'item_type'        => 'Aset',
            'asset_id'         => $this->ids['assets'][0],
            'quantity'         => 1,
            'from_location_id' => $this->ids['locations'][0],
            'to_location_id'   => $this->ids['locations'][1],
            'created_by'       => $this->adminId,
        ]);
        $this->ids['transactions'][] = $db->insertID();

        $result = $this->get('/asset-mutations?format=json&draw=1&start=0&length=25');

        $json = $this->json($result);

        $this->assertGreaterThanOrEqual(1, $json['recordsTotal']);
        $this->assertArrayHasKey('asset_name', $json['data'][0]);
    }

    public function testStockOpnamesIndexReturnsDataTablesJson(): void
    {
        $result = $this->post('/stock-opnames/store', [
            'opname_date' => date('Y-m-d'),
            'location_id' => $this->ids['locations'][0],
        ]);
        $this->assertStatus($result, 302);

        $opname = db_connect()->table('stock_opnames')
            ->where('location_id', $this->ids['locations'][0])
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
        $this->assertNotNull($opname);
        $this->ids['opnames'][] = $opname['id'];

        $result = $this->get('/stock-opnames?format=json&draw=1&start=0&length=25');

        $json = $this->json($result);

        $this->assertGreaterThanOrEqual(1, $json['recordsTotal']);
        $this->assertArrayHasKey('opname_code', $json['data'][0]);
    }

    /* =========================================================
     * AJAX: aset
     * ========================================================= */

    public function testAssetUpdateAjaxReturnsValidationErrors(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/assets/update/' . $this->ids['assets'][0], [
                'asset_code'  => 'TEST-ASET-EDIT',
                'name'        => '',
                'category_id' => $this->ids['categories'][0],
                'unit_id'     => $this->ids['units'][0],
                'location_id' => $this->ids['locations'][0],
            ]);

        $this->assertStatus($result, 422);

        $json = json_decode($result->getJSON(), true);
        $this->assertFalse($json['success']);
        $this->assertArrayHasKey('name', $json['errors']);
    }

    public function testAssetUpdateAjaxSuccess(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/assets/update/' . $this->ids['assets'][0], [
                'asset_code'       => 'TEST-ASET-EDIT-2-' . uniqid(),
                'name'             => 'TEST-ASET-EDIT-NAMA',
                'category_id'      => $this->ids['categories'][0],
                'unit_id'          => $this->ids['units'][0],
                'location_id'      => $this->ids['locations'][0],
                'condition_status' => 'Rusak Ringan',
                'asset_status'     => 'Dipinjam',
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);

        $row = db_connect()->table('assets')->where('id', $this->ids['assets'][0])->get()->getRowArray();
        $this->assertSame('TEST-ASET-EDIT-NAMA', $row['name']);
        $this->assertSame('Dipinjam', $row['asset_status']);
    }

    public function testAssetDeleteAjaxRejectedWhenHasHistory(): void
    {
        $db = db_connect();
        $db->table('inventory_transactions')->insert([
            'transaction_code' => 'TRX-TEST-3',
            'transaction_date' => date('Y-m-d\\TH:i'),
            'transaction_type' => 'Perolehan',
            'item_type'        => 'Aset',
            'asset_id'         => $this->ids['assets'][0],
            'quantity'         => 1,
            'to_location_id'   => $this->ids['locations'][0],
            'created_by'       => $this->adminId,
        ]);
        $this->ids['transactions'][] = $db->insertID();

        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/assets/delete/' . $this->ids['assets'][0]);

        $this->assertStatus($result, 409);

        $json = json_decode($result->getJSON(), true);
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('riwayat', $json['message']);

        $this->assertNotNull(db_connect()->table('assets')->where('id', $this->ids['assets'][0])->get()->getRow());
    }

    public function testAssetDeleteAjaxSuccess(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/assets/delete/' . $this->ids['assets'][1]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);

        $row = db_connect()->table('assets')->where('id', $this->ids['assets'][1])->get()->getRow();
        $this->assertNotNull($row);
        $this->assertNotNull($row->deleted_at);
    }

    public function testAssetMutationAjaxSuccess(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/asset-mutations/store', [
                'asset_id'     => $this->ids['assets'][0],
                'to_location_id' => $this->ids['locations'][1],
                'to_unit_id'   => $this->ids['units'][0],
                'mutation_date' => date('Y-m-d'),
                'reason'       => 'TEST mutasi',
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertSame((string) $this->ids['locations'][1], (string) $json['data']['location_id']);
        $this->assertSame('Mutasi', $json['data']['transaction']['transaction_type']);

        $row = db_connect()->table('assets')->where('id', $this->ids['assets'][0])->get()->getRowArray();
        $this->assertSame((string) $this->ids['locations'][1], (string) $row['location_id']);
    }

    public function testAssetOutAjaxSuccess(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/assets/asset-out/' . $this->ids['assets'][0], [
                'transaction_date' => date('Y-m-d\\TH:i'),
                'outbound_type'    => 'Pemindahan',
                'recipient_name'   => 'TEST PENERIMA',
                'reason'           => 'TEST keluar',
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertSame('Keluar Perusahaan', $json['data']['asset_status']);
        $this->assertSame('Keluar Perusahaan', $json['data']['transaction']['transaction_type']);

        $row = db_connect()->table('assets')->where('id', $this->ids['assets'][0])->get()->getRowArray();
        $this->assertSame('Keluar Perusahaan', $row['asset_status']);
    }

    public function testAssetReturnAjaxSuccess(): void
    {
        db_connect()->table('assets')->where('id', $this->ids['assets'][0])->update([
            'asset_status' => 'Keluar Perusahaan',
        ]);

        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/assets/asset-return/' . $this->ids['assets'][0], [
                'transaction_date' => date('Y-m-d\\TH:i'),
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertSame('Aktif', $json['data']['asset_status']);
        $this->assertSame('Pengembalian', $json['data']['transaction']['transaction_type']);

        $row = db_connect()->table('assets')->where('id', $this->ids['assets'][0])->get()->getRowArray();
        $this->assertSame('Aktif', $row['asset_status']);
    }

    /* =========================================================
     * AJAX: barang stok
     * ========================================================= */

    public function testStockItemStoreAjaxSuccess(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-items/store', [
                'item_code'   => 'TEST-STK-BARU-' . uniqid(),
                'name'        => 'TEST-STK-BARU-NAMA',
                'category_id' => $this->ids['categories'][0],
                'unit_id'     => $this->ids['units'][0],
                'location_id' => $this->ids['locations'][0],
                'satuan'      => 'box',
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertIsInt((int) $json['data']['id']);

        $row = db_connect()->table('stock_items')->where('id', $json['data']['id'])->get()->getRowArray();
        $this->assertSame('TEST-STK-BARU-NAMA', $row['name']);

        $this->ids['stock_items'][] = $row['id'];
    }

    public function testStockInAjaxSuccess(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-items/stock-in/' . $this->ids['stock_items'][0], [
                'quantity'         => 4,
                'transaction_date' => date('Y-m-d\\TH:i'),
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertSame(9, (int) $json['data']['quantity']);
        $this->assertSame('Masuk', $json['data']['transaction']['transaction_type']);

        $row = db_connect()->table('stock_items')->where('id', $this->ids['stock_items'][0])->get()->getRowArray();
        $this->assertSame(9, (int) $row['quantity']);
    }

    public function testStockOutAjaxInsufficientRejected(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-items/stock-out/' . $this->ids['stock_items'][0], [
                'quantity'         => 999,
                'transaction_date' => date('Y-m-d\\TH:i'),
                'outbound_type'    => 'Pemindahan',
                'recipient_name'   => 'TEST PENERIMA',
            ]);

        $this->assertStatus($result, 409);

        $json = json_decode($result->getJSON(), true);
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('tidak mencukupi', $json['message']);

        $row = db_connect()->table('stock_items')->where('id', $this->ids['stock_items'][0])->get()->getRowArray();
        $this->assertSame(5, (int) $row['quantity']);
    }

    public function testStockOutAjaxSuccess(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-items/stock-out/' . $this->ids['stock_items'][0], [
                'quantity'         => 2,
                'transaction_date' => date('Y-m-d\\TH:i'),
                'outbound_type'    => 'Pemindahan',
                'recipient_name'   => 'TEST PENERIMA',
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertSame(3, (int) $json['data']['quantity']);
        $this->assertSame('Keluar', $json['data']['transaction']['transaction_type']);
    }

    public function testTransferAjaxSuccess(): void
    {
        $this->insert('units', [
            'name'      => 'TEST-UNIT-2',
            'code'      => 'TEST-UNIT-2-' . uniqid(),
            'is_active' => 1,
        ]);

        $unit2 = $this->ids['units'][1];

        db_connect()->table('unit_locations')->insert([
            'unit_id'     => $unit2,
            'location_id' => $this->ids['locations'][1],
        ]);

        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-items/transfer/' . $this->ids['stock_items'][0], [
                'to_location_id'   => $this->ids['locations'][1],
                'to_unit_id'       => $unit2,
                'transaction_date' => date('Y-m-d\\TH:i'),
                'reason'           => 'TEST pindah',
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertSame((string) $this->ids['locations'][1], (string) $json['data']['location_id']);
        $this->assertSame((string) $unit2, (string) $json['data']['unit_id']);
        $this->assertSame('Pindah', $json['data']['transaction']['transaction_type']);

        $row = db_connect()->table('stock_items')->where('id', $this->ids['stock_items'][0])->get()->getRowArray();
        $this->assertSame((string) $this->ids['locations'][1], (string) $row['location_id']);
        $this->assertSame((string) $unit2, (string) $row['unit_id']);

        $tx = db_connect()->table('inventory_transactions')
            ->where('stock_item_id', $this->ids['stock_items'][0])
            ->where('transaction_type', 'Pindah')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
        $this->assertSame((string) $this->ids['units'][0], (string) $tx['from_unit_id']);
        $this->assertSame((string) $unit2, (string) $tx['to_unit_id']);
    }

    public function testAdjustmentDownAjaxSuccess(): void
    {
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-items/adjustment/' . $this->ids['stock_items'][0], [
                'adjustment_type'  => 'Turun',
                'quantity'         => 1,
                'transaction_date' => date('Y-m-d\\TH:i'),
                'reason'           => 'TEST selisih',
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertSame(4, (int) $json['data']['quantity']);
        $this->assertSame('Penyesuaian Turun', $json['data']['transaction']['transaction_type']);
    }

    /* =========================================================
     * AJAX: stock opname
     * ========================================================= */

    public function testStockOpnameCheckAndFinishAjaxFlow(): void
    {
        $result = $this->post('/stock-opnames/store', [
            'opname_date' => date('Y-m-d'),
            'location_id' => $this->ids['locations'][0],
        ]);
        $this->assertStatus($result, 302);

        $db = db_connect();
        $opname = $db->table('stock_opnames')
            ->where('location_id', $this->ids['locations'][0])
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
        $this->assertNotNull($opname);
        $this->ids['opnames'][] = $opname['id'];

        $stockDetail = $db->table('stock_opname_stock_details')
            ->where('stock_opname_id', $opname['id'])
            ->get()
            ->getRowArray();
        $this->assertNotNull($stockDetail);
        $this->assertSame(5, (int) $stockDetail['system_qty']);

        // Simpan hitung fisik via AJAX
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-opnames/stock-detail/' . $stockDetail['id'] . '/update', [
                'physical_qty' => 7,
                'notes'        => 'TEST hitung',
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertSame(7, (int) $json['data']['physical_qty']);
        $this->assertSame(2, (int) $json['data']['diff']);

        // Periksa aset via AJAX
        $assetDetail = $db->table('stock_opname_details')
            ->where('stock_opname_id', $opname['id'])
            ->where('asset_id', $this->ids['assets'][0])
            ->get()
            ->getRowArray();
        $this->assertNotNull($assetDetail);

        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-opnames/detail/' . $assetDetail['id'] . '/update', [
                'is_found'         => '1',
                'condition_status' => 'Baik',
                'notes'            => 'TEST ok',
            ]);

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertTrue((bool) $json['data']['is_found']);

        // Selesaikan opname via AJAX
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-opnames/' . $opname['id'] . '/finish');

        $json = $this->json($result);
        $this->assertTrue($json['success']);
        $this->assertSame('Selesai', $json['data']['status']);

        // Detail tidak boleh diubah setelah selesai
        $result = $this->withHeaders($this->ajaxHeaders())
            ->post('/stock-opnames/stock-detail/' . $stockDetail['id'] . '/update', [
                'physical_qty' => 8,
            ]);

        $this->assertStatus($result, 409);

        $json = json_decode($result->getJSON(), true);
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('sudah diselesaikan', $json['message']);
    }

    /* =========================================================
     * Akun tanpa role tidak memiliki akses modul
     * ========================================================= */

    public function testUserWithoutRolesBlockedFromModules(): void
    {
        $this->withSession([
            'user_id'      => $this->adminId,
            'username'     => 'userbiasa',
            'location_ids' => [],
            'name'         => 'User Biasa',
            'role_ids'     => [],
            'roles'        => [],
            'permissions'  => [],
            'isLoggedIn'   => true,
        ]);

        foreach (['/assets', '/stock-items', '/stock-opnames', '/stock-movements', '/asset-mutations'] as $url) {
            $result = $this->get($url);

            $this->assertStatus($result, 302);
            $result->assertHeader('Location', base_url('dashboard'));
        }

        $dashboard = $this->get('/dashboard');
        $this->assertStringContainsString('Belum Ada Akses', $dashboard->getBody());
    }

    public function testUserStoreWithoutRolesSucceeds(): void
    {
        $username = 'TEST-NOROLE-' . uniqid();

        $result = $this->post('/users/store', [
            'username' => $username,
            'password' => 'rahasia123',
            'name'     => 'User Tanpa Role',
        ]);

        $this->assertStatus($result, 302);
        $result->assertHeader('Location', base_url('users'));

        $db = db_connect();
        $user = $db->table('users')
            ->where('username', $username)
            ->get()
            ->getRow();

        $this->assertNotNull($user);

        $roleCount = $db->table('user_roles')
            ->where('user_id', $user->id)
            ->countAllResults();

        $this->assertSame(0, $roleCount);
    }

    /* =========================================================
     * Jalur non-AJAX tetap dipertahankan
     * ========================================================= */

    public function testNonAjaxAssetDeleteRedirects(): void
    {
        $result = $this->post('/assets/delete/' . $this->ids['assets'][1]);

        $this->assertStatus($result, 302);
        $result->assertHeader('Location', base_url('assets'));
    }

    public function testNonAjaxStockInRedirects(): void
    {
        $result = $this->post('/stock-items/stock-in/' . $this->ids['stock_items'][0], [
            'quantity'         => 1,
            'transaction_date' => date('Y-m-d\\TH:i'),
        ]);

        $this->assertStatus($result, 302);
        $result->assertHeader(
            'Location',
            base_url('stock-items/' . $this->ids['stock_items'][0])
        );
    }

    public function testNonAjaxAssetUpdateRedirectsWithErrors(): void
    {
        $result = $this->post('/assets/update/' . $this->ids['assets'][0], [
            'asset_code'  => 'TEST-ASET-X',
            'name'        => '',
        ]);

        $this->assertStatus($result, 302);
        $result->assertSessionHas('errors');
    }
}
