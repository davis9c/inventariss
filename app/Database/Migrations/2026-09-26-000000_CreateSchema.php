<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Skema dasar aplikasi, digabung menjadi satu.
 *
 * Sebelumnya schema ini dibangun oleh 14 file migrasi berurutan yang
 * saling menambal: enum asset_status diperluas, kolom level/capabilities
 * ditambahkan, tabel permissions di-drop, dan seterusnya. Untuk instalasi
 * baru semua itu hanya menambah langkah tanpa tambah nilai.
 *
 * File ini dibuat dengan MENGGENERATE ulang dari skema yang benar-benar
 * ada di database (SHOW CREATE TABLE per tabel), lalu diverifikasi dengan
 * membandingkan hasil build di database kosong terhadap database lama
 * tabel per tabel. Jadi isinya bukan hasil menulis ulang dari rantai
 * migrasi, melainkan salinan yang sudah dibuktikan sama.
 *
 * Tabel yang sengaja tidak ada di sini:
 *
 *   permissions, role_permissions  -- tidak pernah dipakai, sudah di-drop.
 *   maintenances                    -- tidak ada controller, route, atau
 *                                     model yang memakainya; kolomnya
 *                                     hanya sisa dari fitur yang tidak
 *                                     pernah dibangun.
 *
 * Catatan JSON untuk database yang sudah ada: migrasi ini TIDAK dijalankan
 * pada database yang sudah pernah dimigrasi. Baris di tabel `migrations`
 * cukup diganti ke versi ini. Jalankan juga `db:seed DatabaseSeeder` bila
 * data awal belum ada.
 */
