<?= view('layout/header', ['title' => 'Laporan Inventaris']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="mb-4">
        <h3>Laporan Inventaris</h3>

        <p class="text-muted mb-0">
            Daftar barang / aset inventaris.
        </p>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="get"
                action="<?= base_url('reports/assets') ?>">

                <div class="row">

                    <!-- Lokasi -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Lokasi
                        </label>

                        <select name="location_id"
                            class="form-select">

                            <option value="">
                                Semua Lokasi
                            </option>

                            <?php foreach ($locations as $location): ?>

                                <option value="<?= $location['id'] ?>"
                                    <?= ($filters['location_id'] ?? '') == $location['id']
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc($location['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Kategori -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Kategori
                        </label>

                        <select name="category_id"
                            class="form-select">

                            <option value="">
                                Semua Kategori
                            </option>

                            <?php foreach ($categories as $category): ?>

                                <option value="<?= $category['id'] ?>"
                                    <?= ($filters['category_id'] ?? '') == $category['id']
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc($category['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Kondisi -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Kondisi
                        </label>

                        <select name="condition_status"
                            class="form-select">

                            <option value="">
                                Semua Kondisi
                            </option>

                            <option value="Baik"
                                <?= ($filters['condition_status'] ?? '') === 'Baik'
                                    ? 'selected'
                                    : '' ?>>
                                Baik
                            </option>

                            <option value="Rusak Ringan"
                                <?= ($filters['condition_status'] ?? '') === 'Rusak Ringan'
                                    ? 'selected'
                                    : '' ?>>
                                Rusak Ringan
                            </option>

                            <option value="Rusak Berat"
                                <?= ($filters['condition_status'] ?? '') === 'Rusak Berat'
                                    ? 'selected'
                                    : '' ?>>
                                Rusak Berat
                            </option>

                        </select>

                    </div>


                    <!-- Status -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Status Aset
                        </label>

                        <select name="asset_status"
                            class="form-select">

                            <option value="">
                                Semua Status
                            </option>

                            <option value="Digunakan"
                                <?= ($filters['asset_status'] ?? '') === 'Digunakan'
                                    ? 'selected'
                                    : '' ?>>
                                Digunakan
                            </option>

                            <option value="Tidak Digunakan"
                                <?= ($filters['asset_status'] ?? '') === 'Tidak Digunakan'
                                    ? 'selected'
                                    : '' ?>>
                                Tidak Digunakan
                            </option>

                        </select>

                    </div>

                </div>


                <div>

                    <button type="submit"
                        class="btn btn-primary">

                        Tampilkan

                    </button>

                    <a href="<?= base_url('reports/assets') ?>"
                        class="btn btn-secondary">

                        Reset

                    </a>

                </div>

            </form>

        </div>

    </div>


    <!-- Ringkasan -->
    <div class="alert alert-light border">

        <strong>
            Total aset:
        </strong>

        <?= count($assets) ?>

    </div>


    <!-- Tabel -->
    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Kode Aset</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Lokasi</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($assets)): ?>

                            <tr>

                                <td colspan="8"
                                    class="text-center text-muted">

                                    Tidak ada data inventaris.

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($assets as $i => $asset): ?>

                                <tr>

                                    <td>
                                        <?= $i + 1 ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= esc($asset['asset_code']) ?>
                                        </strong>
                                    </td>

                                    <td>

                                        <?= esc($asset['name']) ?>

                                        <?php if (!empty($asset['serial_number'])): ?>

                                            <br>

                                            <small class="text-muted">
                                                SN:
                                                <?= esc($asset['serial_number']) ?>
                                            </small>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= esc($asset['category_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= esc($asset['unit_name'] ?? '-') ?>
                                    </td>

                                    <td>

                                        <?= esc($asset['location_name'] ?? '-') ?>

                                        <?php if (!empty($asset['building']) || !empty($asset['room'])): ?>

                                            <br>

                                            <small class="text-muted">

                                                <?= esc($asset['building'] ?? '') ?>

                                                <?php if (!empty($asset['room'])): ?>
                                                    -
                                                    <?= esc($asset['room']) ?>
                                                <?php endif; ?>

                                            </small>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if ($asset['condition_status'] === 'Baik'): ?>

                                            <span class="badge bg-success">
                                                Baik
                                            </span>

                                        <?php elseif ($asset['condition_status'] === 'Rusak Ringan'): ?>

                                            <span class="badge bg-warning text-dark">
                                                Rusak Ringan
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-danger">
                                                <?= esc($asset['condition_status']) ?>
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if ($asset['asset_status'] === 'Digunakan'): ?>

                                            <span class="badge bg-success">
                                                Digunakan
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                <?= esc($asset['asset_status']) ?>
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>