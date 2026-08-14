<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h4 class="mb-1">Setup Sistem</h4>
                    <p class="text-muted mb-4">
                        Buat akun Super Admin untuk pertama kali.
                    </p>

                    <form method="post" action="<?= base_url('setup') ?>">

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text"
                                   class="form-control"
                                   value="admin"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password"
                                   name="password_confirm"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Buat Super Admin
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>