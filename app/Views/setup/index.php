<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Setup Sistem') ?></title>

    <link href="<?= base_url('vendor/bootstrap.min.css') ?>" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">

                    <h4 class="mb-1">Setup Sistem</h4>
                    <p class="text-muted mb-4">
                        Instalasi otomatis: struktur database, data awal, dan akun Super Admin.
                    </p>

                    <div class="mb-4">

                        <h6 class="text-uppercase text-secondary mb-2">
                            <span class="badge bg-primary me-1">1</span> Database &amp; Struktur
                        </h6>

                        <ul class="list-unstyled mb-0">

                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Koneksi database</span>
                                <?php if ($dbConnected): ?>
                                    <span class="badge bg-success">Terhubung</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Gagal</span>
                                <?php endif; ?>
                            </li>

                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Host</span>
                                <span class="fw-semibold"><?= esc($hostname ?: '-') ?></span>
                            </li>

                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Database</span>
                                <span class="fw-semibold"><?= esc($database ?: '-') ?></span>
                            </li>

                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Struktur tabel</span>
                                <?php if (!$dbConnected): ?>
                                    <span class="badge bg-secondary">Belum tersedia</span>
                                <?php elseif ($tablesExist): ?>
                                    <span class="badge bg-success">Sudah ada</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Akan dibuat otomatis</span>
                                <?php endif; ?>
                            </li>

                        </ul>

                        <?php if (!$dbConnected): ?>
                            <div class="alert alert-warning mt-3 mb-0 py-2 small">
                                Koneksi database gagal. Periksa konfigurasi di file
                                <code>.env</code> (database.default.hostname / database / username / password).
                            </div>
                        <?php endif; ?>

                    </div>

                    <hr>

                    <h6 class="text-uppercase text-secondary mb-3">
                        <span class="badge bg-primary me-1">2</span> Akun Super Admin
                    </h6>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger py-2">
                            <?= esc(session()->getFlashdata('error')) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= base_url('setup') ?>">

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text"
                                   name="username"
                                   class="form-control"
                                   value="<?= esc(old('username', 'admin')) ?>"
                                   maxlength="50"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   minlength="8"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password"
                                   name="password_confirm"
                                   class="form-control"
                                   minlength="8"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Selesai &amp; Buat Super Admin
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>