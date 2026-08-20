<div class="bg-dark text-white p-3" style="width: 240px; min-height: 100vh;">

    <h5 class="mb-4">INVENTARIS</h5>

    <div class="mb-3">
        <small class="text-secondary">MENU</small>
    </div>

    <div class="nav flex-column">

        <!-- Dashboard -->
        <a href="<?= base_url('dashboard') ?>"
            class="nav-link text-white">
            Dashboard
        </a>


        <?php
        $roles = session()->get('roles') ?? [];

        $isSuperAdmin = in_array('Super Admin', $roles);

        $canManageAsset =
            $isSuperAdmin ||
            in_array('Admin Inventaris', $roles) ||
            in_array('Petugas Inventaris', $roles);

        $canManageCategories = has_permission('category.manage');
        $canManageLocations  = has_permission('location.manage');
        $canManageUnits      = has_permission('unit.manage');

        $canManageMasterData =
            $canManageCategories ||
            $canManageLocations ||
            $canManageUnits;

        $canReport =
            $isSuperAdmin ||
            in_array('Manajemen', $roles) ||
            in_array('Auditor', $roles);
        ?>


        <!-- Barang -->
        <?php if ($canManageAsset): ?>

            <a href="<?= base_url('assets') ?>"
                class="nav-link text-white">
                Barang / Aset
            </a>

            <a href="<?= base_url('stock-items') ?>"
                class="nav-link text-white">
                Barang Stok
            </a>

            <a href="<?= base_url('asset-mutations') ?>"
                class="nav-link text-white">
                Mutasi Aset
            </a>

            <a href="<?= base_url('stock-movements') ?>"
                class="nav-link text-white">
                Stock Movement
            </a>

            <a href="<?= base_url('stock-opnames') ?>"
                class="nav-link text-white">
                Stock Opname
            </a>

        <?php endif; ?>


        <!-- Master Data -->
        <?php if ($canManageMasterData): ?>

            <hr>

            <div class="mb-2">
                <small class="text-secondary">
                    MASTER DATA
                </small>
            </div>

            <?php if ($canManageCategories): ?>
                <a href="<?= base_url('categories') ?>"
                    class="nav-link text-white">
                    Kategori Barang
                </a>
            <?php endif; ?>

            <?php if ($canManageLocations): ?>
                <a href="<?= base_url('locations') ?>"
                    class="nav-link text-white">
                    Lokasi
                </a>
            <?php endif; ?>

            <?php if ($canManageUnits): ?>
                <a href="<?= base_url('units') ?>"
                    class="nav-link text-white">
                    Unit / Departemen
                </a>
            <?php endif; ?>

        <?php endif; ?>


        <!-- Laporan -->
        <?php if ($canReport): ?>

            <hr>
            <a href="<?= base_url('reports/assets') ?>" class="nav-link text-white">
                Laporan Inventaris
            </a>
        <?php endif; ?>


        <!-- User Management -->
        <?php if ($isSuperAdmin): ?>

            <hr>

            <div class="mb-2">
                <small class="text-secondary">
                    ADMINISTRASI
                </small>
            </div>

            <a href="<?= base_url('users') ?>"
                class="nav-link text-white">
                User Management
            </a>

            <a href="<?= base_url('roles') ?>"
                class="nav-link text-white">
                Manajemen Role
            </a>

        <?php endif; ?>


        <?php if (! hasAppAccess()): ?>

            <hr>

            <div class="small text-secondary p-2 rounded"
                style="background: rgba(255,255,255,.08);">
                Akun Anda belum memiliki akses modul.
                Hubungi Administrator Inventaris untuk meminta akses.
            </div>

        <?php endif; ?>


        <hr>

        <form method="post" action="<?= base_url('logout') ?>" class="w-100">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-light btn-sm w-100">
                Logout
            </button>
        </form>

    </div>

</div>

<div class="flex-grow-1">