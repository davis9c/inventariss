<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */


/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
*/

$routes->get('/', 'Landing::index');
$routes->get('setup', 'Setup::index');
$routes->post('setup', 'Setup::create');


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->post('logout', 'Auth::logout');


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
        $routes->get(
            'units-by-location/(:num)',
            'StockItem::unitsByLocation/$1'
        );
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
        $routes->get('(:num)', 'InventoryTransaction::show/$1');
    }
);

$routes->group('attachments', ['filter' => 'auth'], static function ($routes) {
    $routes->post('document/(:segment)/(:num)', 'Attachment::storeDocument/$1/$2');
    $routes->post('photo/(:segment)/(:num)', 'Attachment::storePhoto/$1/$2');
    $routes->post('evidence/(:num)', 'Attachment::storeEvidence/$1');
    $routes->post('photo/(:num)/delete', 'Attachment::deletePhoto/$1');
    $routes->get('file/(:segment)/(:num)', 'Attachment::file/$1/$2');
});


/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
|
| Khusus Super Admin.
|
*/

$routes->group(
    'users',
    [
        'filter' => [
            'auth',
            'role:Super Admin',
        ],
    ],
    static function ($routes) {

        $routes->get('/', 'User::index');
        $routes->get('create', 'User::create');
        $routes->post('store', 'User::store');
        $routes->get('edit/(:num)', 'User::edit/$1');
        $routes->post('update/(:num)', 'User::update/$1');
        $routes->get('(:num)', 'User::show/$1');
    }
);


/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
|
| Khusus Super Admin.
|
*/

$routes->group(
    'roles',
    [
        'filter' => [
            'auth',
            'role:Super Admin',
        ],
    ],
    static function ($routes) {

        $routes->get('/', 'Role::index');
        $routes->get('create', 'Role::create');
        $routes->post('store', 'Role::store');
        $routes->get('edit/(:num)', 'Role::edit/$1');
        $routes->post('update/(:num)', 'Role::update/$1');
        $routes->post('delete/(:num)', 'Role::delete/$1');
        $routes->get('(:num)', 'Role::show/$1');
    }
);

$routes->get('reports/assets', 'Report::assets');
