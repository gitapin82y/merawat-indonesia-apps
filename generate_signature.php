<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$timestamp = '2026-04-12T08:13:00+07:00';
$method = 'POST';
$endpoint = '/api/v1.0/qr/qr-mpm-generate';
$body = json_encode([
    'partnerReferenceNo' => 'QR-POS-006-' . time(),
    'amount'             => ['value' => '25000.00', 'currency' => 'IDR'],
    'feeAmount'          => ['value' => '0.00', 'currency' => 'IDR'],
    'merchantId'         => 'merch00001',
    'validityPeriod'     => '2026-04-13T08:13:00+07:00',
    'additionalInfo'     => [
        'deviceId' => '12345679237',
        'channel'  => 'mobilephone',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$hashedBody = strtolower(hash('sha256', $body));
$stringToSign = $method . ':' . $endpoint . ':' . $hashedBody . ':' . $timestamp;

$privateKey = file_get_contents(storage_path('app/keys/espay/private_key.pem'));
$pkeyId = openssl_pkey_get_private($privateKey);
$signature = '';
openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

echo "Signature: " . base64_encode($signature) . "\n";
echo "Body: " . $body . "\n";