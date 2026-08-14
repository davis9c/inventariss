<?= view('layout/header', ['title' => 'User Management']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>User Management</h3>
            <p class="text-muted mb-0">
                Kelola pengguna dan role aplikasi.
            </p>
        </div>

        <a href="<?= base_url('users/create') ?>"
            class="btn btn-primary">
            + Tambah User
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
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($users)): ?>

                            <tr>
                                <td colspan="6"
                                    class="text-center text-muted">
                                    Belum ada user.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($users as $i => $user): ?>

                                <tr>

                                    <td><?= $i + 1 ?></td>

                                    <td>
                                        <?= esc($user['name']) ?>
                                    </td>

                                    <td>
                                        <?= esc($user['username']) ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($user['roles'])): ?>

                                            <?php foreach ($user['roles'] as $role): ?>

                                                <span class="badge bg-primary me-1">
                                                    <?= esc($role['name']) ?>
                                                </span>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                Belum ada role
                                            </span>

                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <a href="<?= base_url('users/' . $user['id']) ?>"
                                            class="btn btn-sm btn-info">
                                            Detail
                                        </a>
                                        <a href="<?= base_url('users/edit/' . $user['id']) ?>"
                                            class="btn btn-sm btn-warning">
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