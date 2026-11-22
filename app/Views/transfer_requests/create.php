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
    }
    itemType.addEventListener('change', toggleType);
    toggleType();

    function loadItems() {
        const locId = sourceLoc.value;
        if (!locId) return;
        const isStock = itemType.value === 'Barang Stok';
        const url = isStock
            ? baseUrl + 'transfer-requests/stock-by-location/' + locId
            : baseUrl + 'transfer-requests/assets-by-location/' + locId;

        fetch(url, { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                const sel = isStock ? stockSelect : assetSelect;
                sel.innerHTML = '<option value="">-- Pilih --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    if (isStock) {
                        opt.textContent = item.item_code + ' - ' + item.name + ' (stok: ' + item.quantity + ' ' + item.satuan + ')';
                        opt.dataset.qty = item.quantity;
                    } else {
                        opt.textContent = item.asset_code + ' - ' + item.name;
                    }
                    sel.appendChild(opt);
                });
                if (window.initSelect2) window.initSelect2(sel.parentElement || document);
            });
    }
    sourceLoc.addEventListener('change', loadItems);
    itemType.addEventListener('change', loadItems);

    stockSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.dataset.qty) {
            qtyInput.max = opt.dataset.qty;
        }
    });
})();
</script>

<?= view('layout/footer') ?>