<?= view('layout/header', ['title' => 'Barang / Aset']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Barang / Aset</h3>
            <p class="text-muted mb-0">
                Kelola seluruh barang inventaris.
            </p>
        </div>

        <a href="<?= base_url('assets/create') ?>"
           class="btn btn-primary">
            + Tambah Barang
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
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Lokasi</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($assets)): ?>

                        <tr>
                            <td colspan="9"
                                class="text-center text-muted">
                                Belum ada barang.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($assets as $i => $asset): ?>

                            <tr>
                                <td><?= $i + 1 ?></td>

                                <td>
                                    <span class="badge bg-secondary">
                                        <?= esc($asset['asset_code']) ?>
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        <?= esc($asset['name']) ?>
                                    </strong>

                                    <?php if ($asset['brand']): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= esc($asset['brand']) ?>
                                            <?= esc($asset['model'] ?? '') ?>
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= esc($asset['category_name']) ?>
                                </td>

                                <td>
                                    <?= esc($asset['unit_name']) ?>
                                </td>

                                <td>
                                    <?= esc($asset['location_name']) ?>
                                </td>

                                <td>
                                    <?php
                                    $conditionClass = match ($asset['condition_status']) {
                                        'Baik' => 'success',
                                        'Rusak Ringan' => 'warning',
                                        'Rusak Berat' => 'danger',
                                        default => 'secondary',
                                    };
                                    ?>

                                    <span class="badge bg-<?= $conditionClass ?>">
                                        <?= esc($asset['condition_status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    $statusClass = match ($asset['asset_status']) {
                                        'Aktif' => 'success',
                                        'Dipinjam' => 'primary',
                                        'Maintenance' => 'warning',
                                        'Tidak Digunakan' => 'secondary',
                                        default => 'secondary',
                                    };
                                    ?>

                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= esc($asset['asset_status']) ?>
                                    </span>
                                </td>

                                <td class="text-nowrap">
<a href="<?= base_url('assets/' . $asset['id']) ?>"
   class="btn btn-sm btn-info">
    Detail
</a>
                                    <a href="<?= base_url('assets/edit/' . $asset['id']) ?>"
                                       class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    <form method="post"
                                          action="<?= base_url('assets/delete/' . $asset['id']) ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus barang ini?')">

                                        <?= csrf_field() ?>

                                        <button type="submit"
                                                class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>

                                    </form>

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