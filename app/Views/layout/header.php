<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'Inventaris' ?></title>

    <?php
        $assetVer = max(
            filemtime(FCPATH . 'js/inventaris.js') ?: 0,
            filemtime(FCPATH . 'vendor/jquery.min.js') ?: 0,
            filemtime(FCPATH . 'vendor/dataTables.min.js') ?: 0,
            filemtime(FCPATH . 'vendor/bootstrap.bundle.min.js') ?: 0,
            filemtime(FCPATH . 'vendor/sb-admin-2.min.css') ?: 0,
            filemtime(FCPATH . 'vendor/sb-admin-2.min.js') ?: 0
        ) ?: time();
    ?>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="<?= base_url('vendor/all.min.css') ?>?v=<?= $assetVer ?>" rel="stylesheet">

    <!-- Bootstrap & SBAdmin2 -->
    <link href="<?= base_url('vendor/bootstrap.min.css') ?>?v=<?= $assetVer ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/sb-admin-2.min.css') ?>?v=<?= $assetVer ?>" rel="stylesheet">

    <!-- DataTables (after SBAdmin2) -->
    <link href="<?= base_url('vendor/dataTables.bootstrap5.min.css') ?>?v=<?= $assetVer ?>" rel="stylesheet">

    <!-- Fix DataTables BS5 specificity vs SBAdmin2 -->
    <style>
        /* Let SBAdmin2's .form-control handle search input styling */
        div.dataTables_wrapper .dataTables_filter input {
            width: 100%;
            max-width: 280px;
        }

        /* Let SBAdmin2's .form-select handle length menu styling */
        div.dataTables_wrapper .dataTables_length select {
            width: auto;
        }

        /* Fix table margins — SBAdmin2 handles spacing via .card */
        table.dataTable {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
    </style>

    <!-- jQuery & Bootstrap Bundle -->
    <script src="<?= base_url('vendor/jquery.min.js') ?>?v=<?= $assetVer ?>"></script>
    <script src="<?= base_url('vendor/bootstrap.bundle.min.js') ?>?v=<?= $assetVer ?>"></script>

    <!-- DataTables -->
    <script src="<?= base_url('vendor/dataTables.min.js') ?>?v=<?= $assetVer ?>"></script>
    <script src="<?= base_url('vendor/dataTables.bootstrap5.min.js') ?>?v=<?= $assetVer ?>"></script>

    <script>window.inventarisBaseUrl = '<?= base_url() ?>';</script>
    <script src="<?= base_url('js/inventaris.js') ?>?v=<?= $assetVer ?>"></script>
</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">
