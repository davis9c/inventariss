<?= view('layout/header', ['title' => 'Detail User']) ?>
<?= view('layout/sidebar') ?>

<div class="mb-4">

    <h3>Detail User</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <div class="row">
                <div class="col-md-6">
                    <p><strong>Username:</strong> <?= esc($user['username']) ?></p>
                    <p><strong>Nama:</strong> <?= esc($user['name']) ?></p>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($ugUser)): ?>
                        <p><strong>Email:</strong> <?= esc($ugUser['email'] ?? '-') ?></p>
                        <p>
                            <strong>Status UserGate:</strong>
                            <?php if (($ugUser['status'] ?? 'ACTIVE') === 'ACTIVE'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <p>
                        <strong>Status Lokal:</strong>
                        <?php if ($user['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <hr>

            <h6>Role (Lokal)</h6>

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
