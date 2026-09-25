<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'Inventaris' ?></title>

    <?php
        $assetVer = max(
            filemtime(FCPATH . 'js/inventaris.js') ?: 0,
            filemtime(FCPATH . 'vendor/datatables.min.css') ?: 0,
            filemtime(FCPATH . 'vendor/datatables.min.js') ?: 0
        ) ?: time();
    ?>

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"
        integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">

    <!-- DataTables (Bootstrap 5 build) -->
    <link href="<?= base_url('vendor/datatables.min.css') ?>?v=<?= $assetVer ?>" rel="stylesheet">

    <!--
        Scripts stay in <head> on purpose: every content view places its
        inline <script> block before view('layout/footer'), so Bootstrap and
        DataTables must already be evaluated by then.
    -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= base_url('vendor/datatables.min.js') ?>?v=<?= $assetVer ?>"></script>

    <script>window.inventarisBaseUrl = '<?= base_url() ?>';</script>
    <script src="<?= base_url('js/inventaris.js') ?>?v=<?= $assetVer ?>"></script>
</head>

<body>

<div class="d-flex flex-column min-vh-100">
