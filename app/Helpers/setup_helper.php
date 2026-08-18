<?php

use App\Models\UserModel;

if (!function_exists('is_app_installed')) {
    function is_app_installed(): bool
    {
        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists('roles')) {
                return false;
            }

            return (new UserModel())->countAllResults() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }
}