<?= view('layout/header', ['title' => 'Maintenance']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Maintenance & Perbaikan</h3>

            <p class="text-muted mb-0">
                Riwayat perawatan dan perbaikan barang.
            </p>
        </div>

        <?php if (has_permission('maintenance.create')): ?>

            <a href="<?= base_url('maintenances/create') ?>"
                class="btn btn-primary">
                + Maintenance
            </a>

        <?php endif; ?>

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
                            <th>Kode</th>
                            <th>Barang</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Teknisi</th>
                            <th>Status</th>
                            <th>Biaya</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>
                    <tbody>
                        <?php if (empty($maintenances)): ?>
                            <tr>
                                <td colspan="9"
                                    class="text-center text-muted">
                                    Belum ada data maintenance.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($maintenances as $index => $maintenance): ?>
                                <tr>
                                    <!-- Nomor -->
                                    <td>
                                        <?= $index + 1 ?>
                                    </td>

                                    <!-- Kode -->
                                    <td>
                                        <strong>
                                            <?= esc($maintenance['maintenance_code']) ?>
                                        </strong>
                                    </td>

                                    <!-- Barang -->
                                    <td>
                                        <strong>
                                            <?= esc($maintenance['asset_name']) ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= esc($maintenance['asset_code']) ?>
                                        </small>
                                    </td>

                                    <!-- Tanggal -->
                                    <td>
                                        <?= esc($maintenance['maintenance_date']) ?>
                                    </td>

                                    <!-- Jenis -->
                                    <td>
                                        <?= esc($maintenance['maintenance_type']) ?>
                                    </td>

                                    <!-- Teknisi -->
                                    <td>
                                        <?= esc($maintenance['technician_type']) ?>
                                        <?php if (!empty($maintenance['technician_name'])): ?>

                                            <br>
                                            <small class="text-muted">
                                                <?= esc($maintenance['technician_name']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <?php
                                        $status = $maintenance['status'] ?? '';

                                        $statusClass = match ($status) {
                                            'Diajukan'   => 'bg-warning text-dark',
                                            'Disetujui'  => 'bg-primary',
                                            'Diproses'   => 'bg-info text-dark',
                                            'Selesai'    => 'bg-success',
                                            'Ditolak'    => 'bg-danger',
                                            'Dibatalkan' => 'bg-secondary',
                                            default      => 'bg-secondary',
                                        };
                                        ?>

                                        <span class="badge <?= $statusClass ?>">
                                            <?= esc($status ?: 'Belum ditentukan') ?>
                                        </span>
                                    </td>

                                    <!-- Biaya -->
                                    <td>
                                        Rp <?= number_format(
                                                (float) $maintenance['cost'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>
                                    </td>

                                    <!-- Aksi -->
                                    <td>

                                        <!-- Diajukan -->
                                        <?php if ($maintenance['status'] === 'Diajukan'): ?>
                                            <?php if (has_permission('maintenance.approve')): ?>
                                                <form method="post"
                                                    action="<?= base_url('maintenances/approve/' . $maintenance['id']) ?>"
                                                    class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit"
                                                        class="btn btn-success btn-sm">
                                                        Setujui
                                                    </button>
                                                </form>

                                                <button type="button"
                                                    class="btn btn-danger btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal<?= $maintenance['id'] ?>">
                                                    Tolak
                                                </button>

                                                <div class="modal fade"
                                                    id="rejectModal<?= $maintenance['id'] ?>"
                                                    tabindex="-1">

                                                    <div class="modal-dialog">

                                                        <div class="modal-content">

                                                            <form method="post"
                                                                action="<?= base_url('maintenances/reject/' . $maintenance['id']) ?>">

                                                                <?= csrf_field() ?>

                                                                <div class="modal-header">

                                                                    <h5 class="modal-title">
                                                                        Tolak Maintenance
                                                                    </h5>

                                                                    <button type="button"
                                                                        class="btn-close"
                                                                        data-bs-dismiss="modal">
                                                                    </button>

                                                                </div>

                                                                <div class="modal-body">

                                                                    <p>
                                                                        Anda akan menolak maintenance:
                                                                    </p>

                                                                    <strong>
                                                                        <?= esc($maintenance['maintenance_code']) ?>
                                                                    </strong>

                                                                    <div class="mt-3">

                                                                        <label class="form-label">
                                                                            Alasan Penolakan
                                                                        </label>

                                                                        <textarea name="approval_notes"
                                                                            class="form-control"
                                                                            rows="4"
                                                                            required
                                                                            placeholder="Masukkan alasan penolakan..."></textarea>

                                                                    </div>

                                                                </div>

                                                                <div class="modal-footer">

                                                                    <button type="button"
                                                                        class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">
                                                                        Batal
                                                                    </button>

                                                                    <button type="submit"
                                                                        class="btn btn-danger">
                                                                        Tolak Maintenance
                                                                    </button>

                                                                </div>

                                                            </form>

                                                        </div>

                                                    </div>

                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Disetujui -->
                                        <?php if ($maintenance['status'] === 'Disetujui'): ?>
                                            <?php if (has_permission('maintenance.update')): ?>
                                                <form method="post"
                                                    action="<?= base_url('maintenances/start/' . $maintenance['id']) ?>"
                                                    class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit"
                                                        class="btn btn-primary btn-sm">
                                                        Mulai Proses
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Diproses -->
                                        <?php if ($maintenance['status'] === 'Diproses'): ?>
                                            <?php if (has_permission('maintenance.update')): ?>
                                                <form method="post"
                                                    action="<?= base_url('maintenances/complete/' . $maintenance['id']) ?>"
                                                    class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit"
                                                        class="btn btn-success btn-sm">
                                                        Selesaikan
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Edit -->
                                        <?php if (
                                            has_permission('maintenance.update')
                                            && !in_array(
                                                $maintenance['status'],
                                                ['Selesai', 'Ditolak']
                                            )
                                        ): ?>
                                            <a href="<?= base_url('maintenances/edit/' . $maintenance['id']) ?>"
                                                class="btn btn-warning btn-sm">
                                                Edit
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('maintenances/' . $maintenance['id']) ?>"
                                            class="btn btn-info btn-sm">
                                            Detail
                                        </a>
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