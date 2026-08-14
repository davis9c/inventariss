<?= view('layout/header', ['title' => 'Maintenance']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Detail Maintenance</h3>

            <p class="text-muted mb-0">
                <?= esc($maintenance['maintenance_code']) ?>
            </p>
        </div>

        <a href="<?= base_url('maintenances') ?>"
            class="btn btn-secondary">
            Kembali
        </a>

    </div>

    <div class="card shadow-sm">

        <div class="card-header">
            <strong>Informasi Maintenance</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <strong>Kode Maintenance</strong>
                    <div>
                        <?= esc($maintenance['maintenance_code']) ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Barang</strong>
                    <div>
                        <?= esc($maintenance['asset_code']) ?>
                        -
                        <?= esc($maintenance['asset_name']) ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Tanggal</strong>
                    <div>
                        <?= esc($maintenance['maintenance_date']) ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Jenis</strong>
                    <div>
                        <?= esc($maintenance['maintenance_type']) ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Teknisi</strong>
                    <div>
                        <?= esc($maintenance['technician_name'] ?: '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Status</strong>
                    <div>
                        <?= esc($maintenance['status']) ?>
                    </div>
                </div>

            </div>

            <hr>

            <div class="mb-3">
                <strong>Masalah / Keluhan</strong>

                <div class="mt-2">
                    <?= nl2br(esc($maintenance['problem'] ?: '-')) ?>
                </div>
            </div>

            <div class="mb-3">
                <strong>Tindakan / Perbaikan</strong>

                <div class="mt-2">
                    <?= nl2br(esc($maintenance['action_taken'] ?: '-')) ?>
                </div>
            </div>

            <div class="mb-3">
                <strong>Catatan</strong>

                <div class="mt-2">
                    <?= nl2br(esc($maintenance['notes'] ?: '-')) ?>
                </div>
            </div>

        </div>

    </div>

    <div class="card shadow-sm mt-4">

        <div class="card-header">
            <strong>Informasi Approval</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">
                    <strong>Status</strong>
                    <div>
                        <?= esc($maintenance['status']) ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Diproses Oleh</strong>
                    <div>
                        <?= esc($maintenance['approved_by_name'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Waktu Approval</strong>
                    <div>
                        <?= esc($maintenance['approved_at'] ?? '-') ?>
                    </div>
                </div>

            </div>

            <?php if (!empty($maintenance['approval_notes'])): ?>

                <hr>

                <strong>Catatan Approval</strong>

                <div class="alert alert-warning mt-2 mb-0">
                    <?= nl2br(esc($maintenance['approval_notes'])) ?>
                </div>

            <?php endif; ?>

        </div>

    </div>
</div>

<?= view('layout/footer') ?>