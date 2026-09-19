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

## Instalasi Manual

### Prasyarat

- PHP 8.2+ dengan extensi: `intl`, `mbstring`, `mysqlnd`, `curl`, `json`
- MySQL 5.7+ atau 8.0+
- Composer
- Web server (Apache/Nginx)

### Langkah-langkah

```bash
# 1. Clone repository
git clone <repository-url> inventaris
cd inventaris

# 2. Install dependencies
composer install

# 3. Copy environment file
cp env .env

# 4. Edit .env — sesuaikan konfigurasi
```

Edit `.env`:

```ini
CI_ENVIRONMENT = production

app.baseURL = 'http://localhost:8092/'

database.default.hostname = localhost
database.default.database = inventaris
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.port = 3306

usergate.url = 'http://localhost:8091/api/v1'
usergate.api_key = 'your_usergate_api_key'
```

```bash
# 5. Buat database
mysql -u root -p -e "CREATE DATABASE inventaris CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# 6. Jalankan migrasi & seed
php spark migrate
php spark db:seed DatabaseSeeder

# 7. Set permission writable
chmod -R 777 writable/

# 8. Jalankan server
php spark serve --port 8092
```

Buka `http://localhost:8092` di browser.

### Login Pertama Kali

1. Buka `http://localhost:8092`
2. Halaman Setup akan muncul jika tabel `users` belum ada
3. Login dengan akun UserGate
4. **User pertama yang login otomatis menjadi Super Admin**

---

## Instalasi Docker

### Prasyarat

- Docker 20.10+
- Docker Compose v2+

### File yang Diperlukan

Pastikan file berikut ada di root project:

- `Dockerfile`
- `docker-compose.yml`
- `.dockerignore`

### Langkah-langkah

```bash
# 1. Clone repository
git clone <repository-url> inventaris
cd inventaris

# 2. Copy environment file
cp env .env

# 3. Edit .env
```

Edit `.env` — **gunakan nama service Docker sebagai hostname database**:

```ini
CI_ENVIRONMENT = production

app.baseURL = 'http://localhost:8092/'

database.default.hostname = db
database.default.database = inventaris
database.default.username = inventaris
database.default.password = inventaris_secret
database.default.DBDriver = MySQLi
database.default.port = 3306

usergate.url = 'http://usergate:8091/api/v1'
usergate.api_key = 'your_usergate_api_key'
```

```bash
# 4. Build & jalankan container
docker compose up -d --build

# 5. Jalankan migrasi & seed
docker compose exec app php spark migrate
docker compose exec app php spark db:seed DatabaseSeeder

# 6. Set permission
docker compose exec app chmod -R 777 writable/
```

Buka `http://localhost:8092` di browser.

### Perintah Docker Berguna

```bash
# Lihat log
docker compose logs -f app

# Masuk ke container
docker compose exec app bash

# Jalankan spark command
docker compose exec app php spark <command>

# Restart container
docker compose restart app

# Stop semua container
docker compose down

# Stop + hapus volume database
docker compose down -v
```

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
│   │   ├── User.php      # Manajemen user
│   │   └── Role.php      # Manajemen role
│   ├── Filters/          # Filter request
│   │   ├── AuthFilter.php   # Cek session & token refresh
│   │   └── RoleFilter.php   # Cek role user
│   ├── Libraries/        # Library custom
│   │   └── UserGateLibrary.php # API client UserGate
│   ├── Models/           # Model classes
│   ├── Database/
│   │   └── Migrations/   # Database migrations
│   └── Views/            # Template views
│       ├── layout/       # Header, sidebar, footer
│       ├── auth/         # Login page
│       ├── setup/        # Setup page
│       └── ...           # View per modul
├── public/               # Web root (document root)
│   ├── index.php         # Entry point
│   ├── vendor/           # CSS, JS assets
│   ├── js/               # Custom JavaScript
│   └── webfonts/         # Font Awesome fonts
├── writable/             # Cache, logs, session, uploads
├── .env                  # Environment config
├── Dockerfile            # Docker image
├── docker-compose.yml    # Docker compose
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

```
chmod: cannot access 'writable/'
```

```bash
chmod -R 777 writable/
# atau untuk Docker:
docker compose exec app chmod -R 777 writable/
```

### Asset Tampilan Rusak

- Hard refresh: `Ctrl + Shift + R`
- Cek browser console untuk error 404 pada CSS/JS
- Pastikan folder `public/vendor/` dan `public/webfonts/` ada

---

## Development

### Jalankan dalam mode development

```bash
# Edit .env
CI_ENVIRONMENT = development

# Jalankan server
php spark serve --port 8092
```

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
