<?php

return [

    /*
    |--------------------------------------------------------------------------
    | BPJS API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi koneksi ke API BPJS Kesehatan.
    | Pada Tahap 1, adapter "fake" digunakan untuk development/demo.
    | Saat kontrak API BPJS terkonfirmasi, ganti adapter ke "http".
    |
    */

    'adapter' => env('BPJS_ADAPTER', 'fake'),

    'base_url' => env('BPJS_BASE_URL', ''),

    'consumer_id' => env('BPJS_CONSUMER_ID', ''),

    'consumer_secret' => env('BPJS_CONSUMER_SECRET', ''),

    'timeout' => env('BPJS_TIMEOUT', 10),

    'retry' => [
        'times' => env('BPJS_RETRY_TIMES', 3),
        'sleep_ms' => env('BPJS_RETRY_SLEEP_MS', 2000),
        'multiplier' => 2, // exponential backoff: 2s, 4s, 8s
    ],

    'polling' => [
        'ward_interval' => env('BPJS_WARD_POLL_INTERVAL', 10),
        'operating_room_interval' => env('BPJS_OR_POLL_INTERVAL', 10),
    ],

];
