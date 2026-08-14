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
            '(:num)/finish',
            'StockOpname::finish/$1'
        );
    }
);


/*
|--------------------------------------------------------------------------
| Maintenance
|--------------------------------------------------------------------------
|
| Akses utama membutuhkan maintenance.view.
|
*/

$routes->group(
    'maintenances',
    [
        'filter' => [
            'auth',
            'permission:maintenance.view',
        ],
    ],
    static function ($routes) {

        $routes->get(
            '/',
            'Maintenance::index'
        );

        $routes->get(
            'create',
            'Maintenance::create',
            [
                'filter' => 'permission:maintenance.create',
            ]
        );

        $routes->post(
            'store',
            'Maintenance::store',
            [
                'filter' => 'permission:maintenance.create',
            ]
        );

        $routes->get(
            'edit/(:num)',
            'Maintenance::edit/$1',
            [
                'filter' => [
                    'auth',
                    'permission:maintenance.update',
                ],
            ]
        );

        $routes->post(
            'update/(:num)',
            'Maintenance::update/$1',
            [
                'filter' => 'permission:maintenance.update',
            ]
        );

        $routes->post(
            'delete/(:num)',
            'Maintenance::delete/$1',
            [
                'filter' => 'permission:maintenance.delete',
            ]
        );

        $routes->post(
            'approve/(:num)',
            'Maintenance::approve/$1',
            [
                'filter' => 'permission:maintenance.approve',
            ]
        );

        $routes->post(
            'reject/(:num)',
            'Maintenance::reject/$1',
            [
                'filter' => 'permission:maintenance.approve',
            ]
        );
        $routes->post(
            'start/(:num)',
            'Maintenance::start/$1',
            [
                'filter' => [
                    'auth',
                    'permission:maintenance.update',
                ],
            ]
        );

        $routes->post(
            'complete/(:num)',
            'Maintenance::complete/$1',
            [
                'filter' => [
                    'auth',
                    'permission:maintenance.update',
                ],
            ]
        );
        $routes->get(
            '(:num)',
            'Maintenance::show/$1'
        );
    }
);


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
