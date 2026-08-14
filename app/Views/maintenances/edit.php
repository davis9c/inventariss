<?= view('layout/header', ['title' => 'Edit Maintenance']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Edit Maintenance</h3>

    <div class="card shadow-sm mt-4">

        <div class="card-body">

            <form method="post"
                  action="<?= base_url('maintenances/update/' . $maintenance['id']) ?>">

                <?= csrf_field() ?>

                <div class="mb-3">

                    <label class="form-label">
                        Barang
                    </label>

                    <select name="asset_id"
                            class="form-select"
                            required>

                        <?php foreach ($assets as $asset): ?>

                            <option value="<?= $asset['id'] ?>"
                                <?= $asset['id'] == $maintenance['asset_id'] ? 'selected' : '' ?>>

                                <?= esc($asset['asset_code']) ?>
                                -
                                <?= esc($asset['name']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Tanggal
                        </label>

                        <input type="date"
                               name="maintenance_date"
                               class="form-control"
                               value="<?= esc($maintenance['maintenance_date']) ?>"
                               required>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Jenis
                        </label>

                        <select name="maintenance_type"
                                class="form-select">

                            <?php foreach ([
                                'Preventive',
                                'Corrective',
                                'Inspection'
                            ] as $type): ?>

                                <option value="<?= $type ?>"
                                    <?= $maintenance['maintenance_type'] === $type ? 'selected' : '' ?>>

                                    <?= $type ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Masalah / Keluhan
                    </label>

                    <textarea name="problem"
                              class="form-control"
                              rows="3"><?= esc($maintenance['problem']) ?></textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Tindakan / Perbaikan
                    </label>

                    <textarea name="action_taken"
                              class="form-control"
                              rows="3"><?= esc($maintenance['action_taken']) ?></textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Teknisi
                    </label>

                    <select name="technician_type"
                            class="form-select">

                        <option value="Internal"
                            <?= $maintenance['technician_type'] === 'Internal' ? 'selected' : '' ?>>
                            Teknisi Internal
                        </option>

                        <option value="External"
                            <?= $maintenance['technician_type'] === 'External' ? 'selected' : '' ?>>
                            Vendor / Teknisi External
                        </option>

                    </select>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Nama Teknisi
                    </label>

                    <input type="text"
                           name="technician_name"
                           class="form-control"
                           value="<?= esc($maintenance['technician_name']) ?>">

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Nama Vendor
                    </label>

                    <input type="text"
                           name="vendor_name"
                           class="form-control"
                           value="<?= esc($maintenance['vendor_name']) ?>">

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select name="status"
                                class="form-select">

                            <?php foreach ([
                                'Diajukan',
                                'Diproses',
                                'Selesai',
                                'Dibatalkan'
                            ] as $status): ?>

                                <option value="<?= $status ?>"
                                    <?= $maintenance['status'] === $status ? 'selected' : '' ?>>

                                    <?= $status ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Biaya
                        </label>

                        <input type="number"
                               name="cost"
                               class="form-control"
                               value="<?= esc($maintenance['cost']) ?>">

                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Tanggal Selesai
                    </label>

                    <input type="date"
                           name="completed_date"
                           class="form-control"
                           value="<?= esc($maintenance['completed_date']) ?>">

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Catatan
                    </label>

                    <textarea name="notes"
                              class="form-control"
                              rows="3"><?= esc($maintenance['notes']) ?></textarea>

                </div>

                <a href="<?= base_url('maintenances') ?>"
                   class="btn btn-secondary">

                    Kembali

                </a>

                <button type="submit"
                        class="btn btn-primary">

                    Update

                </button>

            </form>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>