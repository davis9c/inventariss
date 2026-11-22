<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransferRequests extends Migration
{
    public function up()
    {
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `transfer_requests` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `request_kind` enum('Penawaran','Permintaan') NOT NULL DEFAULT 'Penawaran',
              `item_type` enum('Aset','Barang Stok') NOT NULL,
              `asset_id` int unsigned DEFAULT NULL,
              `stock_item_id` int unsigned DEFAULT NULL,
              `from_location_id` int unsigned NOT NULL,
              `to_location_id` int unsigned NOT NULL,
              `from_unit_id` int unsigned DEFAULT NULL,
              `to_unit_id` int unsigned DEFAULT NULL,
              `quantity` int unsigned NOT NULL DEFAULT '1',
              `reason` varchar(255) DEFAULT NULL,
              `notes` text,
              `status` enum('Pending','Disetujui','Ditolak') NOT NULL DEFAULT 'Pending',
              `requested_by` int unsigned NOT NULL,
              `confirmed_by` int unsigned DEFAULT NULL,
              `confirmed_at` datetime DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `tr_kind` (`request_kind`),
              KEY `tr_status` (`status`),
              KEY `tr_from_loc` (`from_location_id`),
              KEY `tr_to_loc` (`to_location_id`),
              KEY `tr_requested_by` (`requested_by`),
              CONSTRAINT `tr_asset_fk` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `tr_stock_fk` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `tr_from_loc_fk` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `tr_to_loc_fk` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `tr_from_unit_fk` FOREIGN KEY (`from_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `tr_to_unit_fk` FOREIGN KEY (`to_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `tr_requested_by_fk` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `tr_confirmed_by_fk` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);
    }

    public function down()
    {
        $this->forge->dropTable('transfer_requests', true);
    }
}