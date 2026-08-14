<?= view('layout/header', ['title' => 'Detail Stock Opname']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3>
                <?= esc($opname['opname_code']) ?>
            </h3>

            <p class="text-muted mb-0">

                Tanggal:
                <?= esc($opname['opname_date']) ?>

                |

                Lokasi:

                <?php if ($opname['location_id']): ?>

                    <?= esc($opname['building']) ?>
                    -
                    <?= esc($opname['room']) ?>

                <?php else: ?>

                    Semua lokasi

                <?php endif; ?>

            </p>

        </div>

        <div>

            <?php if ($opname['status'] === 'Draft'): ?>

                <form method="post"
                      action="<?= base_url('stock-opnames/' . $opname['id'] . '/finish')?>"
                      class="d-inline"
                      onsubmit="return confirm('Selesaikan stock opname ini?')">

                    <?= csrf_field() ?>

                    <button type="submit"
                            class="btn btn-success">

                        Selesaikan Opname

                    </button>

                </form>

            <?php endif; ?>

        </div>

    </div>

    <?php if (session()->getFlashdata('success')): ?>

        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>#</th>
                            <th>Barang</th>
                            <th>Serial Number</th>
                            <th>Ditemukan</th>
                            <th>Kondisi</th>
                            <th>Catatan</th>
                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($details as $i => $detail): ?>

                        <tr>

                            <form method="post"
                                  action="<?= base_url('stock-opnames/detail/' . $detail['id'] . '/update') ?>">

                                <?= csrf_field() ?>

                                <td>
                                    <?= $i + 1 ?>
                                </td>

                                <td>

                                    <strong>
                                        <?= esc($detail['asset_name']) ?>
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        <?= esc($detail['asset_code']) ?>
                                    </small>

                                </td>

                                <td>
                                    <?= esc($detail['serial_number'] ?? '-') ?>
                                </td>

                                <td>

                                    <input type="checkbox"
                                           name="is_found"
                                           value="1"
                                           class="form-check-input"
                                           <?= $detail['is_found'] ? 'checked' : '' ?>
                                           <?= $opname['status'] === 'Selesai' ? 'disabled' : '' ?>>

                                </td>

                                <td>

                                    <select name="condition_status"
                                            class="form-select form-select-sm"
                                            <?= $opname['status'] === 'Selesai' ? 'disabled' : '' ?>>

                                        <option value="Baik"
                                            <?= $detail['condition_status'] === 'Baik' ? 'selected' : '' ?>>
                                            Baik
                                        </option>

                                        <option value="Rusak Ringan"
                                            <?= $detail['condition_status'] === 'Rusak Ringan' ? 'selected' : '' ?>>
                                            Rusak Ringan
                                        </option>

                                        <option value="Rusak Berat"
                                            <?= $detail['condition_status'] === 'Rusak Berat' ? 'selected' : '' ?>>
                                            Rusak Berat
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <input type="text"
                                           name="notes"
                                           class="form-control form-control-sm"
                                           value="<?= esc($detail['notes'] ?? '') ?>"
                                           <?= $opname['status'] === 'Selesai' ? 'disabled' : '' ?>>

                                </td>

                                <td>

                                    <?php if ($opname['status'] === 'Draft'): ?>

                                        <button type="submit"
                                                class="btn btn-sm btn-primary">

                                            Simpan

                                        </button>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Terkunci
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </form>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>