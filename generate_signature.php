<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$timestamp = '2026-04-11T20:20:00+07:00';
$method = 'POST';
$endpoint = '/apimerchant/v1.0/qr/qr-mpm-generate';
$body = json_encode([
    'partnerReferenceNo' => 'QR-NEG-002-' . time(),
    'merchantId'         => 'SGWYAYASANBINAMULIA',
    'subMerchantId'      => '478e6640ee7aab15364bf42569559a35',
    'amount'             => ['value' => '25000.00', 'currency' => 'IDR'],
    'feeAmount'          => ['value' => '0.00', 'currency' => 'IDR'],
    'validityPeriod'     => '2026-04-12T20:20:00+07:00',
    'additionalInfo'     => (object)[]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$hashedBody = strtolower(hash('sha256', $body));
$stringToSign = $method . ':' . $endpoint . ':' . $hashedBody . ':' . $timestamp;

$privateKey = file_get_contents(storage_path('app/keys/espay/private_key.pem'));
$pkeyId = openssl_pkey_get_private($privateKey);
$signature = '';
openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

echo "Signature: " . base64_encode($signature) . "\n";
echo "Body: " . $body . "\n";