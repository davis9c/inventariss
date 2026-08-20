<?= view('layout/header', ['title' => 'Dashboard']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="mb-4">
        <h3>Dashboard Inventaris</h3>

        <p class="text-muted mb-0">
            Ringkasan kondisi dan jumlah barang inventaris.
        </p>
    </div>

    <?php if (! $hasAccess): ?>

        <div class="alert alert-info border-0 shadow-sm" role="alert">
            <h5 class="alert-heading mb-2">
                Belum Ada Akses
            </h5>

            <p class="mb-0">
                Akun Anda belum memiliki akses ke modul apapun.
                Silakan hubungi <strong>Administrator Inventaris</strong>
                untuk meminta akses.
            </p>
        </div>

    <?php else: ?>

    <!-- Statistik Utama -->
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Aset</h6>
                    <h2><?= esc($totalAssets) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Aset Digunakan</h6>
                    <h2><?= esc($usedAssets) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Tidak Digunakan</h6>
                    <h2><?= esc($unusedAssets) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Lokasi</h6>
                    <h2><?= esc($totalLocations) ?></h2>
                </div>
            </div>
        </div>

    </div>


    <!-- Kondisi Aset -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <h6 class="text-muted">
                        Kondisi Baik
                    </h6>

                    <h2>
                        <?= esc($goodAssets) ?>
                    </h2>

                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <h6 class="text-muted">
                        Rusak Ringan
                    </h6>

                    <h2>
                        <?= esc($lightDamageAssets) ?>
                    </h2>

                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <h6 class="text-muted">
                        Rusak Berat
                    </h6>

                    <h2>
                        <?= esc($heavyDamageAssets) ?>
                    </h2>

                </div>
            </div>
        </div>

    </div>


    <!-- Informasi Tambahan -->
    <div class="card shadow-sm">

        <div class="card-body">

            <h5 class="mb-3">
                Informasi Inventaris
            </h5>

            <div class="row">

                <div class="col-md-6">

                    <p class="mb-2">
                        <strong>Total Aset:</strong>
                        <?= esc($totalAssets) ?>
                    </p>

                    <p class="mb-2">
                        <strong>Digunakan:</strong>
                        <?= esc($usedAssets) ?>
                    </p>

                    <p class="mb-2">
                        <strong>Tidak Digunakan:</strong>
                        <?= esc($unusedAssets) ?>
                    </p>

                </div>

                <div class="col-md-6">

                    <p class="mb-2">
                        <strong>Lokasi Aktif:</strong>
                        <?= esc($totalLocations) ?>
                    </p>

                    <p class="mb-2">
                        <strong>Kategori Aktif:</strong>
                        <?= esc($totalCategories) ?>
                    </p>

                </div>

            </div>

        </div>

    </div>

    <?php endif; ?>

</div>

<?= view('layout/footer') ?>