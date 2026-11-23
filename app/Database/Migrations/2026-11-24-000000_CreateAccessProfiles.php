<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccessProfiles extends Migration
{
    public function up()
    {
        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `access_profiles` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `description` text,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_access_profiles_name` (`name`)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `access_profile_locations` (
              `profile_id` int unsigned NOT NULL,
              `location_id` int unsigned NOT NULL,
              PRIMARY KEY (`profile_id`,`location_id`),
              KEY `access_profile_locations_location_id_foreign` (`location_id`),
              CONSTRAINT `access_profile_locations_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `access_profile_locations_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `access_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);

        $this->db->query(<<<SQL
            CREATE TABLE IF NOT EXISTS `user_access_profiles` (
              `user_id` int unsigned NOT NULL,
              `profile_id` int unsigned NOT NULL,
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`user_id`,`profile_id`),
              KEY `user_access_profiles_profile_id_foreign` (`profile_id`),
              CONSTRAINT `user_access_profiles_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `access_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `user_access_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            SQL);
    }

    public function down()
    {
        $this->forge->dropTable('user_access_profiles', true);
        $this->forge->dropTable('access_profile_locations', true);
        $this->forge->dropTable('access_profiles', true);
    }
}