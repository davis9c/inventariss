<?= view('layout/header', ['title' => 'Tambah Role']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Tambah Role</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <form method="post"
                action="<?= base_url('roles/store') ?>">

                <?= csrf_field() ?>

                <!-- Nama Role -->
                <div class="mb-3">

                    <label class="form-label">
                        Nama Role
                    </label>

                    <input type="text"
                        name="name"
                        class="form-control"
                        required>

                </div>

                <!-- Deskripsi -->
                <div class="mb-3">

                    <label class="form-label">
                        Deskripsi
                    </label>

                    <textarea name="description"
                        class="form-control"
                        rows="3"></textarea>

                </div>

                <!-- Permission -->
                <div class="mb-3">
                    <label class="form-label">
                        Permission
                    </label>

                    <?php foreach ($permissions as $permission): ?>

                        <div class="form-check">
                            <input
                                type="checkbox"
                                name="permission_ids[]"
                                value="<?= $permission['id'] ?>"
                                class="form-check-input"
                                id="permission_<?= $permission['id'] ?>">

                            <label
                                class="form-check-label"
                                for="permission_<?= $permission['id'] ?>">
                                <?= esc($permission['name']) ?>
                            </label>
                        </div>

                    <?php endforeach; ?>
                </div>

                <!-- Tombol -->
                <a href="<?= base_url('roles') ?>"
                    class="btn btn-secondary">

                    Kembali

                </a>

                <button type="submit"
                    class="btn btn-primary">

                    Simpan

                </button>

            </form>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>