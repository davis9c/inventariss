<?php
$seg1 = current_url(true)->getSegment(1);

$roles = session()->get('roles') ?? [];
$isSuperAdmin = isSuperAdmin();
$canManageAsset = $isSuperAdmin || in_array('Admin Inventaris', $roles) || in_array('Petugas Inventaris', $roles);
$canManageLocation = $isSuperAdmin || in_array('Admin Inventaris', $roles) || in_array('Petugas Inventaris', $roles);
$canReport = $isSuperAdmin || in_array('Manajemen', $roles) || in_array('Auditor', $roles);

/**
 * Render satu grup menu sebagai dropdown Bootstrap.
 *
 * Toggle mendapat .active bila salah satu item di dalamnya sedang aktif,
 * supaya posisi menu tetap terbaca walau grupnya tertutup.
 *
 * @param string $label  Judul grup
 * @param array  $items  Daftar [segment, label, url]
 */
$navGroup = function (string $label, array $items) use ($seg1): void {
    if (empty($items)) {
        return;
    }

    $groupActive = false;
    foreach ($items as $item) {
        if ($seg1 === $item[0]) {
            $groupActive = true;
            break;
        }
    }

    echo '<li class="nav-item dropdown">'
        . '<a class="nav-link dropdown-toggle' . ($groupActive ? ' active' : '') . '"'
        . ' href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">'
        . esc($label)
        . '</a>'
        . '<ul class="dropdown-menu">';

    foreach ($items as [$segment, $itemLabel, $url]) {
        echo '<li><a class="dropdown-item' . ($seg1 === $segment ? ' active' : '') . '"'
            . ' href="' . esc($url) . '">' . esc($itemLabel) . '</a></li>';
    }

    echo '</ul></li>';
};

// ─── Grup: Inventaris ────────────────────────────────────────────
$inventaris = [];
if ($canManageAsset) {
    // Badge jumlah permintaan mutasi pending (masuk ke lokasi user)
    $pendingTransferCount = 0;
    try {
        $locIds = session()->get('location_ids') ?? [];
        if (!is_array($locIds) || empty($locIds)) {
            $locIds = array_column(
                (new \App\Models\LocationModel())->where('is_active', 1)->findAll(),
                'id'
            );
        }
        $pendingTransferCount = (new \App\Models\TransferRequestModel())->countPendingIncoming(array_map('intval', $locIds));
    } catch (\Throwable $e) {
        $pendingTransferCount = 0;
    }

    $transferLabel = 'Permintaan Mutasi';
    if ($pendingTransferCount > 0) {
        $transferLabel .= ' (' . $pendingTransferCount . ')';
    }

    $inventaris = [
        ['assets', 'Barang / Aset', base_url('assets')],
        ['stock-items', 'Barang Stok', base_url('stock-items')],
        ['asset-mutations', 'Mutasi Aset', base_url('asset-mutations')],
        ['transfer-requests', $transferLabel, base_url('transfer-requests')],
        ['stock-movements', 'Stock Movement', base_url('stock-movements')],
        ['stock-opnames', 'Stock Opname', base_url('stock-opnames')],
    ];
}

// ─── Grup: Master Data ────────────────────────────────────────────
$masterData = [];
if ($canManageLocation) {
    $masterData = [
        ['categories', 'Kategori Barang', base_url('categories')],
        ['locations', 'Lokasi', base_url('locations')],
        ['units', 'Unit / Departemen', base_url('units')],
    ];
}

// ─── Grup: Laporan ────────────────────────────────────────────────
$laporan = [];
if ($canReport) {
    $laporan = [
        ['reports', 'Laporan Inventaris', base_url('reports/assets')],
    ];
}

// ─── Grup: Bantuan ───────────────────────────────────────────────
// Dokumentasi hak akses. Terbuka untuk semua user yang login.
$bantuan = [
    ['help', 'Panduan Akses', base_url('help')],
    // Label undergoes esc() when rendered, so it must stay plain text --
    // writing &amp; here would show up literally as "&amp;".
    ['help-dokumen', 'Panduan Dokumen & Gambar', base_url('help/dokumen')],
];

// ─── Grup: Administrasi ───────────────────────────────────────────
// Role dikelola lewat seed, jadi tidak ada Manajemen Role. Yang tersisa
// hanya User Management: memberikan role yang sudah ada ke user.
$administrasi = [];
if ($isSuperAdmin || in_array('Admin Inventaris', $roles)) {
    $administrasi = [
        ['users', 'User Management', base_url('users')],
    ];
}
?>

<!-- Topbar: seluruh navigasi memakai komponen navbar + dropdown Bootstrap -->
<nav class="navbar navbar-expand-xl bg-body border-bottom sticky-top">
    <div class="container-fluid">

        <a class="navbar-brand fw-bold" href="<?= base_url('dashboard') ?>">
            <i class="bi bi-box-seam me-2"></i>INVENTARIS
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNav"
            aria-controls="appNav" aria-expanded="false" aria-label="Tampilkan menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="appNav">
            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a class="nav-link<?= $seg1 === 'dashboard' ? ' active' : '' ?>" href="<?= base_url('dashboard') ?>">
                        Dashboard
                    </a>
                </li>

                <?php $navGroup('Inventaris', $inventaris); ?>
                <?php $navGroup('Master Data', $masterData); ?>
                <?php $navGroup('Laporan', $laporan); ?>
                <?php $navGroup('Bantuan', $bantuan); ?>
                <?php $navGroup('Administrasi', $administrasi); ?>

            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="themeDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false" aria-label="Pilih tema tampilan">
                        <i class="bi bi-circle-half" id="themeIcon"></i>
                        <span class="d-none d-xl-inline ms-1 text-body-secondary small" id="themeLabel">Sistem</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeDropdown">
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center"
                                data-theme-option="light">
                                <i class="bi bi-sun me-2"></i>
                                <span>Terang</span>
                                <i class="bi bi-check2 ms-auto theme-check invisible"></i>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center"
                                data-theme-option="dark">
                                <i class="bi bi-moon-stars me-2"></i>
                                <span>Gelap</span>
                                <i class="bi bi-check2 ms-auto theme-check invisible"></i>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center"
                                data-theme-option="auto">
                                <i class="bi bi-circle-half me-2"></i>
                                <span>Ikuti Sistem</span>
                                <i class="bi bi-check2 ms-auto theme-check invisible"></i>
                            </button>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-xl-inline ms-1 text-body-secondary small">
                            <?= esc(session()->get('name') ?? session()->get('username') ?? 'User') ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

    </div>
</nav>

<!-- Begin Page Content -->
<main class="flex-grow-1 container-fluid py-4">
