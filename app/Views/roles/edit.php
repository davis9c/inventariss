<?= view('layout/header', ['title' => 'Edit Role']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Edit Role</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <?php if (session()->getFlashdata('error')): ?>

                <div class="alert alert-danger">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>

            <?php endif; ?>

            <form method="post"
                action="<?= base_url('roles/update/' . $role['id']) ?>">

                <?= csrf_field() ?>

                <!-- Nama Role -->
                <div class="mb-3">

                    <label class="form-label">
                        Nama Role
                    </label>

                    <input type="text"
                        name="name"
                        class="form-control"
                        value="<?= esc($role['name']) ?>"
                        required>

                </div>

                <!-- Deskripsi -->
                <div class="mb-3">

                    <label class="form-label">
                        Deskripsi
                    </label>

                    <textarea name="description"
                        class="form-control"
                        rows="3"><?= esc($role['description'] ?? '') ?></textarea>

                </div>

                <!-- Permission -->
                <div class="mb-3">

                    <label class="form-label">
                        Permission
                    </label>

                    <div class="card border">

                        <div class="card-body">

                            <?php if (!empty($permissions)): ?>

                                <?php foreach ($permissions as $permission): ?>

                                    <div class="form-check mb-2">

                                        <input
                                            type="checkbox"
                                            name="permission_ids[]"
                                            value="<?= $permission['id'] ?>"
                                            class="form-check-input"
                                            id="permission_<?= $permission['id'] ?>"
                                            <?= in_array(
                                                $permission['id'],
                                                $permissionIds ?? []
                                            ) ? 'checked' : '' ?>>

                                        <label
                                            class="form-check-label"
                                            for="permission_<?= $permission['id'] ?>">

                                            <strong>
                                                <?= esc($permission['name']) ?>
                                            </strong>

                                            <?php if (!empty($permission['description'])): ?>

                                                <small class="text-muted d-block">
                                                    <?= esc($permission['description']) ?>
                                                </small>

                                            <?php endif; ?>

                                        </label>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <div class="text-muted">
                                    Belum ada permission tersedia.
                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

                <!-- Tombol -->
                <a href="<?= base_url('roles') ?>"
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