<?php

if (! function_exists('make_unique_code')) {
    /**
     * Pastikan kode unik di tabel/kolom. Jika sudah ada, tambahkan sufiks
     * acak (4 hex) hingga unik.
     *
     * @return string kode unik (mungkin berbeda dari input)
     */
    function make_unique_code(string $table, string $column, string $code, ?int $excludeId = null): string
    {
        $code = trim($code);
        if ($code === '') {
            return $code;
        }

        $db = db_connect();

        $exists = function (string $c) use ($db, $table, $column, $excludeId): bool {
            $b = $db->table($table)->where($column, $c);
            if ($excludeId !== null) {
                $b->where('id !=', $excludeId);
            }
            return (int) $b->countAllResults() > 0;
        };

        if (!$exists($code)) {
            return $code;
        }

        // tambahkan sufiks acak
        $base = $code;
        $attempts = 0;
        do {
            $suffix = '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $candidate = $base . $suffix;
            $attempts++;
        } while ($exists($candidate) && $attempts < 10);

        return $candidate;
    }
}