<?php

/**
 * Kartu "Gambar" untuk halaman detail barang.
 *
 * Dipakai oleh assets/show.php dan stock_items/show.php. Semua variabel
 * di sini sudah disiapkan controller:
 *
 *   $images       array<int, array> baris item_images milik barang ini
 *   $imageConfig  Config\ItemImages
 *   $imageBase    'assets' atau 'stock-items' (segmen path route)
 *   $imageOwner   'Barang' atau 'Barang stok' (untuk pesan)
 *
 * Jumlah tombol tambah sengaja disembunyikan setelah batas tercapai,
 * supaya pengguna tidak mengunggah sesuatu yang pasti ditolak.
 */

$imageBase    = $imageBase    ?? 'assets';
$imageOwner   = $imageOwner   ?? 'Barang';
$imageLimit   = (int) ($imageConfig->maxImages ?? 5);
$imageCount   = is_array($images) ? count($images) : 0;
$imageFree    = max(0, $imageLimit - $imageCount);
?>

<div class="card shadow-sm mb-4" id="gambarCard">

    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Gambar</strong>
        <?php if ($imageFree > 0): ?>
            <button type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#uploadGambarModal">
                + Tambah Gambar
            </button>
        <?php endif; ?>
    </div>

    <div class="card-body">

        <?php if ($imageCount === 0): ?>

            <div class="text-muted">
                Belum ada gambar. Tambahkan foto barang agar lebih mudah
                dikenali saat memilih atau memindahkan barang.
            </div>

        <?php else: ?>

            <div class="row g-3">
                <?php foreach ($images as $img): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="border rounded p-2 h-100 d-flex flex-column">
                            <img src="<?= base_url($imageBase . '/images/' . (int) $img['id']) ?>"
                                 class="img-fluid rounded mb-2 w-100"
                                 alt="<?= esc($img['title']) ?>"
                                 loading="lazy">
                            <div class="mb-1">
                                <span class="d-block small fw-semibold">
                                    <?= esc($img['title']) ?>
                                </span>
                            </div>
                            <?php if (! empty($img['description'])): ?>
                                <div class="small text-muted mb-2">
                                    <?= esc($img['description']) ?>
                                </div>
                            <?php else: ?>
                                <div class="mb-2"></div>
                            <?php endif; ?>
                            <div class="text-muted small mb-2">
                                <?= esc(document_size_label((int) $img['size_bytes'])) ?>
                                <?php if (! empty($img['uploader_name'])): ?>
                                    &middot; <?= esc($img['uploader_name']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="mt-auto d-flex gap-2">
                                <a href="<?= base_url($imageBase . '/images/' . (int) $img['id']) ?>"
                                   class="btn btn-sm btn-outline-primary"
                                   target="_blank"
                                   rel="noopener">Lihat</a>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-hapus-gambar"
                                        data-id="<?= (int) $img['id'] ?>"
                                        data-title="<?= esc($img['title']) ?>">Hapus</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <?php if ($imageCount > 0): ?>
            <div class="text-muted small mt-3">
                <?= esc((string) $imageCount) ?> dari <?= esc((string) $imageLimit) ?> gambar terpakai.
            </div>
        <?php endif; ?>

    </div>
</div>
