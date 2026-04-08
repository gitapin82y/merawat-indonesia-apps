<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Moota API Key
    |--------------------------------------------------------------------------
    | Bearer token dari dashboard Moota: Integrasi > API Token
    */
    'api_key' => env('MOOTA_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Moota Webhook Secret Token
    |--------------------------------------------------------------------------
    | Secret token yang didaftarkan saat membuat webhook di Moota dashboard.
    | Digunakan untuk verifikasi signature setiap push webhook.
    | Biarkan kosong untuk skip verifikasi (tidak direkomendasikan production).
    */
    'webhook_secret' => env('MOOTA_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Moota API Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => 'https://app.moota.co/api/v2',
];