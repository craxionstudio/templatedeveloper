<?php

return [

    /*
    | Di production, view Blade dikompilasi saat deploy (`php artisan optimize` → view:cache)
    | oleh user deploy, sedangkan PHP-FPM berjalan sebagai www-data. Kalau timestamp dicek,
    | Laravel bisa memanggil touch() pada file kompilasi milik user lain → "Utime failed:
    | Operation not permitted" → 500. Setiap deploy sudah menghapus & mengompilasi ulang
    | semua view, jadi pengecekan timestamp tidak diperlukan di production.
    | Path dan folder kompilasi tetap memakai bawaan framework.
    */
    'check_cache_timestamps' => (bool) env('VIEW_CHECK_CACHE_TIMESTAMPS', env('APP_ENV') !== 'production'),

];
