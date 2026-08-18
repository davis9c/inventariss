# INVENTARIS — Sistem Inventaris

> [!WARNING]
> Versi ini (branch **V2**) **belum diuji** (belum testing). Sebaiknya jangan digunakan untuk produksi sebelum pengujian selesai.

Aplikasi web pengelolaan **inventaris** berbasis [CodeIgniter 4](https://codeigniter.com) (PHP 8.2+ / MySQL) dengan antarmuka berbahasa Indonesia. Mencakup pencatatan aset tetap, barang stok, mutasi, stock opname, audit trail, dan manajemen pengguna multi-role.

## Fitur

- **Aset & Barang Stok** — catat aset dan barang stok dengan kategori, unit, lokasi, tahun perolehan, dan harga.
- **Mutasi & Riwayat** — pemindahan, peminjaman, barang keluar, pengembalian, serta stok masuk-keluar dengan riwayat lengkap.
- **Stock Opname** — periksa aset dan stok berkala, catat temuan, dan hitung selisih otomatis.
- **Laporan** — ringkasan aset, stok, mutasi, dan opname dengan filter.
- **Audit Trail** — setiap perubahan tercatat otomatis (siapa, kapan, apa yang berubah).
- **Multi-Role** — 6 role: Super Admin, Admin Inventaris, Petugas Inventaris, PIC Unit, Manajemen, dan Auditor.
- **Wizard Instalasi** — pemasangan otomatis (migrasi + data awal + akun Super Admin) melalui browser, tanpa perintah `spark` manual.

## Kebutuhan Sistem

- PHP **8.2** atau lebih baru dengan ekstensi: `mysqlnd`, `mbstring`, `intl`, `curl`, `json`.
- MySQL **5.7** atau **8.0** (disarankan 8.0).
- Composer (untuk memasang dependency).

## Panduan Instalasi

### 1. Siapkan file konfigurasi

Salin template environment lalu sesuaikan `baseURL` dan kredensial database:

```bash
cp env .env
```

Edit `.env`, minimal bagian berikut:

```ini
#--------------------------------------------------------------------
# KONFIGURASI WEB
#--------------------------------------------------------------------
app.baseURL = 'http://localhost:9003/'

#--------------------------------------------------------------------
# KONFIGURASI DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = db_inventori
database.default.username = inv
database.default.password = rahasia
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

### 2. Buat database dan user MySQL

Jalankan perintah SQL berikut sebagai user MySQL dengan hak admin (mis. `root`) agar aplikasi berjalan optimal:

```sql
-- Buat database dengan dukungan karakter lengkap (utf8mb4)
CREATE DATABASE IF NOT EXISTS db_inventori
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Buat user aplikasi (ganti rahasia dengan kata sandi kuat)
CREATE USER IF NOT EXISTS 'inv'@'localhost' IDENTIFIED BY 'rahasia';

-- Beri seluruh hak akses khusus untuk database aplikasi
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX,
      REFERENCES, TRIGGER, LOCK TABLES
  ON db_inventori.*
  TO 'inv'@'localhost';

FLUSH PRIVILEGES;
```

Catatan:

- Jika database berada di server terpisah, ganti host pada user, misalnya `'inv'@'10.10.10.%'`, dan set `database.default.hostname` ke IP server tersebut.
- MySQL 8.0 menggunakan autentikasi `caching_sha2_password` secara bawaan; didukung penuh oleh ekstensi PHP `mysqlnd`. Jika terkendala, gunakan `IDENTIFIED WITH mysql_native_password BY 'rahasia'`.
- Tetapkan **hak akses hanya pada database aplikasi** (`db_inventori.*`), bukan seluruh server.

### 3. Pasang dependency

```bash
composer install
```

### 4. Instalasi otomatis (disarankan)

1. Jalankan server: `php spark serve --port 9003`.
2. Buka root URL aplikasi di browser (contoh: `http://localhost:9003/`).
3. Halaman *landing page* akan menampilkan tombol **Mulai Instalasi**.
4. Wizard akan:
   - memeriksa status koneksi database dan struktur tabel;
   - menjalankan seluruh migrasi otomatis;
   - mengisi data awal (role, permission, lokasi, unit, kategori);
   - membuat akun **Super Admin** (username dapat diubah).
5. Setelah selesai, masuk ke halaman login dengan akun Super Admin tersebut.

> Aplikasi otomatis memindahkan Anda ke `/setup` bila sistem belum terpasang (belum ada tabel/akun).

### 5. Cara manual (opsional)

Jika ingin menjalankan migrasi dan seed melalui CLI:

```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

Akun Super Admin pertama tetap dibuat melalui wizard di browser (halaman `/setup`), atau buat langsung di tabel `users` + `user_roles`.

### 6. Menjalankan aplikasi

**Pengembangan (lokal):**

```bash
php spark serve --port 9003
# buka http://localhost:9003/
```

**Produksi:**

- Arahkan *document root* web server (Apache/Nginx) ke folder `public/` dari proyek ini, **bukan** ke root proyek.
- Pastikan folder `writable/` dapat ditulis oleh web server.
- Atur `app.baseURL` sesuai domain, mis. `https://inventaris.ademayem.my.id/`.

## Data Awal Hasil Instalasi

| Jenis | Isi |
|---|---|
| Role (6) | Super Admin, Admin Inventaris, Petugas Inventaris, PIC Unit, Manajemen, Auditor |
| Permission (5) | `maintenance.view`, `maintenance.create`, `maintenance.update`, `maintenance.delete`, `maintenance.approve` |
| Lokasi (2) | Kantor Pusat, Kantor Cabang |
| Unit (10) | Satuan kerja/pengguna di bawah lokasi |
| Kategori (4) | Perangkat Komputer, Perabotan & Sarana, Keamanan, Peralatan IT & Jaringan |

## Reset Pabrik Data

Reset mengembalikan aplikasi ke kondisi seperti baru diinstal: seluruh data transaksi, aset, stok, dan akun dihapus.

### Cara 1 — Hapus database (cepat, disarankan)

```sql
-- Hentikan penggunaan DB dulu, lalu:
DROP DATABASE IF EXISTS db_inventori;

-- Buat ulang dengan charset yang sama
CREATE DATABASE db_inventori
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Ulangi GRANT bila user dibuat khusus untuk database ini
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX,
      REFERENCES, TRIGGER, LOCK TABLES
  ON db_inventori.*
  TO 'inv'@'localhost';
FLUSH PRIVILEGES;
```

Kemudian buka root URL aplikasi → halaman landing menampilkan **Mulai Instalasi** → ikuti wizard. Aplikasi akan membuat struktur tabel dan data awal dari nol.

### Cara 2 — Reset struktur tanpa menghapus database

```bash
# Hapus semua tabel dan jalankan migrasi ulang (data hilang, struktur baru)
php spark migrate:refresh
```

Setelah itu, buka wizard di browser (`/setup`) untuk mengisi data awal dan membuat akun Super Admin baru.

> Peringatan: kedua cara **menghapus seluruh data** (aset, stok, mutasi, opname, pengguna). Pastikan telah membackup data bila diperlukan: `mysqldump -u inv -p db_inventori > backup.sql`.

## Struktur Proyek

```
app/
  Controllers/        Logika aplikasi (Assets, StockItems, Mutations, Auth, Landing, Setup, ...)
  Database/
    Migrations/       Struktur tabel (6 migrasi)
    Seeds/            Data awal (DatabaseSeeder, AssetSeeder, StockOpnameSeeder)
  Models/             Akses data
  Views/              Tampilan (layout, landing, setup, auth, modul)
  Helpers/            Fungsi bantu (auth, permission, location, option, setup)
public/               Document root web server (index.php, vendor Bootstrap)
writable/             Cache, log, session (harus writable)
env                   Template konfigurasi -> salin menjadi .env
```

## Keamanan

- Jangan mengekspos folder root proyek; arahkan web server hanya ke `public/`.
- Jangan commit `.env` berisi kredensial nyata.
- Gunakan kata sandi kuat untuk user database dan akun Super Admin.
