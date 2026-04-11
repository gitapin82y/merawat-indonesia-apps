<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$timestamp = '2026-04-11T19:00:00+07:00';
$method = 'POST';
$endpoint = '/apimerchant/v1.0/transfer-va/status';
$body = json_encode([
    'partnerServiceId' => 'SGWYAYASANBINAMULIA',
    'customerNo' => 'SGWYAYASANBINAMULIA',
    'virtualAccountNo' => '00X32ytGuQgcuS0g2jPqAN6tgBGhXXJK',
    'inquiryRequestId' => 'req-pos-006',
    'paymentRequestId' => 'payreq-pos-010',
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