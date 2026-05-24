<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public Asset URL
    |--------------------------------------------------------------------------
    |
    | Domain publik yang dipakai untuk QR aset, link detail aset di notifikasi,
    | dan seluruh URL publik aset. Pisahkan dari APP_URL supaya QR tidak ikut
    | berubah saat domain panel/admin atau environment aplikasi berpindah.
    |
    */
    'public_base_url' => env('ASSET_PUBLIC_URL', env('APP_URL')),

    /*
    |--------------------------------------------------------------------------
    | Legacy Asset Hosts
    |--------------------------------------------------------------------------
    |
    | Daftar host lama yang masih boleh masuk ke route publik aset. Jika request
    | datang dari host-host ini, aplikasi akan mengarahkan otomatis ke domain
    | publik aset yang baru tanpa perlu cetak ulang QR.
    |
    */
    'legacy_hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => trim($host),
        explode(',', (string) env('ASSET_LEGACY_HOSTS', 'ypik.pradita.website'))
    ))),
];
