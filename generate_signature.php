<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$timestamp = '2026-04-12T15:10:00+07:00';
$method = 'POST';
$endpoint = '/api/v1.0/qr/qr-mpm-generate';
$body = json_encode([
    'partnerReferenceNo' => 'DON-57628-1775980884',
    'merchantId'         => 'SGWYAYASANBINAMULIA',
    'amount'             => ['value' => '25000.00', 'currency' => 'IDR'],
    'additionalInfo'     => ['productCode' => 'QRIS'],
    'validityPeriod'     => '2026-04-13T14:52:00+07:00',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$hashedBody = strtolower(hash('sha256', $body));
$stringToSign = $method . ':' . $endpoint . ':' . $hashedBody . ':' . $timestamp;

$privateKey = file_get_contents(storage_path('app/keys/espay/private_key.pem'));
$pkeyId = openssl_pkey_get_private($privateKey);
$signature = '';
openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

echo "Signature: " . base64_encode($signature) . "\n";
echo "Body: " . $body . "\n";
