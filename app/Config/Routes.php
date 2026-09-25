<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */


/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
*/

$routes->get('/', 'Setup::index');
$routes->get('setup', 'Setup::index');
$routes->post('setup', 'Setup::create');


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

$routes->get(
    'dashboard',
    'Dashboard::index',
    ['filter' => 'auth']
);


/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

$routes->group(
    'categories',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get('/', 'Category::index');
        $routes->get('create', 'Category::create');
        $routes->post('store', 'Category::store');
        $routes->get('edit/(:num)', 'Category::edit/$1');
        $routes->post('update/(:num)', 'Category::update/$1');
        $routes->post('delete/(:num)', 'Category::delete/$1');

        // Satu baris sebagai JSON, untuk mengisi modal edit dari ?edit=<id>
        $routes->get('data/(:num)', 'Category::data/$1');
    }
);


/*
|--------------------------------------------------------------------------
| Locations
|--------------------------------------------------------------------------
*/

$routes->group(
    'locations',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get('/', 'Location::index');
        $routes->get('create', 'Location::create');
        $routes->post('store', 'Location::store');
        $routes->get('edit/(:num)', 'Location::edit/$1');
        $routes->post('update/(:num)', 'Location::update/$1');
        $routes->post('delete/(:num)', 'Location::delete/$1');

        $routes->get('data/(:num)', 'Location::data/$1');

        // Detail lokasi
        $routes->get('(:num)', 'Location::show/$1');
    }
);


/*
|--------------------------------------------------------------------------
| Units
|--------------------------------------------------------------------------
*/

$routes->group(
    'units',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get('/', 'Unit::index');
        $routes->get('create', 'Unit::create');
        $routes->post('store', 'Unit::store');
        $routes->get('edit/(:num)', 'Unit::edit/$1');
        $routes->post('update/(:num)', 'Unit::update/$1');
        $routes->post('delete/(:num)', 'Unit::delete/$1');

        $routes->get('data/(:num)', 'Unit::data/$1');

        $routes->get('(:num)', 'Unit::show/$1');
    }
);


/*
|--------------------------------------------------------------------------
| Assets
|--------------------------------------------------------------------------
*/

$routes->group(
    'assets',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get('/', 'Asset::index');
        $routes->get('create', 'Asset::create');
        $routes->post('store', 'Asset::store');
        $routes->get('edit/(:num)', 'Asset::edit/$1');
        $routes->get('(:num)', 'Asset::show/$1');
        $routes->post('update/(:num)', 'Asset::update/$1');
        $routes->post('delete/(:num)', 'Asset::delete/$1');
        $routes->get('asset-out/(:num)', 'Asset::assetOut/$1');
        $routes->post('asset-out/(:num)', 'Asset::storeAssetOut/$1');
        $routes->get('asset-return/(:num)', 'Asset::assetReturn/$1');
        $routes->post('asset-return/(:num)', 'Asset::storeAssetReturn/$1');

        // Dokumen (garansi, bukti pembelian, sertifikat).
        // Berkas disimpan di writable/uploads, yaitu DI LUAR docroot,
        // jadi unduhan hanya bisa lewat downloadDocument() yang memeriksa
        // auth + can_access_location.
        $routes->get('documents/(:num)', 'Asset::downloadDocument/$1');
        $routes->post('(:num)/documents', 'Asset::uploadDocuments/$1');
        $routes->post('documents/(:num)/delete', 'Asset::deleteDocument/$1');

        // Gambar barang. Logikanya diberikan trait HandlesItemImages,
        // sama persis dengan dokumen: berkas di luar docroot, unduhan lewat
        // controller, dan setiap endpoint memeriksa can_access_location().
        $routes->get('images/(:num)', 'Asset::downloadItemImage/$1');
        $routes->post('(:num)/images', 'Asset::uploadItemImage/$1');
        $routes->post('images/(:num)/delete', 'Asset::deleteItemImage/$1');
    }
);


/*
|--------------------------------------------------------------------------
| Asset Mutations
|--------------------------------------------------------------------------
*/

$routes->group(
    'asset-mutations',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get('/', 'AssetMutation::index');
        $routes->get('create', 'AssetMutation::create');
        $routes->post('store', 'AssetMutation::store');
        $routes->get(
            'units-by-location/(:num)',
            'AssetMutation::unitsByLocation/$1'
        );
    }
);


/*
|--------------------------------------------------------------------------
| Stock Items
|--------------------------------------------------------------------------
*/

$routes->group(
    'stock-items',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get('/', 'StockItem::index');
        $routes->get('create', 'StockItem::create');
        $routes->post('store', 'StockItem::store');
        $routes->get('edit/(:num)', 'StockItem::edit/$1');
        $routes->post('update/(:num)', 'StockItem::update/$1');
        $routes->get('stock-in/(:num)', 'StockItem::stockIn/$1');
        $routes->post('stock-in/(:num)', 'StockItem::storeStockIn/$1');
        $routes->get('stock-out/(:num)', 'StockItem::stockOut/$1');
        $routes->post('stock-out/(:num)', 'StockItem::storeStockOut/$1');
        $routes->get('transfer/(:num)', 'StockItem::transfer/$1');
        $routes->post('transfer/(:num)', 'StockItem::storeTransfer/$1');
        $routes->get('adjustment/(:num)', 'StockItem::adjustment/$1');
        $routes->post('adjustment/(:num)', 'StockItem::storeAdjustment/$1');

        // Gambar barang. Logikanya diberikan trait HandlesItemImages,
        // sama persis dengan gambar aset.
        $routes->get('images/(:num)', 'StockItem::downloadItemImage/$1');
        $routes->post('(:num)/images', 'StockItem::uploadItemImage/$1');
        $routes->post('images/(:num)/delete', 'StockItem::deleteItemImage/$1');

        $routes->get('(:num)', 'StockItem::show/$1');
    }
);


