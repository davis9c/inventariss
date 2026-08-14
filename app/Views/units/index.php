<?= view('layout/header', ['title' => 'Unit / Departemen']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Unit / Departemen</h3>
            <p class="text-muted mb-0">
                Kelola unit atau departemen.
            </p>
        </div>

        <a href="<?= base_url('units/create') ?>"
            class="btn btn-primary">
            + Tambah Unit
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
                            <th width="60">#</th>
                            <th>Kode</th>
                            <th>Nama Unit</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($units)): ?>

                            <tr>
                                <td colspan="6"
                                    class="text-center text-muted">
                                    Belum ada unit.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($units as $i => $unit): ?>

                                <tr>
                                    <td><?= $i + 1 ?></td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= esc($unit['code']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= esc($unit['name']) ?>
                                    </td>

                                    <td>
                                        <?= esc($unit['description'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?php if ($unit['is_active']): ?>
                                            <span class="badge bg-success">
                                                Aktif
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                Nonaktif
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="<?= base_url('units/' . $unit['id']) ?>"
                                            class="btn btn-sm btn-info">
                                            Detail
                                        </a>

                                        <a href="<?= base_url('units/edit/' . $unit['id']) ?>"
                                            class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <form action="<?= base_url('units/delete/' . $unit['id']) ?>"
                                            method="post"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus unit ini?');">

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