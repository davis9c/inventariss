<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= base_url('vendor/all.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/sb-admin-2.min.css') ?>" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-7">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-2">Selamat Datang</h1>
                                <p class="mb-4 text-gray-600">Sistem Inventaris</p>
                            </div>

                            <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= esc(session()->getFlashdata('error')) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="<?= base_url('login') ?>" class="user">
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
                                    Login
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= base_url('vendor/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
