<?= view('layout/header', ['title' => 'Tambah User']) ?>
<?= view('layout/sidebar') ?>

<div class="mb-4">

    <h3>Tambah User</h3>
    <p class="text-muted">User akan dibuat di UserGate.</p>

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
                        Email
                    </label>

                    <input type="email"
                        name="email"
                        class="form-control"
                        value="<?= old('email') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Nama Lengkap
                    </label>

                    <input type="text"
                        name="full_name"
                        class="form-control"
                        value="<?= old('full_name') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Password
                    </label>

                    <input type="password"
                        name="password"
                        class="form-control"
                        minlength="8"
                        required>

                    <small class="text-muted">
                        Minimal 8 karakter.
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
