<?php

/**
 * Espay Payment Gateway Configuration
 * 
 * Konfigurasi untuk integrasi Espay Payment Gateway
 * Dokumentasi: https://docs.espay.id/pg/
 */

return [
    
    /*
    |--------------------------------------------------------------------------
    | Espay Environment
    |--------------------------------------------------------------------------
    |
    | Tentukan environment yang digunakan: 'sandbox' atau 'production'
    |
    */
    'environment' => env('ESPAY_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Espay API Credentials
    |--------------------------------------------------------------------------
    |
    | Credential API yang diberikan oleh Espay
    |
    */
    'merchant_code' => env('ESPAY_MERCHANT_CODE', 'SGWBINAMULIA'),
    'merchant_name' => env('ESPAY_MERCHANT_NAME', 'Yayasan Bina Mulia'),
    'api_key' => env('ESPAY_API_KEY', '95fd9b93b4dd2268b9283abbb9f2c90a'),
    'signature_key' => env('ESPAY_SIGNATURE_KEY', 'ct9vikymmea2ze1t'),
    'password' => env('ESPAY_PASSWORD', 'KNQKREHV'),

    /*
    |--------------------------------------------------------------------------
    | Espay API URLs
    |--------------------------------------------------------------------------
    |
    | URL endpoint untuk API Espay
    |
    */
    'api_url' => env('ESPAY_ENVIRONMENT', 'sandbox') === 'production' 
        ? 'https://api.espay.id' 
        : 'https://sandbox-api.espay.id',
    
    'api_merchant_url' => env('ESPAY_ENVIRONMENT', 'sandbox') === 'production'
        ? 'https://api-merchant.espay.id'
        : 'https://sandbox-api.espay.id',

    /*
    |--------------------------------------------------------------------------
    | Espay Portal Credentials
    |--------------------------------------------------------------------------
    |
    | Credential untuk akses dashboard Espay (hanya untuk informasi)
    |
    */
    'portal' => [
        'url' => env('ESPAY_ENVIRONMENT', 'sandbox') === 'production'
            ? 'https://portal.espay.id/'
            : 'https://sandbox-portal.espay.id/',
        'cust_id' => env('ESPAY_PORTAL_CUST_ID', 'YBM001'),
        'user_id' => env('ESPAY_PORTAL_USER_ID', '62812237623'),
        'username' => env('ESPAY_PORTAL_USERNAME', 'YBM'),
        'password' => env('ESPAY_PORTAL_PASSWORD', '#Bahrul110300'),
    ],

    /*
    |--------------------------------------------------------------------------
    | RSA Keys Path
    |--------------------------------------------------------------------------
    |
    | Path untuk private dan public key RSA
    | Espay menggunakan Asymmetric Signature (RSA)
    |
    */
    'private_key_path' => storage_path('app/keys/espay/private_key.pem'),
    'public_key_path' => storage_path('app/keys/espay/public_key.pem'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Settings
    |--------------------------------------------------------------------------
    |
    | Pengaturan default untuk transaksi
    |
    */
    'default_expiry_hours' => 24, // Lama waktu expired transaksi (dalam jam)
    'channel_id' => 'ESPAY',
    
    /*
    |--------------------------------------------------------------------------
    | Callback Settings
    |--------------------------------------------------------------------------
    |
    | Pengaturan untuk callback/notification dari Espay
    |
    */
    'callback' => [
        'payment_notification_url' => env('APP_URL') . '/api/espay/callback',
        'verify_signature' => env('ESPAY_VERIFY_SIGNATURE', true),
    ],

];