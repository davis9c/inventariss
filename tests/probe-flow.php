<?php

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

$t = new class('probe') extends CIUnitTestCase {
    use FeatureTestTrait;

    public function boot(): void
    {
        $this->setUp();
    }

    public function getWithSession(string $path)
    {
        $this->withSession([
            'user_id'      => 1,
            'username'     => 'admin',
            'location_ids' => [],
            'name'         => 'Admin',
            'role_ids'     => [1],
            'roles'        => ['Super Admin'],
            'permissions'  => ['*'],
            'isLoggedIn'   => true,
        ]);

        return $this->get($path);
    }

    public function postWithSession(string $path, array $data)
    {
        $this->withSession([
            'user_id'      => 1,
            'username'     => 'admin',
            'location_ids' => [],
            'name'         => 'Admin',
            'role_ids'     => [1],
            'roles'        => ['Super Admin'],
            'permissions'  => ['*'],
            'isLoggedIn'   => true,
        ]);

        return $this->post($path, $data);
    }
};

$db = \Config\Database::connect('default');

$t->boot();
$r = $t->getWithSession('/categories');
echo 'GET /categories: ' . $r->response()->getStatusCode() . PHP_EOL;

$r = $t->postWithSession('/categories/store', [
    'name'        => 'PROBE-CAT-' . uniqid(),
    'description' => 'probe',
    'is_active'   => '1',
]);
echo 'POST /categories/store: ' . $r->response()->getStatusCode() . ' loc=' . $r->response()->getHeaderLine('Location') . PHP_EOL;
$cat = $db->table('categories')->like('name', 'PROBE-CAT-', 'after')->orderBy('id', 'DESC')->get()->getRowArray();
echo 'DB category: ' . var_export($cat !== null, true) . PHP_EOL;
if ($cat) {
    $db->table('categories')->delete(['id' => $cat['id']]);
}

$r = $t->postWithSession('/units/store', [
    'name'        => 'PROBE-UNIT-' . uniqid(),
    'code'        => 'PROBE-' . uniqid(),
    'description' => 'probe',
    'is_active'   => '1',
]);
echo 'POST /units/store: ' . $r->response()->getStatusCode() . ' loc=' . $r->response()->getHeaderLine('Location') . PHP_EOL;

$r = $t->postWithSession('/locations/store', [
    'name'        => 'PROBE-LOK-' . uniqid(),
    'building'    => 'Gedung X',
    'floor'       => 'L1',
    'room'        => 'R1',
    'is_active'   => '1',
]);
echo 'POST /locations/store: ' . $r->response()->getStatusCode() . ' loc=' . $r->response()->getHeaderLine('Location') . PHP_EOL;

echo '--- Roles/Users flow ---' . PHP_EOL;
$r = $t->getWithSession('/roles');
echo 'GET /roles: ' . $r->response()->getStatusCode() . PHP_EOL;
$r = $t->postWithSession('/roles/store', [
    'name'        => 'PROBE-ROLE-' . uniqid(),
    'description' => 'probe',
]);
echo 'POST /roles/store: ' . $r->response()->getStatusCode() . ' loc=' . $r->response()->getHeaderLine('Location') . PHP_EOL;

echo '--- Setup page ---' . PHP_EOL;
$t2 = new class('probe2') extends CIUnitTestCase {
    use FeatureTestTrait;

    public function boot(): void
    {
        $this->setUp();
    }
};
$t2->boot();
$r = $t2->get('/setup');
echo 'GET /setup: ' . $r->response()->getStatusCode() . PHP_EOL;
