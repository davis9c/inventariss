<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url('dashboard') ?>">
        <div class="sidebar-brand-icon">
            <i class="fas fa-warehouse"></i>
        </div>
        <div class="sidebar-brand-text mx-2">INVENTARIS</div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <?php $seg1 = current_url(true)->getSegment(1); ?>
    <li class="nav-item <?= $seg1 === 'dashboard' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('dashboard') ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <?php
    $roles = session()->get('roles') ?? [];
    $isSuperAdmin = in_array('Super Admin', $roles);
    $canManageAsset = $isSuperAdmin || in_array('Admin Inventaris', $roles) || in_array('Petugas Inventaris', $roles);
    $canManageLocation = $isSuperAdmin || in_array('Admin Inventaris', $roles) || in_array('Petugas Inventaris', $roles);
    $canReport = $isSuperAdmin || in_array('Manajemen', $roles) || in_array('Auditor', $roles);
    ?>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">Inventaris</div>

    <?php if ($canManageAsset): ?>
        <li class="nav-item <?= $seg1 === 'assets' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('assets') ?>">
                <i class="fas fa-fw fa-box"></i>
                <span>Barang / Aset</span>
            </a>
        </li>
        <li class="nav-item <?= $seg1 === 'stock-items' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('stock-items') ?>">
                <i class="fas fa-fw fa-cubes"></i>
                <span>Barang Stok</span>
            </a>
        </li>
        <li class="nav-item <?= $seg1 === 'asset-mutations' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('asset-mutations') ?>">
                <i class="fas fa-fw fa-exchange-alt"></i>
                <span>Mutasi Aset</span>
            </a>
        </li>
        <li class="nav-item <?= $seg1 === 'stock-movements' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('stock-movements') ?>">
                <i class="fas fa-fw fa-truck"></i>
                <span>Stock Movement</span>
            </a>
        </li>
        <li class="nav-item <?= $seg1 === 'stock-opnames' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('stock-opnames') ?>">
                <i class="fas fa-fw fa-clipboard-check"></i>
                <span>Stock Opname</span>
            </a>
        </li>
    <?php endif; ?>

    <?php if ($canManageLocation): ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Master Data</div>
        <li class="nav-item <?= $seg1 === 'categories' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('categories') ?>">
                <i class="fas fa-fw fa-tags"></i>
                <span>Kategori Barang</span>
            </a>
        </li>
        <li class="nav-item <?= $seg1 === 'locations' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('locations') ?>">
                <i class="fas fa-fw fa-map-marker-alt"></i>
                <span>Lokasi</span>
            </a>
        </li>
        <li class="nav-item <?= $seg1 === 'units' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('units') ?>">
                <i class="fas fa-fw fa-building"></i>
                <span>Unit / Departemen</span>
            </a>
        </li>
    <?php endif; ?>

    <?php if ($canReport): ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Laporan</div>
        <li class="nav-item <?= $seg1 === 'reports' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('reports/assets') ?>">
                <i class="fas fa-fw fa-chart-bar"></i>
                <span>Laporan Inventaris</span>
            </a>
        </li>
    <?php endif; ?>

    <?php if ($isSuperAdmin || in_array('Admin Inventaris', $roles)): ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Administrasi</div>
        <li class="nav-item <?= $seg1 === 'users' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('users') ?>">
                <i class="fas fa-fw fa-users"></i>
                <span>User Management</span>
            </a>
        </li>
    <?php endif; ?>

    <?php if ($isSuperAdmin): ?>
        <li class="nav-item <?= $seg1 === 'roles' ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('roles') ?>">
                <i class="fas fa-fw fa-user-shield"></i>
                <span>Manajemen Role</span>
            </a>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle">
            <i class="fas fa-angle-left"></i>
        </button>
    </div>

</ul>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow">
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3">
                <i class="fa fa-bars"></i>
            </button>
            <ul class="navbar-nav ms-auto">
                <div class="topbar-divider d-none d-sm-block"></div>
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="me-2 d-none d-lg-inline text-gray-600 small">
                            <?= esc(session()->get('name') ?? session()->get('username') ?? 'User') ?>
                        </span>
                        <i class="fas fa-user-circle fa-lg text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- End Topbar -->
        <!-- Begin Page Content -->
        <div class="container-fluid">
