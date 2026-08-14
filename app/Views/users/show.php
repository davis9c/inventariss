<?= view('layout/header', ['title' => 'Detail User']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Detail User</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <h5><?= esc($user['name']) ?></h5>

            <p class="text-muted mb-1">
                Username: <?= esc($user['username']) ?>
            </p>

            <p>
                Status:
                <?php if ($user['is_active']): ?>
                    <span class="badge bg-success">Aktif</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Nonaktif</span>
                <?php endif; ?>
            </p>

            <hr>

            <h6>Role</h6>

            <?php if (empty($roles)): ?>

                <div class="alert alert-warning">
                    User belum memiliki role.
                </div>

            <?php else: ?>

                <div class="list-group mb-4">

                    <?php foreach ($roles as $role): ?>

                        <div class="list-group-item">
                            <strong><?= esc($role['name']) ?></strong>

                            <?php if (!empty($role['description'])): ?>
                                <div class="text-muted small">
                                    <?= esc($role['description']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <h6>Lokasi yang Diizinkan</h6>

            <?php if (empty($locations)): ?>

                <div class="alert alert-warning">
                    User belum memiliki lokasi.
                </div>

            <?php else: ?>

                <div class="list-group mb-4">

                    <?php foreach ($locations as $location): ?>

                        <div class="list-group-item">

                            <strong>
                                <?= esc($location['name']) ?>
                            </strong>

                            <div class="text-muted small">
                                <?= esc($location['building'] ?? '-') ?>
                                -
                                <?= esc($location['floor'] ?? '-') ?>
                                -
                                <?= esc($location['room'] ?? '-') ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <a href="<?= base_url('users') ?>"
                class="btn btn-secondary">
                Kembali
            </a>

            <a href="<?= base_url('users/edit/' . $user['id']) ?>"
                class="btn btn-primary">
                Edit User
            </a>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>