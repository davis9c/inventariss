<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= base_url('vendor/all.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/sb-admin-2.min.css') ?>" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-7 col-md-8">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-2">Setup Pertama Kali</h1>
                                <p class="mb-4 text-gray-600">Akun pertama akan dijadikan Super Admin.</p>
                            </div>

                            <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= esc(session()->getFlashdata('error')) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (!$tableExists): ?>
                            <div class="card mb-4 border-left-primary">
                                <div class="card-body py-3">
                                    <h6 class="font-weight-bold text-primary">Langkah 1 — Persiapan Database</h6>
                                    <p class="text-muted small mb-2">Jalankan perintah berikut di terminal:</p>
                                    <div class="mb-2">
                                        <label class="small font-weight-bold">Migration</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control bg-dark text-light font-monospace" id="cmd-migrate" value="php spark migrate" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyCmd('cmd-migrate', this)">Copy</button>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <label class="small font-weight-bold">Seed</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control bg-dark text-light font-monospace" id="cmd-seed" value="php spark db:seed DatabaseSeeder" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyCmd('cmd-seed', this)">Copy</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <form method="post" action="<?= base_url('setup') ?>" class="user">
                                <?= csrf_field() ?>
                                <div class="form-group mb-3">
                                    <input type="text" name="username" class="form-control form-control-user"
                                        placeholder="Username" required autofocus>
                                </div>
                                <div class="form-group mb-3">
                                    <input type="password" name="password" class="form-control form-control-user"
                                        placeholder="Password" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block w-100">
                                    Login & Setup
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= base_url('vendor/bootstrap.bundle.min.js') ?>"></script>
    <script>
    function copyCmd(inputId, btn) {
        var input = document.getElementById(inputId);
        input.select();
        navigator.clipboard.writeText(input.value).then(function() {
            var original = btn.textContent;
            btn.textContent = 'Tersalin!';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-secondary');
            setTimeout(function() {
                btn.textContent = original;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        });
    }
    </script>
</body>
</html>
