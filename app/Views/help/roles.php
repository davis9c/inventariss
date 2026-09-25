<?= view('layout/header', ['title' => 'Panduan Akses']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

    <h3>Panduan Akses</h3>
    <p class="text-muted">
        Role apa saja yang ada, dan apa yang benar-benar bisa diakses masing-masing.
        Tabel di halaman ini digenerate dari konfigurasi route dan data role,
        jadi tidak perlu disunting manual.
    </p>

    <!-- ── Role Anda sendiri ─────────────────────────────────── -->
    <div class="alert alert-info mt-4" role="alert">
        <h6 class="alert-heading mb-2">Anda login sebagai</h6>
        <?php if (empty($myRoles)): ?>
            <p class="mb-0">
                Akun ini belum memiliki role. Hubungi Super Admin.
            </p>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <?php foreach ($myRoles as $r): ?>
                    <span class="badge text-bg-primary"><?= esc($r) ?></span>
                <?php endforeach; ?>
            </div>
            <p class="mb-0 small">
                Lihat tabel di bawah untuk batas yang benar-benar
                ditegakkan sistem.
            </p>
        <?php endif; ?>
    </div>

    <div class="alert alert-warning mt-3" role="alert">
        <h6 class="alert-heading mb-2">Bacaan penting</h6>
        <p class="mb-0">
            Menu di navbar disembunyikan per role, tapi itu <strong>hanya tampilan</strong>.
            Route-nya sendiri hanya memakai filter <code>auth</code> untuk hampir
            semua modul — sehingga menu yang tersembunyi tetap bisa dibuka dengan
            mengetik URL-nya. Tabel "Menu vs yang bisa diakses" di bawah
            menunjukkan selisihnya.
        </p>
    </div>

    <!-- ── Daftar role ────────────────────────────────────────── -->
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <strong>Daftar Role &amp; Fitur</strong>
            <span class="badge bg-secondary float-end"><?= count($allRoles) ?> role</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Level</th>
                            <th>Fitur yang ditujukan</th>
                            <th>Anda?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allRoles as $r): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?= esc($r['name']) ?>
                                    <?php if (! empty($r['description'])): ?>
                                        <div class="text-muted small fw-normal">
                                            <?= esc($r['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= (int) $r['level'] ?></span>
                                </td>
                                <td>
                                    <?= esc($r['capabilities'] ?? '-') ?>
                                </td>
                                <td>
                                    <?php if (in_array($r['name'], $myRoles, true)): ?>
                                        <span class="badge text-bg-success">Ya</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mb-0 mt-2">
                Kolom <em>Fitur yang ditujukan</em> berasal dari
                <code>DatabaseSeeder</code> (kolom
                <code>roles.capabilities</code>) dan menyatakan
                <strong>niat</strong> untuk role tersebut — bukan jaminan
                bahwa filter akses sudah menegakkannya. Bandingkan dengan
                tabel "Menu vs yang Bisa Diakses" di bawah. Level 1 =
                privilege tertinggi; level dipakai untuk membatasi role mana
                yang boleh diberikan ke user lain, bukan untuk membatasi fitur.
            </p>
        </div>
    </div>

    <!-- ── Matriks akses route (dinamis) ──────────────────────── -->
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <strong>Matriks Akses Route</strong>
            <span class="badge bg-secondary float-end"><?= count($accessMap) ?> modul</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Modul</th>
                            <th>Verifikasi</th>
                            <th>Filter</th>
                            <th>Role yang wajib</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accessMap as $row): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url($row['modul']) ?>">
                                        <?= esc($row['modul']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php foreach ($row['verbs'] as $v): ?>
                                        <span class="badge bg-light text-dark"><?= esc(strtoupper($v)) ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php if (empty($row['filters'])): ?>
                                        <span class="badge text-bg-danger">tidak ada filter</span>
                                    <?php else: ?>
                                        <code><?= esc(implode(' + ', $row['filters'])) ?></code>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($row['roles'])): ?>
                                        <span class="badge text-bg-secondary">semua role</span>
                                    <?php else: ?>
                                        <?php foreach ($row['roles'] as $r): ?>
                                            <span class="badge text-bg-primary"><?= esc($r) ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Menu vs yang bisa diakses ─────────────────────────── -->
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <strong>Menu vs yang Bisa Diakses</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Menu navbar yang tampil</th>
                            <th>Yang benar-benar bisa diakses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold">Super Admin</td>
                            <td>Semua</td>
                            <td>Semua</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Admin Inventaris</td>
                            <td>Inventaris, Master Data, User Management</td>
                            <td>Sama dengan menunya</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Petugas Inventaris</td>
                            <td>Inventaris, Master Data</td>
                            <td>Sama dengan menunya</td>
                        </tr>
                        <tr class="table-warning">
                            <td class="fw-semibold">Manajemen</td>
                            <td>Laporan</td>
                            <td>
                                Inventaris + Master Data + <strong>CRUD penuh</strong>
                            </td>
                        </tr>
                        <tr class="table-warning">
                            <td class="fw-semibold">Auditor</td>
                            <td>Laporan</td>
                            <td>
                                Inventaris + Master Data + <strong>CRUD penuh</strong>
                            </td>
                        </tr>
                        <tr class="table-danger">
                            <td class="fw-semibold">PIC Unit</td>
                            <td><em>(tidak ada menu)</em></td>
                            <td>
                                Inventaris + Master Data + <strong>CRUD penuh</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-danger mt-3 mb-0" role="alert">
                Konsekuensi praktis:
                <ul class="mb-0">
                    <li>Manajemen dan Auditor <strong>bukan read-only</strong> — keduanya bisa menghapus kategori, lokasi, unit, dan aset.</li>
                    <li>PIC Unit <strong>tidak punya pembatas apa pun</strong>. Menunya kosong, tetapi URL inventaris dan master data tetap bisa dibuka langsung, termasuk endpoint hapus.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- ── Batasan lokasi ────────────────────────────────────── -->
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <strong>Batasan Lokasi</strong>
            <span class="badge bg-secondary float-end">terpisah dari role</span>
        </div>
        <div class="card-body">
            <p>
                Selain role, ada pembatasan per-user berdasarkan lokasi lewat
                <code>can_access_location()</code>. Berlaku pada modul
                <code>assets</code>, <code>stock-items</code>,
                <code>stock-opnames</code>, dan <code>stock-movements</code>.
            </p>
            <ul>
                <li><strong>Super Admin</strong> — semua lokasi</li>
                <li><strong>User tanpa baris di <code>user_locations</code></strong> — semua lokasi</li>
                <li><strong>User dengan baris di <code>user_locations</code></strong> — hanya lokasi tersebut</li>
            </ul>
            <p class="text-muted small mb-0">
                Diisi dari menu User Management → Edit → Lokasi yang Dapat Diakses.
            </p>
        </div>
    </div>

    <!-- ── Catatan keamanan ──────────────────────────────────── -->
    <div class="card shadow-sm mt-4 mb-4">
        <div class="card-header">
            <strong>Catatan Keamanan</strong>
        </div>
        <div class="card-body">

            <h6 class="text-danger">1. <code>/reports/assets</code> tidak punya filter</h6>
            <p class="mb-2">
                Route ini berada di luar group route, jadi tidak memakai
                <code>auth</code> maupun <code>role:</code>. Halaman tersebut
                bisa dibuka <strong>tanpa login</strong> dan menampilkan
                seluruh inventaris aset: kode, nama, kategori, unit, lokasi,
                kondisi, dan status.
            </p>
            <p class="text-muted small">
                Daftar route tanpa filter dihitung otomatis oleh
                <code>Help::unfilteredRoutes()</code>:
            </p>
            <ul class="mb-3">
                <?php if (empty($unfiltered)): ?>
                    <li><em>Tidak ada route yang tanpa filter.</em></li>
                <?php else: ?>
                    <?php foreach ($unfiltered as $r): ?>
                        <li><code><?= esc($r) ?></code></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <h6 class="text-danger">2. Fitur "approval" belum ada</h6>
            <p class="mb-3">
                Deskripsi role Manajemen menyebut "melakukan approval", tetapi
                tidak ada alur persetujuan di aplikasi. Tidak ada controller,
                route, maupun model yang menangani persetujuan; kolom
                <code>approved_by</code>, <code>approved_at</code>, dan
                <code>approval_notes</code> ikut terhapus bersama tabel
                <code>maintenances</code>, yang tidak pernah dibangun.
            </p>

            <h6 class="text-danger">3. Master data tanpa validasi</h6>
            <p class="mb-3">
                <code>CategoryModel</code>, <code>UnitModel</code>, dan
                <code>LocationModel</code> tidak punya aturan validasi, sehingga
                nama kosong maupun duplikat tetap diterima. Dikombinasikan
                dengan celah role di atas, role yang punya endpoint CRUD pun
                bisa membuat master data tanpa batasan.
            </p>

            <h6 class="text-danger">4. CSRF belum diaktifkan</h6>
            <p class="mb-0">
                Filter global <code>csrf</code> pada
                <code>app/Config/Filters.php</code> masih dikomentari, sehingga
                seluruh <code>csrf_field()</code> di form tidak diverifikasi.
                Mengaktifkannya perlu penambahan token di form yang belum
                memilikinya lebih dulu.
            </p>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>