/*
|--------------------------------------------------------------------------
| Stock Opname
|--------------------------------------------------------------------------
*/

$routes->group(
    'stock-opnames',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get('/', 'StockOpname::index');
        $routes->get('create', 'StockOpname::create');
        $routes->post('store', 'StockOpname::store');
        $routes->get('(:num)', 'StockOpname::show/$1');

        $routes->post(
            'detail/(:num)/update',
            'StockOpname::updateDetail/$1'
        );

        $routes->post(
            'stock-detail/(:num)/update',
            'StockOpname::updateStockDetail/$1'
        );

        $routes->post(
            '(:num)/finish',
            'StockOpname::finish/$1'
        );
    }
);


/*
|--------------------------------------------------------------------------
| Stock Movement
|--------------------------------------------------------------------------
*/

$routes->group(
    'stock-movements',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get('/', 'InventoryTransaction::index');
        $routes->get('data/(:num)', 'InventoryTransaction::data/$1');

        // Lampiran catatan pergerakan: dokumen (surat jalan, nota, berita
        // acara) dan gambar (bukti kondisi barang).
        //
        // Logikanya diberikan trait HandlesItemImages, sama persis dengan
        // lampiran barang. Bedanya hanya aturan hak akses: transaksi tidak
        // punya satu lokasi tunggal, jadi dipakai can_access_transaction().
        $routes->get('documents/(:num)', 'InventoryTransaction::downloadItemDocument/$1');
        $routes->post('(:num)/documents', 'InventoryTransaction::uploadItemDocument/$1');
        $routes->post('documents/(:num)/delete', 'InventoryTransaction::deleteItemDocument/$1');
        $routes->get('images/(:num)', 'InventoryTransaction::downloadItemImage/$1');
        $routes->post('(:num)/images', 'InventoryTransaction::uploadItemImage/$1');
        $routes->post('images/(:num)/delete', 'InventoryTransaction::deleteItemImage/$1');

        $routes->get('(:num)', 'InventoryTransaction::show/$1');
    }
);


/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
|
| Hanya Admin Inventaris (dan Super Admin).
|
*/

$routes->group(
    'users',
    [
        'filter' => [
            'auth',
            'role:Admin Inventaris,Super Admin',
        ],
    ],
    static function ($routes) {

        $routes->get('/', 'User::index');
        $routes->get('create', 'User::create');
        $routes->post('store', 'User::store');
        $routes->get('data/(:num)', 'User::data/$1');
        $routes->get('(:num)', 'User::show/$1');
        $routes->get('edit/(:num)', 'User::edit/$1');
        $routes->post('update/(:num)', 'User::update/$1');
        $routes->post('delete/(:num)', 'User::delete/$1');

        // Admin Inventaris BOLEH menetapkan role, tapi tidak boleh
        // memberikan atau mencabut role Super Admin. Batas itu ditegakkan
        // di dalam User::updateRoles(), bukan di sini — jadi jangan
        // andalkan filter route saja untuk aturan Super Admin.
        $routes->post('(:num)/roles', 'User::updateRoles/$1');
        $routes->post('(:num)/locations', 'User::updateLocations/$1');
    }
);


/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
|
| TIDAK ADA route /roles. Role dikelola statis lewat DatabaseSeeder (lihat
| konstanta ROLES di class itu) — tidak ada CRUD role di aplikasi.
| Tabel `roles` tetap dipakai User::updateRoles() untuk memvalidasi role_id
| dan menghitung hierarchy level.
|
| Kolom `roles.capabilities` berisi uraian fitur per role (niat, bukan
| enforcement). Halaman yang menampilkannya: /help.
|
*/

/*
|--------------------------------------------------------------------------
| Help / Panduan Akses
|--------------------------------------------------------------------------
|
| Dokumentasi hak akses. Terbuka untuk semua user yang sudah login —
| isinya bukan rahasia, dan justru lebih berguna kalau yang impacted
| bisa membacanya.
|
*/

$routes->group(
    'help',
    ['filter' => 'auth'],
    static function ($routes) {
        $routes->get('/', 'Help::roles');
        $routes->get('dokumen', 'Help::documents');
    }
);


/*
| Reports
|--------------------------------------------------------------------------
|
| CATATAN: route ini berada di luar group, jadi TIDAK punya filter apa pun
| — termasuk `auth`. Artinya /reports/assets bisa dibuka tanpa login dan
| menampilkan seluruh inventaris aset. Lihat /help/roles bagian "Catatan
| Keamanan". Belum diperbaiki karena penentuan role mana yang boleh
| accessing-nya masih pending.
|
*/

$routes->get('reports/assets', 'Report::assets');
