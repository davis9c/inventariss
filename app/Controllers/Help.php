<?php

namespace App\Controllers;

use App\Models\RoleModel;
use CodeIgniter\Router\RouteCollection;
use Config\AssetDocuments;
use Config\ItemImages;

class Help extends BaseController
{
    /**
     * Route yang bukan fitur, jadi tidak ditampilkan sebagai baris di
     * tabel hak akses.
     */
    private const NON_FEATURE_ROUTES = ['login', 'logout', 'setup', 'help', ''];

    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    /**
     * Halaman panduan hak akses.
     *
     * Tabel akses digenerate dari konfigurasi route sungguhan, bukan
     * ditulis tangan, sehingga tidak cepat basi ketika filter atau role
     * berubah.
     */
    public function roles()
    {
        return view('help/roles', [
            'title'      => 'Panduan Akses',
            'allRoles'   => $this->roleModel->orderBy('level', 'ASC')->findAll(),
            'myRoles'    => session()->get('roles') ?? [],
            'accessMap'  => $this->buildAccessMap(),
            'unfiltered' => $this->unfilteredRoutes(),
        ]);
    }

    /**
     * Panduan dokumen aset (garansi, bukti pembelian, sertifikat).
     *
     * Nilainya diambil dari Config\AssetDocuments, bukan ditulis tangan,
     * jadi halaman ini ikut berubah kalau batas atau daftar tipe diubah.
     * Batas server PHP juga ditampilkan karena sering menjadi penyebab
     * unggahan ditolak padahal berkasnya masih di bawah batas aplikasi.
     */
    public function documents()
    {
        $cfg    = new AssetDocuments();
        $bytes  = (int) $cfg->maxSizeBytes;
        $images = new ItemImages();

        return view('help/documents', [
            'title'        => 'Panduan Dokumen & Gambar',
            'allowedMimes' => $cfg->allowedMimes,
            'allowedExts'  => $cfg->allowedExtensions,
            'maxSizeBytes' => $bytes,
            'maxSizeLabel' => document_size_label($bytes),
            'maxFiles'     => (int) $cfg->maxFiles,
            'directory'    => $cfg->directory,

            // Batas PHP inilah yang sebenarnya menegakkan upload. Kalau
            // lebih kecil daripada batas aplikasi, PHP membuang berkas
            // sebelum kode aplikasi sempat berjalan.
            'serverUploadLimit' => ini_get('upload_max_filesize'),
            'serverPostLimit'   => ini_get('post_max_size'),

            // Gambar punya aturan sendiri: jenis lebih sempit, batas lebih
            // kecil, dan dikecilkan di browser sebelum dikirim.
            'imgMimes'         => $images->allowedMimes,
            'imgExts'          => $images->allowedExtensions,
            'imgMaxSizeLabel'  => document_size_label($images->maxSizeBytes),
            'imgMaxOriginal'   => document_size_label($images->maxOriginalBytes),
            'imgMaxImages'     => (int) $images->maxImages,
            'imgMaxDimension'  => (int) $images->maxDimension,
            'imgDirectory'     => $images->directory,
        ]);
    }

    /**
     * Kelompokkan route berdasarkan segmen pertama, lalu ambil filter
     * efektifnya dari RouteCollection.
     *
     * Dipisah per HTTP verb karena satu modul bisa punya aturan berbeda
     * antara GET (baca) dan POST (tulis).
     *
     * @return array<string, array{modul:string, verbs:array, filters:array, roles:array}>
     */
    private function buildAccessMap(): array
    {
        $routes = $this->routeCollection();
        $groups = [];

        foreach (['GET', 'POST'] as $verb) {
            foreach (array_keys($routes->getRoutes($verb)) as $rawKey) {
                // Kunci route disimpan persis seperti ditulis di Routes.php,
                // jadi jangan di-trim sebelum dipakai untuk lookup option.
                $key = trim((string) $rawKey, '/ ');

                $module = $key === '' ? '' : explode('/', $key)[0];

                if ($this->isInternalRoute($module)) {
                    continue;
                }

                $filter = $routes->getRoutesOptions($rawKey, $verb)['filter'] ?? [];
                $filter = is_array($filter) ? $filter : [$filter];

                // Verbmodul tetap dicatat walau route-nya tanpa filter —
                // justru itulah yang perlu terlihat di tabel. Hanya
                // signature filter yang tidak disimpan kalau kosong.
                $groups[$module]['verbs'][$verb] = true;

                // Buang entri kosong/null supaya tidak tersimpan sebagai ''
                // dan tidak terlihat seperti "filter ada tapi kosong".
                $filter = array_values(array_filter(
                    $filter,
                    static fn ($f) => $f !== '' && $f !== null
                ));

                if ($filter === []) {
                    continue;
                }

                $signature = implode(' + ', array_map(
                    static fn ($f) => is_string($f) ? $f : json_encode($f),
                    $filter
                ));

                $groups[$module]['filters'][$signature] = true;

                // Role wajib diambil dari elemen filter MENTAH, bukan dari
                // signature. Nama role mengandung spasi ("Super Admin"),
                // jadi tidak boleh dipecah dengan regex pada teks gabungan.
                foreach ($filter as $f) {
                    if (is_string($f) && str_starts_with($f, 'role:')) {
                        foreach (explode(',', substr($f, strlen('role:'))) as $role) {
                            $role = trim($role);
                            if ($role !== '') {
                                $groups[$module]['roles'][$role] = true;
                            }
                        }
                    }
                }
            }
        }

        $map = [];

        foreach ($groups as $module => $data) {
            $signatures = array_keys($data['filters'] ?? []);
            sort($signatures);

            $map[$module] = [
                'modul'   => $module,
                'verbs'   => array_keys($data['verbs'] ?? []),
                'filters' => $signatures,
                'roles'   => array_keys($data['roles'] ?? []),
            ];
        }

        ksort($map);

        return $map;
    }

    /**
     * Route yang TIDAK punya filter sama sekali, sehingga bisa diakses
     * tanpa login. Dihitung dari config, bukan daftar manual.
     *
     * @return array<int, string>
     */
    private function unfilteredRoutes(): array
    {
        $routes  = $this->routeCollection();
        $missing = [];

        foreach (['GET', 'POST'] as $verb) {
            foreach (array_keys($routes->getRoutes($verb)) as $rawKey) {
                $key = trim((string) $rawKey, '/ ');

                $module = $key === '' ? '' : explode('/', $key)[0];

                if ($this->isInternalRoute($module)) {
                    continue;
                }

                if (! $routes->isFiltered($rawKey, $verb)) {
                    $missing[] = $verb . ' /' . $key;
                }
            }
        }

        return array_values(array_unique($missing));
    }

    /**
     * Route yang tidak relevan ditampilkan sebagai "fitur": halaman auth,
     * wizard setup, dan route internal CI4 (prefix `__`,misalnya
     * `__hot-reload` yang hanya ada di mode development).
     */
    private function isInternalRoute(string $module): bool
    {
        return in_array($module, self::NON_FEATURE_ROUTES, true)
            || str_starts_with($module, '__');
    }

    /**
     * Koleksi route.
     *
     * Catatan penting: RouteCollection menyimpan route dengan kunci verb
     * HURUF BESAR ('GET', 'POST'), sehingga getRoutes('get') selalu kosong
     * walau koleksinya sudah terisi. Semua pemanggilan di kelas ini karena
     * itu memakai 'GET'/'POST'.
     */
    private function routeCollection(): RouteCollection
    {
        return service('routes');
    }
}
