<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$timestamp = '2026-04-11T19:54:00+07:00';
$method = 'DELETE';
$endpoint = '/apimerchant/v1.0/transfer-va/delete-va';
$body = json_encode([
    'partnerServiceId' => ' ESPAY',
    'customerNo' => 'SGWYAYASANBINAMULIA',
    'virtualAccountNo' => 'DON-57590-1775912821',
    'trxId' => 'DEL-002-2026',
    'additionalInfo' => (object)[]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$hashedBody = strtolower(hash('sha256', $body));
$stringToSign = $method . ':' . $endpoint . ':' . $hashedBody . ':' . $timestamp;

$privateKey = file_get_contents(storage_path('app/keys/espay/private_key.pem'));
$pkeyId = openssl_pkey_get_private($privateKey);
$signature = '';
openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

echo "Signature: " . base64_encode($signature) . "\n";
echo "String to Sign: " . $stringToSign . "\n";
