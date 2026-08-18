<?= view('layout/header', ['title' => 'Detail Lokasi']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Detail Lokasi</h3>

            <p class="text-muted mb-0">
                Informasi lokasi.
            </p>
        </div>

        <a href="<?= base_url('locations') ?>"
            class="btn btn-secondary">
            Kembali
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#locationPhotoModal">Tambah Foto</button>

    </div>

    <div class="card shadow-sm mt-4"><div class="card-header"><strong>Dokumentasi & Histori Foto Lokasi</strong></div><div class="card-body"><?php if (empty($photos)): ?><p class="text-muted mb-0">Belum ada foto lokasi.</p><?php else: ?><div class="row g-3"><?php foreach ($photos as $photo): ?><div class="col-6 col-md-3"><a target="_blank" href="<?= base_url('attachments/file/photo/' . $photo['id']) ?>"><img class="img-fluid rounded" src="<?= base_url('attachments/file/photo/' . $photo['id']) ?>" alt="<?= esc($photo['caption'] ?: 'Foto lokasi') ?>"></a><small><?= esc($photo['caption']) ?></small></div><?php endforeach; ?></div><?php endif; ?></div></div>

    <div class="card shadow-sm">

        <div class="card-body">

            <dl class="row mb-0">

                <dt class="col-md-3">Nama Lokasi</dt>
                <dd class="col-md-9">
                    <?= esc($location['name']) ?>
                </dd>

                <dt class="col-md-3">Gedung</dt>
                <dd class="col-md-9">
                    <?= esc($location['building'] ?? '-') ?>
                </dd>

                <dt class="col-md-3">Lantai</dt>
                <dd class="col-md-9">
                    <?= esc($location['floor'] ?? '-') ?>
                </dd>

                <dt class="col-md-3">Ruangan</dt>
                <dd class="col-md-9">
                    <?= esc($location['room'] ?? '-') ?>
                </dd>

                <dt class="col-md-3">Deskripsi</dt>
                <dd class="col-md-9">
                    <?= esc($location['description'] ?? '-') ?>
                </dd>

                <dt class="col-md-3">Status</dt>
                <dd class="col-md-9">

                    <?php if ($location['is_active']): ?>

                        <span class="badge bg-success">
                            Aktif
                        </span>

                    <?php else: ?>

                        <span class="badge bg-secondary">
                            Tidak Aktif
                        </span>

                    <?php endif; ?>

                </dd>

            </dl>

        </div>

    </div>

    <div class="card shadow-sm mt-4">

        <div class="card-body">

            <h5 class="mb-3">
                Unit yang Terkait
            </h5>

            <?php if (empty($units)): ?>

                <p class="text-muted mb-0">
                    Belum ada unit yang terkait dengan lokasi ini.
                </p>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Unit / Departemen</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($units as $index => $unit): ?>

                                <tr>
                                    <td>
                                        <?= $index + 1 ?>
                                    </td>

                                    <td>
                                        <?= esc($unit['code']) ?>
                                    </td>

                                    <td>
                                        <?= esc($unit['name']) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>
    </div>
    <div class="card shadow-sm mt-4">

        <div class="card-body">

            <h5 class="mb-3">
                Aset di Lokasi Ini
            </h5>

            <?php if (empty($assets)): ?>

                <p class="text-muted mb-0">
                    Belum ada aset di lokasi ini.
                </p>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Aset</th>
                                <th>Nama Barang</th>
                                <th>Unit</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($assets as $index => $asset): ?>

                                <tr>

                                    <td>
                                        <?= $index + 1 ?>
                                    </td>

                                    <td>
                                        <?= esc($asset['asset_code']) ?>
                                    </td>

                                    <td>
                                        <?= esc($asset['name']) ?>
                                    </td>

                                    <td>
                                        <?= esc($asset['unit_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= esc($asset['asset_status'] ?? '-') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<div class="modal fade" id="locationPhotoModal" tabindex="-1"><div class="modal-dialog modal-dialog-scrollable"><form class="modal-content" method="post" enctype="multipart/form-data" action="<?= base_url('attachments/photo/location/' . $location['id']) ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Foto Lokasi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input name="caption" class="form-control mb-2" placeholder="Keterangan kondisi/lokasi"><input name="file" type="file" accept="image/*" capture="environment" class="form-control" required></div><div class="modal-footer"><button class="btn btn-primary">Simpan Foto</button></div></form></div></div>

<?= view('layout/footer') ?>
