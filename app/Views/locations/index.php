<?= view('layout/header', ['title' => 'Lokasi']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Lokasi</h3>

            <p class="text-muted mb-0">
                Daftar lokasi penyimpanan dan penempatan aset.
            </p>
        </div>

        <a href="<?= base_url('locations/create') ?>"
            class="btn btn-primary">
            + Tambah Lokasi
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
                        <th>#</th>
                        <th>Nama Lokasi</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </thead>

                    <tbody>

                        <?php if (empty($locations)): ?>

                            <tr>
                                <td colspan="5"
                                    class="text-center text-muted">
                                    Belum ada data lokasi.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($locations as $index => $location): ?>

                                <tr>

                                    <td>
                                        <?= $index + 1 ?>
                                    </td>

                                    <td>
                                        <?= esc($location['name']) ?>
                                    </td>

                                    <td>
                                        <?= esc($location['description'] ?? '-') ?>
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

                                    <td>

                                        <a href="<?= base_url('locations/' . $location['id']) ?>"
                                            class="btn btn-info btn-sm">
                                            Detail
                                        </a>

                                        <a href="<?= base_url('locations/edit/' . $location['id']) ?>"
                                            class="btn btn-warning btn-sm">
                                            Edit
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