<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"
        integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">
</head>
<body class="bg-body-tertiary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-7">
                <div class="card border-0 shadow my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <i class="bi bi-box-seam fs-1 text-primary mb-3"></i>
                                <h1 class="h4 mb-2">Selamat Datang</h1>
                                <p class="mb-4 text-body-secondary">Sistem Inventaris</p>
                            </div>

                            <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= esc(session()->getFlashdata('error')) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="<?= base_url('login') ?>">
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
                                    Login
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
</body>
</html>
