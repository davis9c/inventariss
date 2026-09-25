<?php

/**
 * Panduan dokumen aset (garansi, bukti pembelian, sertifikat).
 *
 * Angka dan daftar tipe di halaman ini diambil dari
 * Config\AssetDocuments oleh Help::documents(), jadi tidak perlu
 * disinkronkan manual kalau batas atau jenis berkas diubah.
 */

?>

<?= view('layout/header', ['title' => $title]) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

    <h3><?= esc($title) ?></h3>
    <p class="text-muted">
        Dokumen pendukung yang bisa dilampirkan ke setiap barang: bukti
        pembelian, sertifikat, atau surat garansi. Batas dan jenis berkas
        di bawah ini dibaca langsung dari konfigurasi aplikasi.
    </p>

    <div class="card shadow-sm mb-4">
    <div class="card-header">
        <strong>Cara mengunggah</strong>
    </div>
    <div class="card-body">

        <p class="mb-0">
            Buka halaman detail barang, lalu tekan
            <strong>+ Unggah Dokumen</strong> pada kartu
            <strong>Dokumen</strong>. Beberapa berkas bisa diunggah
            sekaligus, dan gambar akan langsung Appear sebagai pratinjau.
        </p>

    </div>
    </div>

    <div class="card shadow-sm mb-4">
    <div class="card-header">
        <strong>Batas dan jenis berkas</strong>
    </div>
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <tbody>
                    <tr>
                        <th scope="row" class="w-25">Jenis berkas</th>
                        <td>
                            <?php foreach ($allowedExts as $ext): ?>
                                <span class="badge text-bg-secondary"><?= esc(strtoupper($ext)) ?></span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Tipe MIME</th>
                        <td>
                            <?php foreach ($allowedMimes as $mime): ?>
                                <code><?= esc($mime) ?></code>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Ukuran maksimum</th>
                        <td><?= esc($maxSizeLabel) ?> per berkas</td>
                    </tr>
                    <tr>
                        <th scope="row">Jumlah dokumen</th>
                        <td><?= esc($maxFiles) ?> dokumen per barang</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="alert alert-warning mt-3" role="alert">
            <strong>Batas server:</strong>
            <code>upload_max_filesize = <?= esc($serverUploadLimit) ?></code>,
            <code>post_max_size = <?= esc($serverPostLimit) ?></code>.
            <p class="mb-0 mt-2">
                Batas inilah yang sebenarnya berlaku. Bila lebih kecil
                daripada <?= esc($maxSizeLabel) ?>, berkas yang lebih besar
                ditolak oleh server sebelum aplikasi sempat memprosesnya.
                Kalau sering menemukan pesan &ldquo;melebihi batas
                server&rdquo;, naikkan kedua nilai tersebut di konfigurasi PHP.
            </p>
        </div>

        <p class="mt-3">
            Ekstensi dan isi berkas keduanya diperiksa. Berkas yang
            ekstensinya <code>.pdf</code>, <code>.jpg</code>, atau
            <code>.png</code> tetapi isinya bukan dokumen/gambar
            tersebut akan ditolak, begitu pula sebaliknya. Format
            Microsoft Office (.doc, .docx, .xls, .xlsx) dan SVG
            sengaja tidak diizinkan: keduanya bisa membawa kode yang
            dieksekusi browser.
        </p>

    </div>
    </div>

    <div class="card shadow-sm mb-4">
    <div class="card-header">
        <strong>Di mana berkas disimpan</strong>
    </div>
    <div class="card-body">

        <p>
            Berkas disimpan di <code>writable/uploads/<?= esc($directory) ?>/</code>,
            yaitu <strong>di luar document root</strong>, dengan nama acak
            yang dibuat server. Nama asli dari komputer Anda tidak pernah
            dipakai sebagai nama berkas di server.
        </p>

        <p class="mb-0">
            Karena lokasi itu tidak bisa diakses lewat URL, setiap berkas
            hanya bisa diunduh melalui aplikasi, yang memeriksa login dan
            hak akses lokasi lebih dulu.
        </p>

    </div>
    </div>

    <div class="card shadow-sm mb-4">
    <div class="card-header">
        <strong>Hak akses</strong>
    </div>
    <div class="card-body">

        <p class="mb-0">
            Unggah, unduh, dan hapus dokumen mengikuti aturan lokasi yang
            sama dengan barangnya: Anda hanya bisa menyentuh dokumen barang
            yang lokasinya Anda punya akses. Menghapus barang sekaligus
            menghapus dokumennya, jadi tidak ada berkas tertinggal.
        </p>

    </div>
    </div>

    <div class="card shadow-sm mb-4">
    <div class="card-header">
        <strong>Gambar barang</strong>
    </div>
    <div class="card-body">

        <p>
            Selain dokumen, barang (aset maupun barang stok) bisa diberi
            gambar untuk memudahkan identifikasi saat memilih atau
            memindahkan barang. Setiap gambar punya <strong>judul</strong>
            dan <strong>deskripsi</strong> opsional, seperti dokumen.
        </p>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <tbody>
                    <tr>
                        <th scope="row" class="w-25">Jenis berkas</th>
                        <td>
                            <?php foreach ($imgExts as $ext): ?>
                                <span class="badge text-bg-secondary"><?= esc(strtoupper($ext)) ?></span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Jumlah per barang</th>
                        <td><?= esc((string) $imgMaxImages) ?> gambar</td>
                    </tr>
                    <tr>
                        <th scope="row">Ukuran akhir</th>
                        <td><?= esc($imgMaxSizeLabel) ?> per gambar</td>
                    </tr>
                    <tr>
                        <th scope="row">Ukuran berkas asli</th>
                        <td>maksimal <?= esc($imgMaxOriginal) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="alert alert-info mt-3" role="alert">
            <strong>Gambar dikecilkan di browser.</strong>
            <p class="mb-0 mt-2">
                Sebelum dikirim, gambar diperkecil sehingga sisi terpanjang
                maksimal <?= esc((string) $imgMaxDimension) ?> piksel dan
                hasilnya disimpan sebagai JPEG. Ini membuat halaman daftar
                tetap cepat meskipun foto aslinya besar, dan hasilnya sama
                di server mana pun. Berkas PNG yang transparan akan
                kehilangan transparansinya karena latar dibuat putih.
            </p>
        </div>

        <p class="mb-0">
            Berkas gambar disimpan di
            <code>writable/uploads/<?= esc($imgDirectory) ?>/</code>, juga
            di luar document root, dan hanya bisa diakses lewat aplikasi
            setelah hak akses lokasi diperiksa. Foto pertama yang diunggah
            otomatis dipakai sebagai thumbnail di daftar barang.
        </p>

    </div>
    </div>

    <div class="card shadow-sm mb-4">
    <div class="card-header">
        <strong>Lampiran catatan pergerakan</strong>
    </div>
    <div class="card-body">

        <p>
            Setiap catatan di riwayat pergerakan (stok masuk, keluar, pindah,
            penyesuaian, dan mutasi aset) juga bisa dilampiri dokumen dan
            gambar. Buka halaman detail transaksinya, lalu gunakan kartu
            <strong>Dokumen</strong> dan <strong>Gambar</strong> di halaman
            tersebut. Setiap lampiran punya <strong>judul</strong> dan
            <strong>deskripsi</strong> opsional, sama seperti lampiran barang.
        </p>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <tbody>
                    <tr>
                        <th scope="row" class="w-25">Dokumen</th>
                        <td>
                            <?php foreach ($allowedExts as $ext): ?>
                                <span class="badge text-bg-secondary"><?= esc(strtoupper($ext)) ?></span>
                            <?php endforeach; ?>,
                            maksimal <?= esc((string) $maxFiles) ?> per catatan,
                            <?= esc($maxSizeLabel) ?> per berkas
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Gambar</th>
                        <td>
                            JPG atau PNG, maksimal
                            <?= esc((string) $maxFiles) ?> per catatan,
                            <?= esc($imgMaxSizeLabel) ?> per gambar
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mb-0">
            Hak aksesnya mengikuti aturan catatanGerakannya sendiri: satu
            transaksi bisa berjalan dari lokasi A ke lokasi B, dan Anda boleh
            melihat lampirannya kalau punya akses ke salah satunya. Unggah,
            unduh, dan hapus tersedia dari enam endpoint yang kesemuanya
            memakai aturan yang sama persis, jadi tidak ada celah di mana
            lampiran lebih longgar daripada transaksinya.
        </p>

    </div>
    </div>

</div>

<?= view('layout/footer') ?>
