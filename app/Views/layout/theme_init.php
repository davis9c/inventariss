<?php
/**
 * Pasang tema sebelum CSS dimuat supaya tidak ada kedipan putih ketika
 * pengguna memilih mode gelap. Dipakai oleh layout utama, login, dan setup.
 *
 * Nilai tersimpan: "light", "dark", atau "auto". Mode "auto" diselesaikan
 * di sini agar atribut data-bs-theme sudah benar sebelum halaman digambar.
 */
?>
<script>
    (function () {
        'use strict';

        var key = 'inventaris-theme';
        var mode = 'auto';

        try {
            var saved = window.localStorage.getItem(key);
            if (saved === 'light' || saved === 'dark' || saved === 'auto') {
                mode = saved;
            }
        } catch (e) {
            // localStorage bisa diblokir (mis. mode privat); pakai mode sistem.
        }

        var theme = mode;
        if (mode === 'auto') {
            theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
                ? 'dark'
                : 'light';
        }

        document.documentElement.setAttribute('data-bs-theme', theme);
        document.documentElement.setAttribute('data-theme-mode', mode);
    })();
</script>
