<?php

$MERCHANT_ID     = 'SGWYAYASANBINAMULIA';
$PRIVATE_KEY_PATH = storage_path('app/keys/espay/private_key.pem');
$API_URL  = 'https://sandbox-api.espay.id/api/v1.0/qr/qr-mpm-generate';
$endpoint = '/api/v1.0/qr/qr-mpm-generate';
$CHANNEL_ID      = 'ESPAY';

$timestamp  = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
$externalId = \Illuminate\Support\Str::uuid()->toString();

$body = json_encode([
    'partnerReferenceNo' => 'DON-57710-1776170401',
    'merchantId'         => $MERCHANT_ID,
    'amount'             => ['value' => '25000.00', 'currency' => 'IDR'],
    'additionalInfo'     => ['productCode' => 'SALDOMUQR'],
    'validityPeriod'     => \Carbon\Carbon::now('Asia/Jakarta')->addHours(24)->format('Y-m-d\TH:i:sP'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// Generate signature
$minifiedBody = json_encode(json_decode($body), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$hashedBody   = strtolower(hash('sha256', $minifiedBody));
$stringToSign = 'POST:/api/v1.0/qr/qr-mpm-generate:' . $hashedBody . ':' . $timestamp;

$privateKey = file_get_contents($PRIVATE_KEY_PATH);
$pkeyId     = openssl_pkey_get_private($privateKey);
$signature  = '';
openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);
$signatureBase64 = base64_encode($signature);

echo "=== REQUEST ===\n";
echo "Timestamp: $timestamp\n";
echo "External ID: $externalId\n";
echo "StringToSign: $stringToSign\n";
echo "Signature: $signatureBase64\n";
echo "Body: $body\n\n";

$response = \Illuminate\Support\Facades\Http::withHeaders([
    'Content-Type'  => 'application/json',
    'X-TIMESTAMP'   => $timestamp,
    'X-SIGNATURE'   => $signatureBase64,
    'X-EXTERNAL-ID' => $externalId,
    'X-PARTNER-ID'  => $MERCHANT_ID,
    'CHANNEL-ID'    => $CHANNEL_ID,
])->post($API_URL, json_decode($body, true));

echo "=== RESPONSE (HTTP {$response->status()}) ===\n";
echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";