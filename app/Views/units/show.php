<?= view('layout/header', ['title' => 'Detail Unit']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Detail Unit</h3>
            <p class="text-muted mb-0">
                Informasi unit / departemen
            </p>
        </div>

        <div>
            <a href="<?= base_url('units') ?>"
                class="btn btn-secondary">
                Kembali
            </a>

            <a href="<?= base_url('units/edit/' . $unit['id']) ?>"
                class="btn btn-warning">
                Edit
            </a>
        </div>
    </div>


    <!-- Informasi Unit -->
    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Informasi Unit</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="text-muted">
                        Nama Unit
                    </label>

                    <div class="fw-semibold">
                        <?= esc($unit['name']) ?>
                    </div>
                </div>


                <div class="col-md-6 mb-3">
                    <label class="text-muted">
                        Kode
                    </label>

                    <div class="fw-semibold">
                        <?= esc($unit['code'] ?? '-') ?>
                    </div>
                </div>


                <div class="col-md-6 mb-3">
                    <label class="text-muted">
                        Status
                    </label>

                    <div>
                        <?php if ($unit['is_active']): ?>

                            <span class="badge bg-success">
                                Aktif
                            </span>

                        <?php else: ?>

                            <span class="badge bg-secondary">
                                Tidak Aktif
                            </span>

                        <?php endif; ?>
                    </div>
                </div>


                <div class="col-md-12 mb-3">
                    <label class="text-muted">
                        Deskripsi
                    </label>

                    <div>
                        <?= nl2br(esc($unit['description'] ?? '-')) ?>
                    </div>
                </div>

            </div>

        </div>
    </div>


    <!-- Lokasi -->
    <div class="card shadow-sm mb-4">

        <div class="card-header d-flex justify-content-between">
            <strong>Lokasi Unit</strong>

            <span class="badge bg-primary">
                <?= count($locations) ?> lokasi
            </span>
        </div>

        <div class="card-body">

            <?php if (empty($locations)): ?>

                <div class="text-muted">
                    Belum ada lokasi yang terkait dengan unit ini.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Lokasi</th>
                                <th>Gedung</th>
                                <th>Lantai</th>
                                <th>Ruangan</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($locations as $index => $location): ?>

                                <tr>

                                    <td>
                                        <?= $index + 1 ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= esc($location['name']) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= esc($location['building'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= esc($location['floor'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= esc($location['room'] ?? '-') ?>
                                    </td>

                                    <td>

                                        <?php if ($location['is_active']): ?>

                                            <span class="badge bg-success">
                                                Aktif
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                Tidak Aktif
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>
    </div>


    <!-- Aset -->
    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between">
            <strong>Aset pada Unit</strong>

            <span class="badge bg-primary">
                <?= count($assets) ?> aset
            </span>
        </div>

        <div class="card-body">

            <?php if (empty($assets)): ?>

                <div class="text-muted">
                    Belum ada aset yang berada pada unit ini.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Kode Aset</th>
                                <th>Nama Aset</th>
                                <th>Lokasi</th>
                                <th>Kondisi</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($assets as $index => $asset): ?>

                                <tr>

                                    <td>
                                        <?= $index + 1 ?>
                                    </td>

                                    <td>
                                        <?= esc($asset['asset_code']) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= esc($asset['name']) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= esc($asset['location_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= esc($asset['condition_status'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= esc($asset['asset_status'] ?? '-') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>