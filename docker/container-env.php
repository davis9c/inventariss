<?php

/**
 * Menyuntikkan konfigurasi container ke $_ENV sebelum CodeIgniter membaca .env.
 *
 * Kenapa file ini ada
 * ------------------
 * Container memakai bind-mount `.:/var/www/html`, jadi /var/www/html/.env
 * adalah .env milik host -- yang menunjuk database dev di luar Docker. Kalau
 * begitu, service `db` di docker-compose.yml tidak pernah terpakai.
 *
 * Override lewat environment variable biasa tidak bisa dipakai: Docker
 * membuang nama environment variable yang mengandung titik
 * (database.default.hostname tidak pernah sampai ke proses), dan hanya
 * bentuk titik yang dibaca CodeIgniter lebih dulu -- lihat
 * Config\BaseConfig::getEnvValue().
 *
 * Solusinya: file ini dipasang sebagai auto_prepend_file, jadi berjalan
 * SEBELUM .env dibaca. CodeIgniter hanya menulis ke $_ENV kalau nilainya
 * masih kosong (Config\DotEnv::setVariable()), sehingga nilai yang kita
 * suntikkan di sini menang atas .env host.
 *
 * Nama environment variable yang dipakai sengaja TIDAK mengandung titik
 * supaya Docker mau meneruskannya: DB_HOST, DB_NAME, DB_USER, DB_PASS.
 */

/**
 * @var array<string, string> Variabel container -> kunci .env CodeIgniter
 */
$map = [
    'DB_HOST'     => 'database.default.hostname',
    'DB_NAME'     => 'database.default.database',
    'DB_USER'     => 'database.default.username',
    'DB_PASS'     => 'database.default.password',
    'APP_BASEURL' => 'app.baseURL',
    'UG_URL'      => 'usergate.url',
    'UG_API_KEY'  => 'usergate.api_key',
];

foreach ($map as $from => $to) {
    $value = getenv($from);

    if ($value === false || $value === '') {
        continue;
    }

    $_ENV[$to]    = $value;
    $_SERVER[$to] = $value;
}
