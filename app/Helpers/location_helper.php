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
