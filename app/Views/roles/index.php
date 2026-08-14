<?= view('layout/header', ['title' => 'Lokasi']) ?>
<?= view('layout/sidebar') ?>
<div class="p-4">
    <h3>Manajemen Role</h3>

    <div class="d-flex justify-content-between mb-3">

        <p class="text-muted">
            Kelola role pengguna sistem.
        </p>

        <a href="<?= base_url('roles/create') ?>"
            class="btn btn-primary">
            + Tambah Role
        </a>

    </div>

    <?php if (session()->getFlashdata('success')): ?>

        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Role</th>
                        <th>Deskripsi</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($roles as $i => $role): ?>

                        <tr>

                            <td>
                                <?= $i + 1 ?>
                            </td>

                            <td>
                                <strong>
                                    <?= esc($role['name']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= esc($role['description']) ?>
                            </td>

                            <td>
                                <a href="<?= base_url('roles/' . $role['id']) ?>"
                                    class="btn btn-sm btn-info">
                                    Detail
                                </a>
                                <a href="<?= base_url('roles/edit/' . $role['id']) ?>"
                                    class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <form action="<?= base_url('roles/delete/' . $role['id']) ?>"
                                    method="post"
                                    class="d-inline">

                                    <?= csrf_field() ?>

                                    <button type="submit"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus role ini?')">
                                        Hapus
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>
</div>

<?= view('layout/footer') ?>