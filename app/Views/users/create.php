<?= view('layout/header', ['title' => 'Tambah User']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Tambah User</h3>

    <div class="card shadow-sm mt-4">

        <div class="card-body">

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors')): ?>

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        <?php foreach (session()->getFlashdata('errors') as $error): ?>

                            <li><?= esc($error) ?></li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>

            <form method="post"
                action="<?= base_url('users/store') ?>">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">
                        Username
                    </label>

                    <input type="text"
                        name="username"
                        class="form-control"
                        value="<?= old('username') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Nama
                    </label>

                    <input type="text"
                        name="name"
                        class="form-control"
                        value="<?= old('name') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Password
                    </label>

                    <input type="password"
                        name="password"
                        class="form-control"
                        required>
                </div>

                <!-- ROLE -->
                <div class="mb-4">

                    <label class="form-label fw-bold">
                        Role
                    </label>

                    <div class="border rounded p-3">

                        <?php foreach ($roles as $role): ?>

                            <div class="form-check mb-2">

                                <input type="checkbox"
                                    class="form-check-input"
                                    name="role_ids[]"
                                    value="<?= $role['id'] ?>"
                                    id="role_<?= $role['id'] ?>"
                                    <?= in_array(
                                        $role['id'],
                                        old('role_ids') ?? []
                                    ) ? 'checked' : '' ?>>

                                <label class="form-check-label"
                                    for="role_<?= $role['id'] ?>">

                                    <?= esc($role['name']) ?>

                                </label>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <small class="text-muted">
                        Kosongkan bila akun belum perlu akses modul.
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
                                        <?= in_array(
                                            $location['id'],
                                            old('location_ids') ?? []
                                        ) ? 'checked' : '' ?>>

                                    <label class="form-check-label"
                                        for="location_<?= $location['id'] ?>">

                                        <?= esc($location['name']) ?>

                                    </label>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                    <small class="text-muted">
                        User dapat memiliki lebih dari satu lokasi.
                        Kosongkan jika user tidak memiliki pembatasan lokasi.
                    </small>

                </div>

                <div class="d-flex gap-2">

                    <a href="<?= base_url('users') ?>"
                        class="btn btn-secondary">

                        Kembali

                    </a>

                    <button type="submit"
                        class="btn btn-primary">

                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

<?= view('layout/footer') ?>