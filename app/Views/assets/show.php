<?= view('layout/header', ['title' => 'Detail Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Detail Barang</h3>
            <p class="text-muted mb-0">
                Informasi lengkap aset dan riwayat pengelolaan.
            </p>
        </div>

        <div>
            <a href="<?= base_url('assets') ?>"
                class="btn btn-secondary">
                Kembali
            </a>

            <a href="<?= base_url('assets/edit/' . $asset['id']) ?>"
                class="btn btn-warning">
                Edit
            </a>
        </div>

    </div>


    <!-- INFORMASI BARANG -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Informasi Barang</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <!-- KOLOM KIRI -->

                <div class="col-md-6">

                    <table class="table table-borderless">

                        <tr>
                            <th width="40%">Kode Barang</th>
                            <td>
                                <?= esc($asset['asset_code']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Nama Barang</th>
                            <td>
                                <?= esc($asset['name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Kategori</th>
                            <td>
                                <?= esc($asset['category_name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Merk</th>
                            <td>
                                <?= esc($asset['brand'] ?: '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Model</th>
                            <td>
                                <?= esc($asset['model'] ?: '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Serial Number</th>
                            <td>
                                <?= esc($asset['serial_number'] ?: '-') ?>
                            </td>
                        </tr>

                    </table>

                </div>


                <!-- KOLOM KANAN -->

                <div class="col-md-6">

                    <table class="table table-borderless">

                        <tr>
                            <th width="40%">Unit</th>
                            <td>
                                <?= esc($asset['unit_name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Lokasi</th>
                            <td>
                                <?= esc($asset['location_name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Tahun Perolehan</th>
                            <td>
                                <?= esc($asset['acquisition_year'] ?: '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Harga Perolehan</th>
                            <td>
                                Rp <?= number_format(
                                        $asset['acquisition_price'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Kondisi</th>
                            <td>

                                <?php
                                $conditionBadge = match ($asset['condition_status']) {
                                    'Baik' => 'success',
                                    'Rusak Ringan' => 'warning',
                                    'Rusak Berat' => 'danger',
                                    default => 'secondary',
                                };
                                ?>

                                <span class="badge bg-<?= $conditionBadge ?>">
                                    <?= esc($asset['condition_status']) ?>
                                </span>

                            </td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                <?= esc($asset['asset_status']) ?>
                            </td>
                        </tr>

                    </table>

                </div>

            </div>


            <?php if (!empty($asset['description'])): ?>

                <hr>

                <strong>Deskripsi</strong>

                <p class="text-muted mt-2 mb-0">
                    <?= nl2br(esc($asset['description'])) ?>
                </p>

            <?php endif; ?>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- RIWAYAT MUTASI -->
    <!-- ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <strong>Riwayat Mutasi Aset</strong>

            <a href="<?= base_url('asset-mutations/create?asset_id=' . $asset['id']) ?>"
                class="btn btn-sm btn-primary">
                + Mutasi
            </a>

        </div>

        <div class="card-body">

            <?php if (empty($mutations)): ?>

                <div class="text-center text-muted py-4">
                    Belum ada riwayat mutasi untuk barang ini.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Dari Lokasi</th>
                                <th>Ke Lokasi</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($mutations as $mutation): ?>

                                <tr>

                                    <td>
                                        <?= esc(
                                            $mutation['mutation_date'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $mutation['from_location_name']
                                                ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $mutation['to_location_name']
                                                ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $mutation['description']
                                                ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php
                                        $mutationBadge = match ($mutation['status'] ?? '') {
                                            'Diajukan' => 'warning',
                                            'Disetujui' => 'success',
                                            'Ditolak' => 'danger',
                                            default => 'secondary',
                                        };
                                        ?>

                                        <span class="badge bg-<?= $mutationBadge ?>">
                                            <?= esc(
                                                $mutation['status'] ?? '-'
                                            ) ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- RIWAYAT STOCK OPNAME -->
    <!-- ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <strong>Riwayat Stok Opname</strong>

            <a href="<?= base_url('stock-opnames/create?asset_id=' . $asset['id']) ?>"
                class="btn btn-sm btn-primary">
                + Stok Opname
            </a>

        </div>

        <div class="card-body">

            <?php if (empty($stockOpnames)): ?>

                <div class="text-center text-muted py-4">
                    Belum ada riwayat stok opname untuk barang ini.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Hasil</th>
                                <th>Kondisi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($stockOpnames as $opname): ?>

                                <tr>

                                    <td>
                                        <?= esc(
                                            $opname['opname_date'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $opname['result'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $opname['condition_status']
                                                ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $opname['description'] ?? '-'
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- RIWAYAT MAINTENANCE -->
    <!-- ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <strong>Riwayat Maintenance & Perbaikan</strong>

            <a href="<?= base_url('maintenances/create?asset_id=' . $asset['id']) ?>"
                class="btn btn-sm btn-primary">
                + Maintenance
            </a>

        </div>

        <div class="card-body">

            <?php if (empty($maintenances)): ?>

                <div class="text-center text-muted py-4">
                    Belum ada riwayat maintenance untuk barang ini.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Masalah</th>
                                <th>Tindakan</th>
                                <th>Teknisi</th>
                                <th>Status</th>
                                <th>Biaya</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($maintenances as $maintenance): ?>

                                <tr>

                                    <td>
                                        <?= esc(
                                            $maintenance['maintenance_date']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $maintenance['maintenance_type']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $maintenance['problem'] ?: '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $maintenance['action_taken'] ?: '-'
                                        ) ?>
                                    </td>

                                    <td>

                                        <?php if (
                                            ($maintenance['technician_type'] ?? '')
                                            === 'Internal'
                                        ): ?>

                                            <?= esc(
                                                $maintenance['technician_name']
                                                    ?: '-'
                                            ) ?>

                                            <br>

                                            <span class="badge bg-info">
                                                Internal
                                            </span>

                                        <?php else: ?>

                                            <?= esc(
                                                $maintenance['technician_name']
                                                    ?: '-'
                                            ) ?>

                                            <?php if (
                                                !empty($maintenance['vendor_name'])
                                            ): ?>

                                                <br>

                                                <small class="text-muted">
                                                    <?= esc(
                                                        $maintenance['vendor_name']
                                                    ) ?>
                                                </small>

                                            <?php endif; ?>

                                            <br>

                                            <span class="badge bg-secondary">
                                                External
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php
                                        $statusBadge = match ($maintenance['status']) {
                                            'Diajukan'   => 'warning',
                                            'Diproses'   => 'primary',
                                            'Selesai'    => 'success',
                                            'Dibatalkan' => 'danger',
                                            default      => 'secondary',
                                        };
                                        ?>

                                        <span class="badge bg-<?= $statusBadge ?>">
                                            <?= esc($maintenance['status']) ?>
                                        </span>

                                    </td>

                                    <td>
                                        Rp <?= number_format(
                                                $maintenance['cost'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>
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