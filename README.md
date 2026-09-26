# Sistem Inventaris

Sistem manajemen inventaris berbasis web dengan integrasi UserGate untuk autentikasi. Dibangun dengan CodeIgniter 4.7, Bootstrap 5, SBAdmin2, dan DataTables.

## Fitur Utama

- **Autentikasi via UserGate** — Login menggunakan akun UserGate, token refresh otomatis
- **Manajemen Barang/Aset** — CRUD lengkap dengan kode barang, kategori, unit, lokasi
- **Barang Stok** — Kelola barang non-identitas dengan stock in/out/transfer/adjustment
- **Mutasi Aset** — Pencatatan perpindahan aset antar lokasi
- **Stock Opname** — Pemeriksaan fisik barang dengan pencatatan selisih
- **Laporan** — Laporan inventaris dengan filter lokasi, kategori, kondisi, status
- **Manajemen User** — CRUD user dengan role dan lokasi
- **Manajemen Role** — Setup role dan permission
- **UI SBAdmin2** — Tampilan profesional dengan sidebar, topbar, DataTables

## Teknologi

| Komponen | Versi |
|----------|-------|
| PHP | 8.2+ |
| CodeIgniter | 4.7 |
| MySQL | 5.7+ / 8.0+ |
| Bootstrap | 5.3 |
| SBAdmin2 | 4.1.4 |
| DataTables | 1.13.7 |
| Font Awesome | 6.5.1 |
| jQuery | 3.7.1 |
| UserGate API | v1 |

---

## Instalasi untuk Pengembangan

