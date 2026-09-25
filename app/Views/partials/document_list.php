<?php

/**
 * Kartu "Dokumen" untuk halaman detail.
 *
 * Dipakai oleh assets/show.php (dokumen aset) dan
 * stock_movements/show.php (dokumen catatan pergerakan). Semuanya punya
 * bentuk yang sama -- judul, deskripsi, nama berkas, ukuran, pengunggah --
 * jadi diekstrak supaya tidak ada tiga salinan logika yang bisa berbeda.
 *
 * Variabel yang dibutuhkan:
 *
 *   $documents      array<int, array> baris lampiran, sudah berisi uploader_name
 *   $documentConfig Config\AssetDocuments atau Config\TransactionDocuments
 *   $documentBase   'assets' atau 'stock-movements' (segmen path route)
 *   $documentOwner  'Barang' atau 'Catatan pergerakan' (untuk pesan)
 */

$documentBase   = $documentBase   ?? 'assets';
$documentOwner  = $documentOwner  ?? 'Barang';
$documentLimit  = (int) ($documentConfig->maxFiles ?? 5);
$documentCount  = is_array($documents) ? count($documents) : 0;
?>

<div class="card shadow-sm mb-4" id="dokumenCard">

    <div class="card-header d-flex justify-content-between align-items-center">

        <strong>Dokumen <?= esc($documentOwner) ?></strong>

        <?php if ($documentCount < $documentLimit): ?>
            <button type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#uploadDokumenModal">
                + Unggah Dokumen
            </button>
        <?php endif; ?>

    </div>

    <div class="card-body">

        <div id="dokumenList">

            <?php if ($documentCount === 0): ?>

                <div class="text-muted">
                    Belum ada dokumen terlampir. Unggah bukti pembelian,
                    sertifikat, surat jalan, atau dokumen garansi di sini.
                </div>

            <?php else: ?>

                <div class="row g-3">

                    <?php foreach ($documents as $doc): ?>
                        <?php
                        $isImage = in_array($doc['mime_type'], ['image/jpeg', 'image/png'], true);
                        $badge   = match ($doc['extension']) {
                            'pdf'         => ['PDF', 'text-bg-danger'],
                            'jpg', 'jpeg', 'png' => ['GAMBAR', 'text-bg-primary'],
                            default       => [strtoupper($doc['extension']), 'text-bg-secondary'],
                        };
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded p-3 h-100 d-flex flex-column">

                                <?php if ($isImage): ?>
                                    <img src="<?= base_url($documentBase . '/documents/' . (int) $doc['id']) ?>"
                                         class="img-fluid rounded mb-2 w-100"
                                         alt="<?= esc($doc['title']) ?>"
                                         loading="lazy">
                                <?php endif; ?>

                                <div class="mb-2">
                                    <span class="badge <?= esc($badge[1]) ?>"><?= esc($badge[0]) ?></span>
                                    <span class="d-block fw-semibold small"><?= esc($doc['title']) ?></span>
                                    <span class="text-muted small"><?= esc($doc['original_name']) ?></span>
                                </div>

                                <?php if (! empty($doc['description'])): ?>
                                    <div class="small text-muted mb-2">
                                        <?= esc($doc['description']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="text-muted small mb-2">
                                    <?= esc(document_size_label((int) $doc['size_bytes'])) ?>
                                    &middot; <?= esc($doc['uploader_name'] ?? '-') ?>
                                    <?php if (! empty($doc['created_at'])): ?>
                                        &middot; <?= esc($doc['created_at']) ?>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-auto d-flex gap-2">
                                    <a href="<?= base_url($documentBase . '/documents/' . (int) $doc['id']) ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       target="_blank"
                                       rel="noopener">
                                        Lihat
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete-dokumen"
                                            data-id="<?= (int) $doc['id'] ?>"
                                            data-name="<?= esc($doc['title']) ?>">
                                        Hapus
                                    </button>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

        <?php if ($documentCount > 0): ?>
            <div class="text-muted small mt-3">
                <?= esc((string) $documentCount) ?> dari
                <?= esc((string) $documentLimit) ?> dokumen terpakai.
            </div>
        <?php endif; ?>

    </div>
</div>
