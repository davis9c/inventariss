<?php

use App\Models\UserLocationModel;

if (! function_exists('user_location_ids')) {
    function user_location_ids(): array
    {
        $userId = session()->get('user_id');

        if (!$userId) {
            return [];
        }

        return array_column(
            (new UserLocationModel())
                ->where('user_id', $userId)
                ->findAll(),
            'location_id'
        );
    }
}

if (! function_exists('has_location_restriction')) {
    function has_location_restriction(): bool
    {
        // Super Admin bebas melihat semua lokasi
        $roles = session()->get('roles') ?? [];

        if (in_array('Super Admin', $roles, true)) {
            return false;
        }

        return ! empty(user_location_ids());
    }
}

if (! function_exists('can_access_location')) {
    function can_access_location($locationId): bool
    {
        $roles = session()->get('roles') ?? [];

        // Super Admin bebas
        if (in_array('Super Admin', $roles, true)) {
            return true;
        }

        // User tanpa lokasi
        $locationIds = user_location_ids();

        if (empty($locationIds)) {
            return true;
        }

        return in_array(
            (int) $locationId,
            array_map('intval', $locationIds),
            true
        );
    }
}

if (! function_exists('can_access_transaction')) {
    /**
     * Apakah pemanggil berhak mengakses satu catatan pergerakan.
     *
     * Berbeda dengan can_access_location(): catatan pergerakan tidak punya
     * satu lokasi tunggal. Satu transaksi bisa berjalan dari lokasi A ke
     * lokasi B, jadi akses diberikan kalau pemanggil punya akses ke salah
     * satu dari keduanya.
     *
     * Aturan ini WAJIB dipakai juga oleh lampiran yang menempel pada
     * transaksi. Kalau lampiran memakai aturan yang lebih longgar, user yang
     * tidak berhak membuka satu transaksi masih bisa membaca lampirannya
     * dengan menebak id. Kalau lebih ketat, dokumen milik sendiri bisa
     * menjadi tidak bisa dibuka.
     *
     * @param array $transaction baris inventory_transactions
     */
    function can_access_transaction(array $transaction): bool
    {
        $roles = session()->get('roles') ?? [];

        // Super Admin bebas
        if (in_array('Super Admin', $roles, true)) {
            return true;
        }

        // User tanpa lokasi
        $locationIds = user_location_ids();

        if (empty($locationIds)) {
            return true;
        }

        $involved = array_filter([
            $transaction['from_location_id'] ?? null,
            $transaction['to_location_id'] ?? null,
        ], static fn ($id) => $id !== null && $id !== '');

        if ($involved === []) {
            // Tidak ada lokasi yang tercatat, jadi tidak ada yang bisa
            // dibatasi. Sama seperti InventoryTransaction::data() dan
            // show() yang aturannya diturunkan dari helper ini.
            return true;
        }

        return (bool) array_intersect($involved, array_map('intval', $locationIds));
    }
}
