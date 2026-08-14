<?= view('layout/header', ['title' => 'Stock Opname']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Stock Opname</h3>

            <p class="text-muted mb-0">
                Pemeriksaan fisik barang inventaris.
            </p>
        </div>

        <a href="<?= base_url('stock-opnames/create') ?>"
           class="btn btn-primary">
            + Buat Stock Opname
        </a>

    </div>

    <?php if (session()->getFlashdata('success')): ?>

        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
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
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($opnames)): ?>

                        <tr>
                            <td colspan="6"
                                class="text-center text-muted">

                                Belum ada stock opname.

                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($opnames as $i => $opname): ?>

                            <tr>

                                <td>
                                    <?= $i + 1 ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= esc($opname['opname_code']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= esc($opname['opname_date']) ?>
                                </td>

                                <td>

                                    <?php if ($opname['location_id']): ?>

                                        <?= esc($opname['building']) ?>
                                        -
                                        <?= esc($opname['room']) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Semua lokasi
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if ($opname['status'] === 'Selesai'): ?>

                                        <span class="badge bg-success">
                                            Selesai
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">
                                            Draft
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <a href="<?= base_url('stock-opnames/' . $opname['id']) ?>"
                                       class="btn btn-sm btn-primary">

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