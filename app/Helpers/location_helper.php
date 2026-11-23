<?php

use App\Models\UserLocationModel;
use App\Models\UserAccessProfileModel;
use App\Models\AccessProfileLocationModel;

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
        // Super Admin bebas
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

        $locationIds = user_location_ids();

        // User tanpa lokasi — tidak bisa mengakses apa pun
        if (empty($locationIds)) {
            return false;
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

        $locationIds = user_location_ids();

        // User tanpa lokasi — tidak bisa mengakses apa pun
        if (empty($locationIds)) {
            return false;
        }

        $involved = array_filter([
            $transaction['from_location_id'] ?? null,
            $transaction['to_location_id'] ?? null,
        ], static fn ($id) => $id !== null && $id !== '');

        if ($involved === []) {
            // Tidak ada lokasi yang tercatat — tetap tolak karena
            // user sudah dibatasi ke lokasi tertentu tapi transaksi
            // tidak melibatkan lokasi mana pun yang dimilikinya.
            return false;
        }

        return (bool) array_intersect($involved, array_map('intval', $locationIds));
    }
}

if (! function_exists('sync_user_locations')) {
    /**
     * Sinkronkan lokasi user berdasarkan profile yang dimilikinya.
     *
     * Mengambil semua profile user → gabungkan lokasi dari semua profile →
     * timpa user_locations. Dipanggil setelah:
     *  — user mengganti pilihan profile
     *  — profile di-edit (lokasi bertambah/berkurang)
     *  — profile dihapus
     *
     * @param int|int[] $userIds satu user_id atau array user_id
     */
    function sync_user_locations($userIds): void
    {
        $ids = is_array($userIds) ? $userIds : [(int) $userIds];

        if ($ids === []) {
            return;
        }

        $userProfileModel = new UserAccessProfileModel();
        $profileLocModel  = new AccessProfileLocationModel();
        $userLocModel     = new UserLocationModel();
        $now              = date('Y-m-d H:i:s');

        foreach ($ids as $userId) {
            $profileIds = array_column(
                $userProfileModel->where('user_id', $userId)->findAll(),
                'profile_id'
            );

            // Hapus lokasi lama
            $userLocModel->where('user_id', $userId)->delete();

            if ($profileIds === []) {
                continue;
            }

            // Gabung lokasi dari semua profile
            $locationIds = [];
            foreach ($profileIds as $profileId) {
                foreach ($profileLocModel->where('profile_id', $profileId)->findAll() as $row) {
                    $locationIds[] = (int) $row['location_id'];
                }
            }

            $locationIds = array_values(array_unique($locationIds));

            foreach ($locationIds as $locationId) {
                $userLocModel->insert([
                    'user_id'     => $userId,
                    'location_id' => $locationId,
                    'created_at'  => $now,
                ]);
            }
        }
    }
}