class CreateSchema extends Migration
{
    public function up()
    {
        // Urutan penting: setiap tabel dibuat setelah tabel yang
        // direferensikannya lewat foreign key.

        // Role statis. name unik dan level menentukan hierarki;
        // capabilities hanya uraian niat, bukan enforcement.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `roles` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(50) NOT NULL,
              `level` tinyint unsigned NOT NULL DEFAULT '1' COMMENT 'Hierarchy role; 1 = privilege tertinggi',
              `description` varchar(255) DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              `capabilities` text COMMENT 'Uraian fitur yang ditujukan untuk role ini (niat, bukan enforcement)',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_roles_name` (`name`),
              KEY `idx_roles_level` (`level`)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Pengguna. Akun sebenarnya datang dari UserGate lewat
        // usergate_id.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `users` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `usergate_id` varchar(36) DEFAULT NULL,
              `username` varchar(50) NOT NULL,
              `password` varchar(255) DEFAULT NULL,
              `name` varchar(100) NOT NULL,
              `is_active` tinyint(1) NOT NULL DEFAULT '1',
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `username` (`username`),
              KEY `idx_usergate_id` (`usergate_id`)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Kategori barang.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `categories` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `description` text,
              `is_active` tinyint(1) NOT NULL DEFAULT '1',
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Lokasi penyimpanan.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `locations` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `building` varchar(100) DEFAULT NULL,
              `floor` varchar(50) DEFAULT NULL,
              `room` varchar(100) DEFAULT NULL,
              `description` text,
              `is_active` tinyint(1) NOT NULL DEFAULT '1',
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Unit / departemen.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `units` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `code` varchar(30) NOT NULL,
              `description` text,
              `is_active` tinyint(1) NOT NULL DEFAULT '1',
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Barang (aset). Satu baris untuk satu barang.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `assets` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `asset_code` varchar(50) NOT NULL,
              `name` varchar(150) NOT NULL,
              `category_id` int unsigned NOT NULL,
              `unit_id` int unsigned NOT NULL,
              `location_id` int unsigned NOT NULL,
              `brand` varchar(100) DEFAULT NULL,
              `model` varchar(100) DEFAULT NULL,
              `serial_number` varchar(100) DEFAULT NULL,
              `acquisition_year` year DEFAULT NULL,
              `acquisition_price` decimal(15,2) NOT NULL DEFAULT '0.00',
              `condition_status` enum('Baik','Rusak Ringan','Rusak Berat') NOT NULL DEFAULT 'Baik',
              `asset_status` enum('Aktif','Dipinjam','Maintenance','Tidak Digunakan','Keluar Perusahaan') DEFAULT 'Aktif',
              `description` text,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `asset_code` (`asset_code`),
              KEY `assets_category_id_foreign` (`category_id`),
              KEY `assets_unit_id_foreign` (`unit_id`),
              KEY `assets_location_id_foreign` (`location_id`),
              CONSTRAINT `assets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `assets_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `assets_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Riwayat mutasi aset. Transaksinya sendiri ada di
        // inventory_transactions dengan item_type = 'Aset'.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `asset_mutations` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `asset_id` int unsigned NOT NULL,
              `from_unit_id` int unsigned DEFAULT NULL,
              `to_unit_id` int unsigned DEFAULT NULL,
              `from_location_id` int unsigned DEFAULT NULL,
              `to_location_id` int unsigned DEFAULT NULL,
              `mutation_date` date NOT NULL,
              `reason` varchar(255) DEFAULT NULL,
              `notes` text,
              `created_by` int unsigned DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `asset_mutations_asset_id_foreign` (`asset_id`),
              KEY `asset_mutations_from_unit_id_foreign` (`from_unit_id`),
              KEY `asset_mutations_to_unit_id_foreign` (`to_unit_id`),
              KEY `asset_mutations_from_location_id_foreign` (`from_location_id`),
              KEY `asset_mutations_to_location_id_foreign` (`to_location_id`),
              CONSTRAINT `asset_mutations_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `asset_mutations_from_location_id_foreign` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
              CONSTRAINT `asset_mutations_from_unit_id_foreign` FOREIGN KEY (`from_unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
              CONSTRAINT `asset_mutations_to_location_id_foreign` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
              CONSTRAINT `asset_mutations_to_unit_id_foreign` FOREIGN KEY (`to_unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Barang stok (bukan aset).
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `stock_items` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `item_code` varchar(50) NOT NULL,
              `name` varchar(150) NOT NULL,
              `category_id` int unsigned NOT NULL,
              `unit_id` int unsigned NOT NULL,
              `location_id` int unsigned NOT NULL,
              `satuan` varchar(50) NOT NULL DEFAULT 'pcs',
              `quantity` int unsigned NOT NULL DEFAULT '0',
              `description` text,
              `is_active` tinyint(1) NOT NULL DEFAULT '1',
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `item_code` (`item_code`),
              KEY `stock_items_category_id_foreign` (`category_id`),
              KEY `stock_items_unit_id_foreign` (`unit_id`),
              KEY `stock_items_location_id_foreign` (`location_id`),
              CONSTRAINT `stock_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `stock_items_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `stock_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Pergerakan barang stok.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `stock_transactions` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `stock_item_id` int unsigned NOT NULL,
              `type` enum('Masuk','Keluar') NOT NULL,
              `quantity` int unsigned NOT NULL,
              `transaction_date` date NOT NULL,
              `notes` text,
              `created_by` int unsigned DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `stock_transactions_stock_item_id_foreign` (`stock_item_id`),
              KEY `stock_transactions_created_by_foreign` (`created_by`),
              CONSTRAINT `stock_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
              CONSTRAINT `stock_transactions_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Header stock opname.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `stock_opnames` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `opname_code` varchar(50) NOT NULL,
              `opname_date` date NOT NULL,
              `location_id` int unsigned DEFAULT NULL,
              `status` enum('Draft','Selesai') NOT NULL DEFAULT 'Draft',
              `notes` text,
              `created_by` int unsigned DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `opname_code` (`opname_code`),
              KEY `stock_opnames_location_id_foreign` (`location_id`),
              CONSTRAINT `stock_opnames_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Hasil opname untuk aset.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `stock_opname_details` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `stock_opname_id` int unsigned NOT NULL,
              `asset_id` int unsigned NOT NULL,
              `is_found` tinyint(1) NOT NULL DEFAULT '1',
              `condition_status` enum('Baik','Rusak Ringan','Rusak Berat') NOT NULL DEFAULT 'Baik',
              `notes` text,
              `checked_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `stock_opname_details_stock_opname_id_foreign` (`stock_opname_id`),
              KEY `stock_opname_details_asset_id_foreign` (`asset_id`),
              CONSTRAINT `stock_opname_details_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `stock_opname_details_stock_opname_id_foreign` FOREIGN KEY (`stock_opname_id`) REFERENCES `stock_opnames` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Hasil opname untuk barang stok.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `stock_opname_stock_details` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `stock_opname_id` int unsigned NOT NULL,
              `stock_item_id` int unsigned NOT NULL,
              `system_qty` int unsigned NOT NULL,
              `physical_qty` int unsigned DEFAULT NULL,
              `notes` text,
              `checked_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `stock_opname_id_stock_item_id` (`stock_opname_id`,`stock_item_id`),
              KEY `stock_opname_stock_details_stock_item_id_foreign` (`stock_item_id`),
              CONSTRAINT `stock_opname_stock_details_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `stock_opname_stock_details_stock_opname_id_foreign` FOREIGN KEY (`stock_opname_id`) REFERENCES `stock_opnames` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Semua pergerakan, aset maupun barang stok,
        // dibedakan item_type. Polymorphic: hanya
        // salah satu dari asset_id atau stock_item_id
        // yang terisi.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `inventory_transactions` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `transaction_code` varchar(50) NOT NULL,
              `transaction_date` date NOT NULL,
              `transaction_type` enum('Masuk','Keluar','Pindah','Penyesuaian Naik','Penyesuaian Turun','Perolehan','Mutasi','Keluar Perusahaan','Pengembalian') NOT NULL,
              `item_type` enum('Aset','Barang Stok') NOT NULL,
              `asset_id` int unsigned DEFAULT NULL,
              `stock_item_id` int unsigned DEFAULT NULL,
              `quantity` int unsigned NOT NULL DEFAULT '1',
              `from_location_id` int unsigned DEFAULT NULL,
              `to_location_id` int unsigned DEFAULT NULL,
              `reference_type` varchar(50) DEFAULT NULL,
              `reference_id` int unsigned DEFAULT NULL,
              `reason` varchar(255) DEFAULT NULL,
              `notes` text,
              `created_by` int unsigned DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `transaction_code` (`transaction_code`),
              KEY `inventory_transactions_from_location_id_foreign` (`from_location_id`),
              KEY `inventory_transactions_to_location_id_foreign` (`to_location_id`),
              KEY `inventory_transactions_created_by_foreign` (`created_by`),
              KEY `transaction_date` (`transaction_date`),
              KEY `transaction_type` (`transaction_type`),
              KEY `item_type` (`item_type`),
              KEY `asset_id` (`asset_id`),
              KEY `stock_item_id` (`stock_item_id`),
              CONSTRAINT `inventory_transactions_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `inventory_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
              CONSTRAINT `inventory_transactions_from_location_id_foreign` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
              CONSTRAINT `inventory_transactions_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `inventory_transactions_to_location_id_foreign` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Hubungan user dan role (banyak-ke-banyak).
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `user_roles` (
              `user_id` int unsigned NOT NULL,
              `role_id` int unsigned NOT NULL,
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`user_id`,`role_id`),
              KEY `user_roles_role_id_foreign` (`role_id`),
              CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Lokasi yang boleh diakses user. Kosong = tanpa
        // pembatasan.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `user_locations` (
              `user_id` int unsigned NOT NULL,
              `location_id` int unsigned NOT NULL,
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`user_id`,`location_id`),
              KEY `user_locations_location_id_foreign` (`location_id`),
              CONSTRAINT `user_locations_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `user_locations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Unit yang dipakai di lokasi tertentu.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `unit_locations` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `unit_id` int unsigned NOT NULL,
              `location_id` int unsigned NOT NULL,
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unit_id_location_id` (`unit_id`,`location_id`),
              KEY `unit_locations_location_id_foreign` (`location_id`),
              CONSTRAINT `unit_locations_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `unit_locations_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Dokumen aset (garansi, pembelian, sertifikat).
        // Berkasnya disimpan di writable/uploads, DI LUAR
        // document root.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `asset_documents` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `asset_id` int unsigned NOT NULL COMMENT 'Aset pemilik dokumen',
              `stored_name` varchar(255) NOT NULL,
              `original_name` varchar(255) NOT NULL,
              `title` varchar(150) NOT NULL,
              `description` text COMMENT 'Keterangan dokumen, opsional',
              `mime_type` varchar(100) NOT NULL COMMENT 'Hasil deteksi finfo, bukan dari klien',
              `extension` varchar(10) NOT NULL,
              `size_bytes` int unsigned NOT NULL,
              `uploaded_by` int unsigned DEFAULT NULL COMMENT 'users.id pengunggah',
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `stored_name` (`stored_name`),
              KEY `asset_id` (`asset_id`),
              CONSTRAINT `asset_documents_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Gambar barang dan catatan pergerakan. Polymorphic:
        // owner ditentukan item_type + item_id, jadi tidak ada
        // foreign key.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `item_images` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `item_type` enum('asset','stock_item','stock_movement','asset_movement') NOT NULL COMMENT 'Milik tabel mana',
              `item_id` int unsigned NOT NULL COMMENT 'assets.id atau stock_items.id, sesuai item_type',
              `title` varchar(150) NOT NULL,
              `description` text,
              `stored_name` varchar(255) NOT NULL,
              `original_name` varchar(255) NOT NULL,
              `mime_type` varchar(100) NOT NULL COMMENT 'Hasil deteksi finfo, bukan dari klien',
              `extension` varchar(10) NOT NULL,
              `size_bytes` int unsigned NOT NULL,
              `uploaded_by` int unsigned DEFAULT NULL COMMENT 'users.id pengunggah',
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `stored_name` (`stored_name`),
              KEY `item_type_item_id` (`item_type`,`item_id`)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        // Dokumen catatan pergerakan. Polymorphic,
        // seperti item_images.
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `transaction_documents` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `item_type` enum('stock_movement','asset_movement') NOT NULL COMMENT 'Catatan pergerakan milik barang stok atau aset',
              `item_id` int unsigned NOT NULL COMMENT 'inventory_transactions.id',
              `title` varchar(150) NOT NULL,
              `description` text,
              `stored_name` varchar(255) NOT NULL,
              `original_name` varchar(255) NOT NULL,
              `mime_type` varchar(100) NOT NULL COMMENT 'Hasil deteksi finfo, bukan dari klien',
              `extension` varchar(10) NOT NULL,
              `size_bytes` int unsigned NOT NULL,
              `uploaded_by` int unsigned DEFAULT NULL COMMENT 'users.id pengunggah',
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `stored_name` (`stored_name`),
              KEY `item_type_item_id` (`item_type`,`item_id`)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);
    }

    public function down()
    {
        // Kebalikan dari urutan up(): anak lebih dulu, supaya tidak ada
        // foreign key yang menahan saat tabel induknya dijatuhkan.
        //
        // Tabel `migrations` milik CodeIgniter sendiri dan sengaja tidak
        // disentuh.
        $this->forge->dropTable('transaction_documents', true);
        $this->forge->dropTable('item_images', true);
        $this->forge->dropTable('asset_documents', true);
        $this->forge->dropTable('unit_locations', true);
        $this->forge->dropTable('user_locations', true);
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('inventory_transactions', true);
        $this->forge->dropTable('stock_opname_stock_details', true);
        $this->forge->dropTable('stock_opname_details', true);
        $this->forge->dropTable('stock_opnames', true);
        $this->forge->dropTable('stock_transactions', true);
        $this->forge->dropTable('stock_items', true);
        $this->forge->dropTable('asset_mutations', true);
        $this->forge->dropTable('assets', true);
        $this->forge->dropTable('units', true);
        $this->forge->dropTable('locations', true);
        $this->forge->dropTable('categories', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('roles', true);
    }
}
