<?= view('layout/header', ['title' => 'Kategori Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Kategori Barang</h3>
            <p class="text-muted mb-0">Kelola kategori barang inventaris.</p>
        </div>

        <a href="<?= base_url('categories/create') ?>" class="btn btn-primary">
            + Tambah Kategori
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
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($categories)): ?>

                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Belum ada kategori.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($categories as $i => $category): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>

                                    <td>
                                        <?= esc($category['name']) ?>
                                    </td>

                                    <td>
                                        <?= esc($category['description'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?php if ($category['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="<?= base_url('categories/edit/' . $category['id']) ?>"
                                           class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <form action="<?= base_url('categories/delete/' . $category['id']) ?>"
                                              method="post"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus kategori ini?')">

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