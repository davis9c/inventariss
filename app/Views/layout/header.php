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
            filemtime(FCPATH . 'vendor/bootstrap.bundle.min.js') ?: 0
        ) ?: time();
    ?>

    <link href="<?= base_url('vendor/bootstrap.min.css') ?>?v=<?= $assetVer ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/dataTables.bootstrap5.min.css') ?>?v=<?= $assetVer ?>" rel="stylesheet">

    <script src="<?= base_url('vendor/bootstrap.bundle.min.js') ?>?v=<?= $assetVer ?>"></script>
    <script src="<?= base_url('vendor/jquery.min.js') ?>?v=<?= $assetVer ?>"></script>
    <script src="<?= base_url('vendor/dataTables.min.js') ?>?v=<?= $assetVer ?>"></script>
    <script src="<?= base_url('vendor/dataTables.bootstrap5.min.js') ?>?v=<?= $assetVer ?>"></script>
    <script>window.inventarisBaseUrl = '<?= base_url() ?>';</script>
    <script src="<?= base_url('js/inventaris.js') ?>?v=<?= $assetVer ?>"></script>
</head>

<body>

    <div class="d-flex">