<?php

if (!function_exists('year_options')) {
    function year_options($selected = null, int $min = 2000): string
    {
        $max = (int) date('Y') + 2;
        $html = '<option value="">-- Pilih Tahun --</option>';

        for ($y = $min; $y <= $max; $y++) {
            $sel = ((string) $selected === (string) $y) ? ' selected' : '';
            $html .= '<option value="' . $y . '"' . $sel . '>' . $y . '</option>';
        }

        if ($selected !== null && $selected !== '' && ((int) $selected < $min || (int) $selected > $max)) {
            $html .= '<option value="' . esc($selected) . '" selected>' . esc($selected) . ' (di luar rentang)</option>';
        }

        return $html;
    }
}