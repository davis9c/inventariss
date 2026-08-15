<?php

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';

use CodeIgniter\Test\DatabaseTestTrait;

$db = \Config\Database::connect('default');
echo 'Connected to: ' . $db->getDatabase() . PHP_EOL;
foreach ($db->listTables() as $t) {
    echo $t . PHP_EOL;
}
echo 'users count: ' . $db->table('users')->countAllResults() . PHP_EOL;
echo 'roles count: ' . $db->table('roles')->countAllResults() . PHP_EOL;
$role = $db->table('roles')->where('name', 'Super Admin')->get()->getRowArray();
echo 'Super Admin role: ' . var_export($role, true) . PHP_EOL;
