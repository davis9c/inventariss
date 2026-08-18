<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Inventaris') ?></title>

    <link href="<?= base_url('vendor/bootstrap.min.css') ?>" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }
        .hero {
            background: linear-gradient(135deg, #0d1b2a 0%, #1b2a41 60%, #25355a 100%);
            color: #fff;
            padding: 4rem 0;
        }
        .hero h1 {
            font-weight: 700;
        }
        .feature-card {
            border: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
        }
        .feature-icon {
            font-size: 1.5rem;
        }
        .footer-note {
            color: #6c757d;
            font-size: .875rem;
        }
    </style>
</head>

<body>

    <?php
    $ctaLabel = 'Mulai Instalasi';
    $ctaUrl   = base_url('setup');
    $ctaClass = 'btn-success';

    if ($logged_in) {
        $ctaLabel = 'Buka Dashboard';
        $ctaUrl   = base_url('dashboard');
        $ctaClass = 'btn-primary';
    } elseif ($installed) {
        $ctaLabel = 'Masuk';
        $ctaUrl   = base_url('login');
        $ctaClass = 'btn-primary';
    }
    ?>

    <nav class="navbar navbar-expand navbar-dark" style="background: #0d1b2a;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url('/') ?>">INVENTARIS</a>
            <div class="ms-auto">
                <a href="<?= $ctaUrl ?>" class="btn <?= $ctaClass ?> btn-sm px-3">
                    <?= $ctaLabel ?>
                </a>
            </div>
        </div>
    </nav>

    <section class="hero text-center">
        <div class="container">
            <h1 class="mb-3">INVENTARIS</h1>
            <p class="lead mb-4 mx-auto" style="max-width: 640px;">
                Sistem pengelolaan aset, stok, mutasi, dan stock opname
                dalam satu aplikasi yang mudah digunakan.
            </p>

            <a href="<?= $ctaUrl ?>" class="btn <?= $ctaClass ?> btn-lg px-4">
                <?= $ctaLabel ?>
            </a>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <div class="feature-icon mb-2">📦</div>
                            <h5 class="card-title">Aset &amp; Barang Stok</h5>
                            <p class="card-text text-muted mb-0">
                                Catat aset tetap dan barang stok dengan kategori, unit,
                                lokasi, tahun perolehan, dan harga.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <div class="feature-icon mb-2">🔁</div>
                            <h5 class="card-title">Mutasi &amp; Riwayat</h5>
                            <p class="card-text text-muted mb-0">
                                Kelola pemindahan, peminjaman, barang keluar, pengembalian,
                                dan stok masuk-keluar dengan riwayat lengkap.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <div class="feature-icon mb-2">✅</div>
                            <h5 class="card-title">Stock Opname</h5>
                            <p class="card-text text-muted mb-0">
                                Periksa aset dan stok berkala, catat temuan, dan
                                hasilkan selisih secara otomatis.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <div class="feature-icon mb-2">📊</div>
                            <h5 class="card-title">Laporan</h5>
                            <p class="card-text text-muted mb-0">
                                Ringkasan aset, stok, mutasi, dan opname dalam tampilan
                                tabel yang mudah difilter.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <div class="feature-icon mb-2">🛡️</div>
                            <h5 class="card-title">Audit Trail</h5>
                            <p class="card-text text-muted mb-0">
                                Setiap perubahan tercatat otomatis: siapa, kapan, dan
                                apa yang berubah.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <div class="feature-icon mb-2">👥</div>
                            <h5 class="card-title">Multi-Role</h5>
                            <p class="card-text text-muted mb-0">
                                Super Admin, Admin Inventaris, Petugas, PIC Unit,
                                Manajemen, dan Auditor dengan akses berbeda.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <footer class="text-center pb-4 footer-note">
        <div class="container">
            Sistem Inventaris &copy; <?= date('Y') ?>
            <?php if (!$installed): ?>
                &middot; <a href="<?= base_url('setup') ?>">Instalasi</a>
            <?php endif; ?>
        </div>
    </footer>

</body>
</html>
