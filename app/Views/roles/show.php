<?= view('layout/header', ['title' => 'Detail Role']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Detail Role</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <h5><?= esc($role['name']) ?></h5>

            <p class="text-muted">
                <?= esc($role['description'] ?? '-') ?>
            </p>

            <hr>

            <h6>Permission</h6>

            <?php if (empty($permissions)): ?>

                <div class="alert alert-warning">
                    Role ini belum memiliki permission.
                </div>

            <?php else: ?>

                <div class="list-group">

                    <?php foreach ($permissions as $permission): ?>

                        <div class="list-group-item">
                            <strong>
                                <?= esc($permission['name']) ?>
                            </strong>

                            <?php if (!empty($permission['description'])): ?>
                                <div class="text-muted small">
                                    <?= esc($permission['description']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <div class="mt-4">

                <a href="<?= base_url('roles') ?>"
                    class="btn btn-secondary">
                    Kembali
                </a>

                <a href="<?= base_url('roles/edit/' . $role['id']) ?>"
                    class="btn btn-primary">
                    Edit Role
                </a>

            </div>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>