<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"
        integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">
</head>
<body class="bg-body-tertiary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-7 col-md-8">
                <div class="card border-0 shadow my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <i class="bi bi-box-seam fs-1 text-primary mb-3"></i>
                                <h1 class="h4 mb-2">Setup Pertama Kali</h1>
                                <p class="mb-4 text-body-secondary">Akun pertama akan dijadikan Super Admin.</p>
                            </div>

                            <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= esc(session()->getFlashdata('error')) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (!$tableExists): ?>
                            <div class="card mb-4 border-start border-4 border-primary">
                                <div class="card-body py-3">
                                    <h6 class="fw-bold text-primary">Langkah 1 — Persiapan Database</h6>
                                    <p class="text-body-secondary small mb-2">Jalankan perintah berikut di terminal:</p>
                                    <div class="mb-2">
                                        <label class="small fw-bold">Migration</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control bg-dark text-light font-monospace" id="cmd-migrate" value="php spark migrate" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyCmd('cmd-migrate', this)">Copy</button>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <label class="small fw-bold">Seed</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control bg-dark text-light font-monospace" id="cmd-seed" value="php spark db:seed DatabaseSeeder" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyCmd('cmd-seed', this)">Copy</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <form method="post" action="<?= base_url('setup') ?>">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <input type="text" name="username" class="form-control form-control-lg"
                                        placeholder="Username" required autofocus>
                                </div>
                                <div class="mb-3">
                                    <input type="password" name="password" class="form-control form-control-lg"
                                        placeholder="Password" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    Login &amp; Setup
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
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