Untuk development, aplikasi jalan langsung di host tanpa Docker. Untuk
production, pakai [Docker](#instalasi-dengan-docker) -- lebih sedikit
perbedaan konfigurasi antar lingkungan.

### Prasyarat

- PHP 8.2+ dengan extensi: `intl`, `mbstring`, `mysqlnd`, `curl`, `json`, `gd`, `zip`
- MySQL 5.7+ atau 8.0+ (boleh di luar mesin ini)
- Composer
- UserGate yang bisa dijangkau dari host

### Langkah-langkah

```bash
# 1. Clone repository
git clone <repository-url> inventaris
cd inventaris

# 2. Install dependencies
composer install

# 3. Siapkan konfigurasi
cp .env.example .env
```

Edit `.env` -- hanya nilai bertanda `[PER MESIN]` yang wajib disesuaikan:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8092/'

database.default.hostname = 10.10.10.12
database.default.database = inventaris
database.default.username = root
database.default.password = <password>
database.default.DBDriver = MySQLi
database.default.port = 3306

usergate.url = 'http://localhost:8091/api/v1'
usergate.api_key = <api-key>
```

Di host saja, `localhost` untuk UserGate **boleh** dipakai karena tidak ada
container yang menghalangi. Tapi kalau `.env` yang sama nanti dipakai container
sekaligus -- dan `docker-compose.yml` memang me-*bind-mount* repo ini, jadi
`php -S` dan container membaca file yang sama -- maka `localhost` akan berarti
"container itu sendiri" dan login akan gagal. Kalau kedua cara dipakai di satu
mesin, pakai alamat IP supaya bisa dijangkau dari keduanya.

```bash
# 4. Buat database, kalau belum ada
mysql -u root -p -e "CREATE DATABASE inventaris CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# 5. Migrasi & seed
php spark migrate
php spark db:seed DatabaseSeeder

# 6. Pastikan writable bisa ditulis
chmod -R 775 writable/

# 7. Jalankan server
php spark serve --port 8092
```

Buka `http://localhost:8092` di browser.

### Login Pertama Kali

1. Buka `http://localhost:8092`
2. Halaman Setup akan muncul jika tabel `users` belum ada
3. Login dengan akun UserGate
4. **User pertama yang login otomatis menjadi Super Admin**

---

## Instalasi dengan Docker

Ini cara install untuk production. Database **tidak** dijalankan oleh Docker --
MySQL ada di luar, dan `.env` yang menunjuk ke sana.

### Prasyarat

- Docker 23+ (butuh BuildKit, karena `Dockerfile` memakai heredoc)
- Docker Compose v2.24+ (contoh override port di bawah memakai tag `!override`)
- MySQL yang sudah bisa dijangkau dari host Docker
- UserGate yang sudah bisa dijangkau dari host Docker

### File yang berperan

Hanya tiga, semuanya di root project:

| File | Isinya |
|---|---|
| `Dockerfile` | Image: ekstensi PHP, Apache, dan entrypoint. Script entrypoint ditulis inline, jadi tidak ada folder `docker/` |
| `docker-compose.yml` | Service, port, volume, dan satu-satunya override: `CI_ENVIRONMENT` |
| `.env` | Seluruh konfigurasi aplikasi. Di-*bind-mount* ke dalam container |

### Langkah-langkah

```bash
# 1. Clone repository
git clone <repository-url> inventaris
cd inventaris

# 2. Siapkan konfigurasi
cp .env.example .env
```

Edit `.env`. Yang **wajib** disesuaikan:

```ini
# URL dasar aplikasi. Harus benar untuk mesin ini -- tidak ada auto-deteksi
# host, dan nilai ini membangun action form login serta redirect.
app.baseURL = 'https://inventaris.domain.co.id/'

# MySQL di luar Docker. Pastikan host ini bisa dijangkau dari container.
database.default.hostname = 10.10.10.12
database.default.database = inventaris
database.default.username = inv
database.default.password = <password>

# UserGate. Jangan pakai localhost: dari dalam container, localhost berarti
# container itu sendiri, sehingga login gagal dengan "Connection refused".
usergate.url = 'http://10.10.10.18:8091/api/v1'
usergate.api_key = <api-key>
```

`CI_ENVIRONMENT` **jangan** diubah di `.env`. Nilainya `development` di sana
untuk server pengembangan, dan `docker-compose.yml` menimpanya dengan
`production` untuk container. Nilai container menang tanpa mekanisme tambahan,
karena PHP mengisi `$_ENV` dari process env sebelum CodeIgniter membaca `.env`.

```bash
# 3. Build & jalankan
docker compose up -d --build
```

Migrasi dan seed **otomatis** dijalankan entrypoint setiap container start.
Keduanya idempoten, jadi restart tidak menghapus dan tidak mengalikan baris.
Tidak ada `chmod` manual juga -- `writable` sudah di-*chown* ke `www-data` saat
build.

Cek log untuk memastikan:

```bash
docker compose logs app
```

Yang diharapkan:

```
[entrypoint] database: 10.10.10.12
[entrypoint] menunggu database (maks 30 detik)
[entrypoint] menjalankan migrasi
Migrations complete.
[entrypoint] mengisi data awal (idempoten)
Seeded: App\Database\Seeds\DatabaseSeeder
```

Kalau muncul `PERINGATAN: ... belum terjangkau`, container tetap jalan dan
Apache tetap melayani, tapi migrate/seed dilewati. Perbaiki
`database.default.*` di `.env`, lalu `docker compose restart app`.

Buka `app.baseURL` di browser.

### Mengubah port

Port host ada di `docker-compose.yml`. Di belakang reverse proxy biasanya
`8092:80` sudah cukup dan proxy yang meneruskan. Kalau perlu diganti, edit satu
baris:

```yaml
ports:
  - "80:80"
```

### Perintah Docker

```bash
# Lihat log
docker compose logs -f app

# Masuk ke container
docker compose exec app bash

# Jalankan spark command
docker compose exec app php spark <command>

# Restart
docker compose restart app

# Rebuild setelah ubah kode
docker compose up -d --build

# Stop
docker compose down

# Stop + hapus volume (MENGHAPUS session, log, dan berkas unggahan)
docker compose down -v
```

Tidak ada volume database, jadi `down -v` tidak menyentuh data. Data MySQL ada
di luar Docker.

### Kalau build gagal

**`RUN <<'SHELL'` ditolak atau build berhenti di situ** -- BuildKit versi lama
tidak mendukung heredoc. Perbarui Docker ke 23+.

**`chmod: cannot access '.../public/vendor'`** -- folder `public/vendor` tidak
ada di checkout. File DataTables di sana harusnya ter-*commit*. Kalau
`git status` menunjukkan `vendor/` ter-*ignore*, pastikan `.gitignore` menulis
`/vendor/` dengan garis miring depan, bukan `vendor/` polos -- yang terakhir juga
menyaring `public/vendor/`.

**`extension ... already loaded`** -- peringatan, bukan error. Build tetap
berhasil.

---

## Struktur Aplikasi

```
inventaris/
├── app/
│   ├── Config/           # Konfigurasi aplikasi
│   │   ├── Routes.php    # Route definitions
│   │   ├── Database.php  # Database config
│   │   └── Filters.php   # Auth & Role filters
│   ├── Controllers/      # Controller classes
│   │   ├── Auth.php      # Login/logout UserGate
│   │   ├── Setup.php     # Setup awal
│   │   ├── Asset.php     # Manajemen aset
│   │   ├── StockItem.php # Barang stok
│   │   ├── StockOpname.php # Stock opname
│   │   ├── StockMovement.php # Riwayat transaksi
│   │   ├── AssetMutation.php # Mutasi aset
│   │   ├── Category.php  # Kategori barang
│   │   ├── Location.php  # Lokasi
│   │   ├── Unit.php      # Unit/departemen
│   │   └── User.php      # Manajemen user (role dikelola lewat seeder)
│   ├── Filters/          # Filter request
│   │   ├── AuthFilter.php   # Cek session & token refresh
│   │   └── RoleFilter.php   # Cek role user
│   ├── Libraries/        # Library custom
│   │   └── UserGateLibrary.php # API client UserGate
│   ├── Models/           # Model classes
│   ├── Database/
│   │   └── Migrations/   # Database migrations
│   └── Views/            # Template views
│       ├── layout/       # Header, navbar, footer
│       ├── partials/     # Komponen dipakai ulang (galeri, uploader)
│       ├── auth/         # Login page
│       ├── setup/        # Setup page
│       └── ...           # View per modul
├── public/               # Web root (document root)
│   ├── index.php         # Entry point
│   ├── vendor/           # DataTables (harus ter-commit, bukan .gitignore)
│   └── js/               # Custom JavaScript
├── writable/             # Cache, logs, session, uploads
├── .env                  # Environment config (per mesin, tidak di-commit)
├── .env.example          # Template .env (di-commit)
├── Dockerfile            # Docker image, entrypoint ditulis inline
├── docker-compose.yml    # Docker compose
├── .dockerignore         # Berkas yang tidak ikut build context
└── composer.json         # PHP dependencies
```

---

## Modul Aplikasi

### Barang / Aset

Kelola barang identitas (satu per satu, punya serial number).

| Fitur | Endpoint | Keterangan |
|-------|----------|------------|
| Daftar | `GET /assets` | Tabel server-side dengan search |
| Tambah | `POST /assets/store` | Form modal |
| Edit | `POST /assets/update/:id` | Form modal |
| Detail | `GET /assets/:id` | Halaman detail |
| Hapus | `POST /assets/delete/:id` | Konfirmasi modal |
| Keluar | `POST /assets/asset-out/:id` | Catat barang keluar |
| Kembali | `POST /assets/asset-return/:id` | Catat pengembalian |

### Barang Stok

Kelola barang non-identitas (jumlah stok).

| Fitur | Endpoint | Keterangan |
|-------|----------|------------|
| Daftar | `GET /stock-items` | Tabel server-side |
| Stock In | `POST /stock-items/:id/stock-in` | Tambah stok |
| Stock Out | `POST /stock-items/:id/stock-out` | Kurangi stok |
| Transfer | `POST /stock-items/:id/transfer` | Pindah lokasi |
| Adjustment | `POST /stock-items/:id/adjustment` | Koreksi stok |

### Stock Opname

Pemeriksaan fisik barang inventaris.

| Fitur | Endpoint | Keterangan |
|-------|----------|------------|
| Daftar | `GET /stock-opnames` | Tabel server-side |
| Buat | `GET /stock-opnames/create` | Form baru |
| Periksa | `POST /stock-opnames/detail/:id/update` | Update kondisi |
| Selesai | `POST /stock-opnames/:id/finish` | Finalisasi |

### Master Data

| Modul | Endpoint | Keterangan |
|-------|----------|------------|
| Kategori | `/categories` | CRUD kategori barang |
| Lokasi | `/locations` | CRUD lokasi penyimpanan |
| Unit | `/units` | CRUD unit/departemen |

### Laporan

| Fitur | Endpoint | Keterangan |
|-------|----------|------------|
| Laporan Aset | `GET /reports/assets` | Filter: lokasi, kategori, kondisi, status |

### Manajemen User & Role

| Modul | Endpoint | Keterangan |
|-------|----------|------------|
| User | `/users` | CRUD user + role + lokasi |
| Role | `/roles` | CRUD role + permission (Super Admin only) |

---

## Role & Akses

| Role | Akses |
|------|-------|
| **Super Admin** | Semua menu, manajemen role, manajemen user |
| **Admin Inventaris** | Barang, stok, mutasi, master data, user management |
| **Petugas Inventaris** | Barang, stok, mutasi, master data |
| **Manajemen** | Laporan |
| **Auditor** | Laporan |

---

## Konfigurasi UserGate

Aplikasi ini menggunakan UserGate untuk autentikasi. Pastikan:

1. UserGate server berjalan dan dapat diakses
2. API key valid dan memiliki permission yang sesuai
3. Konfigurasi di `.env` benar:

```ini
usergate.url = 'http://your-usergate-server:8091/api/v1'
usergate.api_key = 'your_api_key'
```

### Alur Autentikasi

1. User login via form → POST ke UserGate `/auth/login`
2. UserGate return `access_token` + `refresh_token`
3. Token disimpan di session CI4
4. `AuthFilter` mengecek session sebelum setiap request
5. Jika token hampir expire (< 5 menit), `AuthFilter` auto-refresh
6. User logout → session dihapus

### User Gate Admin

Jika user adalah admin di UserGate (`is_super_admin = true`), otomatis mendapat role **Super Admin** di aplikasi inventaris.

---

## Troubleshooting

### Database Connection Error

```
Cannot connect to the database.
```

- Cek konfigurasi di `.env` (hostname, database, username, password, port)
- Pastikan MySQL berjalan
- Untuk Docker: gunakan hostname `db` bukan `localhost`

### UserGate Login Gagal

```
Username atau password salah
```

- Cek `usergate.url` dan `usergate.api_key` di `.env`
- Pastikan UserGate server berjalan
- Cek log di `writable/logs/`
- Untuk self-signed SSL: sudah di-bypass otomatis

### Table Not Found

```
Table 'inventaris.users' doesn't exist
```

- Buka `/setup` untuk melihat instruksi migrasi
- Jalankan: `php spark migrate`
- Jalankan: `php spark db:seed DatabaseSeeder`

### Permission Error

`writable/` adalah satu-satunya folder yang perlu bisa ditulis: session, log,
cache, dan berkas unggahan. Apache hanya perlu membaca sisanya.

```bash
# Server pengembangan
chmod -R 775 writable/

# Docker -- biasanya tak perlu, ownership sudah diatur saat build.
# Hanya perlu kalau volume writable pernah diisi dari luar container.
docker compose exec app chown -R www-data:www-data writable/
```

### Asset Tampilan Rusak

- Hard refresh: `Ctrl + Shift + R`
- Cek browser console untuk error 404 pada CSS/JS
- Pastikan `public/vendor/datatables.min.css` dan `.js` ada. Keduanya wajib
  ter-*commit*. Kalau `.gitignore` menulis `vendor/` tanpa garis miring depan,
  `public/vendor/` ikut tersaring dan file itu hilang dari checkout -- tabel
  tetap tampil, tapi searching, sorting, dan paginate tidak berfungsi.

---

## Development

### Menjalankan ulang

```bash
php spark serve --port 8092
```

Tidak perlu menyentuh `.env` untuk berpindah mode: `CI_ENVIRONMENT` di sana
sudah `development`, dan hanya container yang menimpanya menjadi `production`.

### Database Migration

```bash
# Buat migration baru
php spark make:migration NameOfMigration

# Jalankan semua migration
php spark migrate

# Rollback migration terakhir
php spark migrate:rollback

# Lihat status migration
php spark migrate:status
```

### Seed Database

```bash
# Jalankan semua seeder
php spark db:seed DatabaseSeeder

# Jalankan seeder tertentu
php spark db:seed NameOfSeeder
```

---

## License

MIT License
