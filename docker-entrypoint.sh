#!/bin/sh
# Entrypoint ini sengaja TANPA logika database. Container aplikasi tidak
# menjalankan migrasi, tidak menjalankan seeder, dan tidak melakukan
# pemeriksaan koneksi apa pun ke database.
#
# Keduanya dilakukan manual dari host:
#
#     docker compose exec app php spark migrate
#     docker compose exec app php spark db:seed DatabaseSeeder
#
# Alasannya: database berada di luar Docker dan dipakai bersama dengan
# server pengembangan. Container bisa start, restart, di-stop, atau dihidupkan
# kembali oleh daemon Docker karena alasan apa pun (deploy, crash, reboot
# mesin) -- tidak satu pun dari kejadian itu seharusnya menulis ke database.
#
# Satu-satunya tugas script ini adalah meneruskan ke entrypoint bawaan image
# (docker-php-entrypoint), yang menjalankan docker-php-ext-* untuk ekstensi
# yang di-install saat runtime. Tanpa penerusan itu, ekstensi runtime tidak
# akan diproses.
exec docker-php-entrypoint "$@"
