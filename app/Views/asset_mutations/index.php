<?= view('layout/header', ['title' => 'Mutasi Aset']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Mutasi Aset</h3>
            <p class="text-muted mb-0">
                Riwayat perpindahan barang.
            </p>
        </div>

        <a href="<?= base_url('asset-mutations/create') ?>"
           class="btn btn-primary">
            + Mutasi Aset
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($mutations)): ?>

                        <tr>
                            <td colspan="6"
                                class="text-center text-muted">
                                Belum ada mutasi.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($mutations as $i => $mutation): ?>

                            <tr>

                                <td><?= $i + 1 ?></td>

                                <td>
                                    <?= esc($mutation['mutation_date']) ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= esc($mutation['asset_name']) ?>
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= esc($mutation['asset_code']) ?>
                                    </small>
                                </td>

                                <td>
                                    <?= esc($mutation['from_unit_name'] ?? '-') ?>
                                    <br>
                                    <small>
                                        <?= esc($mutation['from_location_name'] ?? '-') ?>
                                    </small>
                                </td>

                                <td>
                                    <?= esc($mutation['to_unit_name'] ?? '-') ?>
                                    <br>
                                    <small>
                                        <?= esc($mutation['to_location_name'] ?? '-') ?>
                                    </small>
                                </td>

                                <td>
                                    <?= esc($mutation['reason'] ?? '-') ?>
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