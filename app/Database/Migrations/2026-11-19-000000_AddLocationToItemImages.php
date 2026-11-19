<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Foto ruangan disimpan di item_images, sama seperti foto aset dan
 * barang stok. Tabel itu memakai enum item_type, jadi nilainya perlu
 * ditambah lewat migrasi terpisah -- bukan dengan mengubah migrasi
 * schema awal yang mungkin sudah dijalankan di database lain.
 */
class AddLocationToItemImages extends Migration
{
    public function up()
    {
        $this->db->query(<<<SQL
            ALTER TABLE `item_images`
            MODIFY `item_type` ENUM('asset','stock_item','stock_movement','asset_movement','location')
            NOT NULL COMMENT 'Milik tabel mana'
            SQL);
    }

    public function down()
    {
        // Hapus dulu baris bertipe location supaya enum lama bisa
        // dipasang kembali tanpa error truncation.
        $this->db->table('item_images')
            ->where('item_type', 'location')
            ->delete();

        $this->db->query(<<<SQL
            ALTER TABLE `item_images`
            MODIFY `item_type` ENUM('asset','stock_item','stock_movement','asset_movement')
            NOT NULL COMMENT 'Milik tabel mana'
            SQL);
    }
}
