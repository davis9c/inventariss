<?php

if (!function_exists('waktu_utc7')) {
    function waktu_utc7(?string $datetime, string $format = 'Y-m-d H:i'): string
    {
        if (empty($datetime)) {
            return '-';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $datetime)) {
            return $datetime;
        }

        try {
            $dt = new DateTime($datetime, new DateTimeZone('UTC'));

            if ($dt->format('H:i:s') === '00:00:00') {
                return $dt->format('Y-m-d');
            }

            return $dt->setTimezone(new DateTimeZone('Asia/Jakarta'))->format($format);
        } catch (Throwable $e) {
            return $datetime;
        }
    }
}

if (!function_exists('waktu_wib_to_utc')) {
    function waktu_wib_to_utc(string $datetime): string
    {
        try {
            return (new DateTime($datetime, new DateTimeZone('Asia/Jakarta')))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            return $datetime;
        }
    }
}