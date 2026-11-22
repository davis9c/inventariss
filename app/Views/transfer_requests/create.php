<?= view('layout/header', ['title' => 'Buat Permintaan Mutasi']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">
    <h3>Buat Permintaan Mutasi</h3>
    <p class="text-muted">Minta barang dari lokasi lain (permintaan) — pengelola sumber akan menyetujui.</p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="<?= base_url('transfer-requests/store') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Jenis Barang</label>
                        <select name="item_type" id="itemType" class="form-select" required>
                            <option value="Barang Stok">Barang Stok</option>
                            <option value="Aset">Aset</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Lokasi Sumber</label>
                        <select name="from_location_id" id="sourceLoc" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($sourceLocations as $l): ?>
                                <option value="<?= $l['id'] ?>"><?= esc($l['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Lokasi Tujuan (milik Anda)</label>
                        <select name="to_location_id" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($myLocations as $l): ?>
                                <option value="<?= $l['id'] ?>"><?= esc($l['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Barang</label>
                        <select name="stock_item_id" id="stockSelect" class="form-select">
                            <option value="">-- Pilih lokasi sumber dulu --</option>
                        </select>
                        <select name="asset_id" id="assetSelect" class="form-select d-none">
                            <option value="">-- Pilih lokasi sumber dulu --</option>
                        </select>
                    </div>

                    <div class="col-md-2" id="qtyWrap">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" id="qtyInput" class="form-control" min="1" value="1" required>
                    </div>

                    <div class="col-md-4" id="unitWrap">
                        <label class="form-label">Unit Tujuan (opsional)</label>
                        <select name="to_unit_id" class="form-select">
                            <option value="">-- Pilih --</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Alasan</label>
                        <input type="text" name="reason" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Bukti (opsional)</label>
                        <input type="file" name="evidence" class="form-control" accept="image/*,application/pdf">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                    <a href="<?= base_url('transfer-requests') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    const baseUrl = '<?= base_url() ?>';
    const itemType = document.getElementById('itemType');
    const sourceLoc = document.getElementById('sourceLoc');
    const stockSelect = document.getElementById('stockSelect');
    const assetSelect = document.getElementById('assetSelect');
    const qtyWrap = document.getElementById('qtyWrap');
    const qtyInput = document.getElementById('qtyInput');

    function toggleType() {
        const isStock = itemType.value === 'Barang Stok';
        stockSelect.classList.toggle('d-none', !isStock);
        stockSelect.disabled = !isStock;
        assetSelect.classList.toggle('d-none', isStock);
        assetSelect.disabled = isStock;
        qtyWrap.classList.toggle('d-none', !isStock);
        if (isStock) {
            assetSelect.name = 'asset_id_disabled';
            stockSelect.name = 'stock_item_id';
        } else {
            stockSelect.name = 'stock_item_id_disabled';
            assetSelect.name = 'asset_id';
        }
        reinitSelects();
    }
    itemType.addEventListener('change', toggleType);
    toggleType();

    function ajaxUrlFor(isStock) {
        const locId = sourceLoc.value || '';
        const base = isStock ? 'autocomplete/stock-items' : 'autocomplete/assets';
        return baseUrl + base + '?location_id=' + encodeURIComponent(locId);
    }

    function reinitSelects() {
        if (typeof jQuery === 'undefined' || !jQuery.fn.select2) return;
        const $ = jQuery;
        const isStock = itemType.value === 'Barang Stok';
        const activeSel = isStock ? stockSelect : assetSelect;
        const otherSel = isStock ? assetSelect : stockSelect;

        // destroy both
        [stockSelect, assetSelect].forEach(function (sel) {
            if ($(sel).data('select2')) {
                $(sel).select2('destroy');
            }
            sel.innerHTML = '';
        });

        $(activeSel).select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Ketik untuk mencari...',
            allowClear: true,
            ajax: {
                url: function () { return ajaxUrlFor(isStock); },
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term || '' }; },
                processResults: function (data) { return { results: data }; },
                cache: true
            },
            minimumInputLength: 0
        });

        // simpan quantity saat pilih (untuk stok)
        if (isStock) {
            $(stockSelect).on('select2:select', function (e) {
                const data = e.params.data;
                if (data && data.quantity) {
                    qtyInput.max = data.quantity;
                    qtyInput.value = 1;
                }
            });
        }
    }

    sourceLoc.addEventListener('change', function () {
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            const isStock = itemType.value === 'Barang Stok';
            const activeSel = isStock ? stockSelect : assetSelect;
            if (jQuery(activeSel).data('select2')) {
                jQuery(activeSel).select2('destroy');
            }
            activeSel.innerHTML = '';
            reinitSelects();
        }
    });

    reinitSelects();
})();
</script>

<?= view('layout/footer') ?>