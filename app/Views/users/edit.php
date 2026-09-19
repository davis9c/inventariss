<?= view('layout/header', ['title' => 'Edit User']) ?>
<?= view('layout/sidebar') ?>

<div class="mb-4">

    <h3>Edit User</h3>

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

    <!-- Info dari UserGate (read-only) -->
    <div class="card shadow-sm mt-4 mb-4">
        <div class="card-header">
            <h5 class="mb-0">Data dari UserGate</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Username:</strong> <?= esc($user['username']) ?></p>
                    <p><strong>Nama:</strong> <?= esc($ugUser['full_name'] ?? $user['name']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> <?= esc($ugUser['email'] ?? '-') ?></p>
                    <p>
                        <strong>Status:</strong>
                        <?php if (($ugUser['status'] ?? 'ACTIVE') === 'ACTIVE'): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <small class="text-muted">
                Data UserGate tidak dapat diubah dari sini. Silakan edit langsung di panel admin UserGate.
            </small>
        </div>
    </div>

    <!-- Form Edit Role & Lokasi -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Atur Role & Lokasi</h5>
            <form method="post"
                action="<?= base_url('users/update/' . $user['id']) ?>">

                <?= csrf_field() ?>

                <!-- ROLE -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Role (Lokal)
                    </label>
                    <div class="border rounded p-3">
                        <?php foreach ($roles as $role): ?>
                            <div class="form-check mb-2">
                                <input type="checkbox"
                                    class="form-check-input"
                                    name="role_ids[]"
                                    value="<?= $role['id'] ?>"
                                    id="role_<?= $role['id'] ?>"
                                    <?= in_array($role['id'], $roleIds) ? 'checked' : '' ?>>
                                <label class="form-check-label"
                                    for="role_<?= $role['id'] ?>">
                                    <?= esc($role['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted">
                        Pilih minimal satu role.
                    </small>
                </div>

                <!-- LOKASI -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Lokasi Tanggung Jawab
                    </label>
                    <div class="border rounded p-3">
                        <?php if (empty($locations)): ?>
                            <div class="text-muted">
                                Belum ada lokasi.
                            </div>
                        <?php else: ?>
                            <?php foreach ($locations as $location): ?>
                                <div class="form-check mb-2">
                                    <input type="checkbox"
                                        class="form-check-input"
                                        name="location_ids[]"
                                        value="<?= $location['id'] ?>"
                                        id="location_<?= $location['id'] ?>"
                                        <?= in_array($location['id'], $locationIds) ? 'checked' : '' ?>>
                                    <label class="form-check-label"
                                        for="location_<?= $location['id'] ?>">
                                        <?= esc($location['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">
                        Kosongkan jika user tidak memiliki pembatasan lokasi.
                    </small>
                </div>

                <!-- STATUS -->
                <div class="mb-4">
                    <label class="form-label">
                        Status (Lokal)
                    </label>
                    <select name="is_active"
                        class="form-select">
                        <option value="1"
                            <?= $user['is_active'] ? 'selected' : '' ?>>
                            Aktif
                        </option>
                        <option value="0"
                            <?= !$user['is_active'] ? 'selected' : '' ?>>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <a href="<?= base_url('users') ?>"
                    class="btn btn-secondary">
                    Kembali
                </a>

                <button type="submit"
                    class="btn btn-primary">
                    Simpan Perubahan
                </button>

            </form>
        </div>
    </div>

</div>

<?= view('layout/footer') ?>
