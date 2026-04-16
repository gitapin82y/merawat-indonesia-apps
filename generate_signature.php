<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$timestamp = '2026-04-15T18:21:00+07:00';
$method = 'POST';
$endpoint = '/api/v1.0/transfer-va/payment';
$body = json_encode([
    'partnerServiceId'   => 'Espay',
    'customerNo'         => 'SGWYAYASANBINAMULIA',
    'virtualAccountNo'   => 'DON-57588-1775910522',
    'virtualAccountName' => 'apin',
    'trxId'              => 'TRX-DOUBLE-001',
    'paymentRequestId'   => 'payreq-double-001',
    'totalAmount'        => ['value' => '25000.00', 'currency' => 'IDR'],
    'trxDateTime'        => '2026-04-15T18:21:00+07:00',
    'additionalInfo'     => ['transactionStatus' => 'S'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$hashedBody = strtolower(hash('sha256', $body));
$stringToSign = $method . ':' . $endpoint . ':' . $hashedBody . ':' . $timestamp;

$privateKey = file_get_contents(storage_path('app/keys/espay/private_key.pem'));
$pkeyId = openssl_pkey_get_private($privateKey);
$signature = '';
openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

echo "Signature: " . base64_encode($signature) . "\n";
echo "Body: " . $body . "\n";


