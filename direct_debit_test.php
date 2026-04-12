<?php
/**
 * Script Uji Fungsional Direct Debit - Payment Host to Host
 * Yayasan Bina Mulia x Espay
 * 
 * Jalankan via: php artisan tinker --execute="require 'direct_debit_test.php';"
 */

// ── CONFIG ───────────────────────────────────────────────────
$MERCHANT_ID     = 'SGWYAYASANBINAMULIA';
$SUB_MERCHANT_ID = '478e6640ee7aab15364bf42569559a35';
$PRIVATE_KEY_PATH = storage_path('app/keys/espay/private_key.pem');
$API_URL         = 'https://sandbox-api.espay.id/apimerchant/v1.0/debit/payment-host-to-host';
$CHANNEL_ID      = 'ESPAY';

// Donasi real dari website (sudah sukses & tercatat di log Espay)
$REAL_DONATION_ID       = 57632;
$REAL_PARTNER_REF       = 'DON-57632-1775985035';
$REAL_AMOUNT            = '25000.00';
$REAL_DONOR_NAME        = 'apin';
$REAL_DONOR_EMAIL       = 'uix.apin@gmail.com';
$REAL_DONOR_PHONE       = '081231548925';

// ── HELPERS ──────────────────────────────────────────────────
function generateTimestamp() {
    return \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
}

function generateExternalId() {
    return \Illuminate\Support\Str::uuid()->toString();
}

function generateSignature($method, $endpoint, $requestBodyJson, $timestamp, $privateKeyPath) {
    $minifiedBody = json_encode(json_decode($requestBodyJson), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $hashedBody   = strtolower(hash('sha256', $minifiedBody));
    $stringToSign = $method . ':' . $endpoint . ':' . $hashedBody . ':' . $timestamp;

    $privateKey = file_get_contents($privateKeyPath);
    $pkeyId     = openssl_pkey_get_private($privateKey);
    $signature  = '';
    openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

    return base64_encode($signature);
}

function buildHeaders($timestamp, $signature, $externalId, $merchantId, $channelId) {
    return [
        'Content-Type'  => 'application/json',
        'X-TIMESTAMP'   => $timestamp,
        'X-SIGNATURE'   => $signature,
        'X-EXTERNAL-ID' => $externalId,
        'X-PARTNER-ID'  => $merchantId,
        'CHANNEL-ID'    => $channelId,
    ];
}

function sendRequest($url, $headers, $body) {
    $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
        ->post($url, json_decode($body, true));
    return [
        'status'  => $response->status(),
        'headers' => $response->headers(),
        'body'    => $response->json(),
    ];
}

function printResult($no, $scenario, $headers, $bodyJson, $response) {
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "TEST CASE {$no}: {$scenario}\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "--- REQUEST ---\n";
    foreach ($headers as $k => $v) echo "{$k}: {$v}\n";
    echo "\n" . json_encode(json_decode($bodyJson), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    echo "\n--- RESPONSE (HTTP {$response['status']}) ---\n";
    
    // Response headers
    $resHeaders = $response['headers'];
    $printHeaders = ['server', 'date', 'content-type', 'transfer-encoding', 'connection', 'x-timestamp', 'x-signature'];
    foreach ($printHeaders as $h) {
        $val = is_array($resHeaders[$h] ?? null) ? ($resHeaders[$h][0] ?? '') : ($resHeaders[$h] ?? '');
        if ($val !== '') echo ucwords(str_replace('-', ' ', $h), ' ') . ": $val\n";
    }
    echo "\n" . json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

$endpoint = '/apimerchant/v1.0/debit/payment-host-to-host';

// Base body menggunakan data donasi real
$baseBody = [
    'partnerReferenceNo' => $REAL_PARTNER_REF,
    'merchantId'         => $MERCHANT_ID,
    'subMerchantId'      => $SUB_MERCHANT_ID,
    'amount'             => ['value' => $REAL_AMOUNT, 'currency' => 'IDR'],
    'urlParam'           => [
        'url'        => 'https://merawatindonesia.com/donations/' . $REAL_DONATION_ID . '/status',
        'type'       => 'PAY_RETURN',
        'isDeeplink' => 'N',
    ],
    'validUpTo'         => \Carbon\Carbon::now('Asia/Jakarta')->addHours(24)->format('Y-m-d\TH:i:sP'),
    'pointOfInitiation' => 'Website',
    'payOptionDetails'  => [
        'payMethod'   => '014',
        'payOption'   => 'BCAATM',
        'transAmount' => ['value' => $REAL_AMOUNT, 'currency' => 'IDR'],
        'feeAmount'   => ['value' => '0.00', 'currency' => 'IDR'],
    ],
    'additionalInfo' => [
        'payType'    => 'REDIRECT',
        'userName'   => $REAL_DONOR_NAME,
        'userEmail'  => $REAL_DONOR_EMAIL,
        'userPhone'  => $REAL_DONOR_PHONE,
        'inquiryUrl' => 'https://merawatindonesia.com/api/v1.0/transfer-va/inquiry',
        'paymentUrl' => 'https://merawatindonesia.com/api/v1.0/transfer-va/payment',
        'callbackUrl'=> 'https://merawatindonesia.com/api/espay/callback',
    ],
];

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║  UJI FUNGSIONAL - DIRECT DEBIT (PAYMENT HOST TO HOST)    ║\n";
echo "║  Nama Penyedia: Yayasan Bina Mulia                        ║\n";
echo "║  Merchant ID  : SGWYAYASANBINAMULIA                       ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";

// ────────────────────────────────────────────────────────────
// TC 19.1 - Not Applicable for S2B Pay
// ────────────────────────────────────────────────────────────
echo "\n═══════════════════════════════════════════════════════════\n";
echo "TEST CASE 19.1: Access Token Invalid\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "--- REQUEST ---\nNot Applicable for S2B Pay\n";
echo "--- RESPONSE ---\nNot Applicable for S2B Pay\n";
echo "--- RESULT ---\nN/A\n";

// ────────────────────────────────────────────────────────────
// TC 19.2 - Unauthorized Signature
// ────────────────────────────────────────────────────────────
$timestamp  = generateTimestamp();
$externalId = generateExternalId();
$bodyJson   = json_encode($baseBody, JSON_UNESCAPED_SLASHES);
$validSig   = generateSignature('POST', $endpoint, $bodyJson, $timestamp, $PRIVATE_KEY_PATH);
$invalidSig = 'invalid' . $validSig;
$headers    = buildHeaders($timestamp, $invalidSig, $externalId, $MERCHANT_ID, $CHANNEL_ID);
$response   = sendRequest($API_URL, $headers, $bodyJson);

printResult('19.2', 'Unauthorized Signature', $headers, $bodyJson, $response);
$pass = $response['status'] === 401 || ($response['body']['responseCode'] ?? '') === '4015400';
echo "--- RESULT ---\n" . ($pass ? "PASS ✓" : "FAIL ✗") . "\n";

// ────────────────────────────────────────────────────────────
// TC 19.3 - Missing Mandatory Field (hapus merchantId)
// ────────────────────────────────────────────────────────────
$bodyMissing = $baseBody;
unset($bodyMissing['merchantId']);
$bodyMissingJson = json_encode($bodyMissing, JSON_UNESCAPED_SLASHES);
$timestamp  = generateTimestamp();
$externalId = generateExternalId();
$sig        = generateSignature('POST', $endpoint, $bodyMissingJson, $timestamp, $PRIVATE_KEY_PATH);
$headers    = buildHeaders($timestamp, $sig, $externalId, $MERCHANT_ID, $CHANNEL_ID);
$response   = sendRequest($API_URL, $headers, $bodyMissingJson);

printResult('19.3', 'Missing Mandatory Field (merchantId dihapus)', $headers, $bodyMissingJson, $response);
$pass = $response['status'] === 400 || ($response['body']['responseCode'] ?? '') === '4005402';
echo "--- RESULT ---\n" . ($pass ? "PASS ✓" : "FAIL ✗") . "\n";

// ────────────────────────────────────────────────────────────
// TC 19.4 - Invalid Field Format
// ────────────────────────────────────────────────────────────
$bodyInvalid = $baseBody;
$bodyInvalid['partnerReferenceNo'] = '@#INVALID!FORMAT%';
$bodyInvalidJson = json_encode($bodyInvalid, JSON_UNESCAPED_SLASHES);
$timestamp  = generateTimestamp();
$externalId = generateExternalId();
$sig        = generateSignature('POST', $endpoint, $bodyInvalidJson, $timestamp, $PRIVATE_KEY_PATH);
$headers    = buildHeaders($timestamp, $sig, $externalId, $MERCHANT_ID, $CHANNEL_ID);
$response   = sendRequest($API_URL, $headers, $bodyInvalidJson);

printResult('19.4', 'Invalid Field Format (partnerReferenceNo berisi simbol)', $headers, $bodyInvalidJson, $response);
$pass = $response['status'] === 400 || ($response['body']['responseCode'] ?? '') === '4005401';
echo "--- RESULT ---\n" . ($pass ? "PASS ✓" : "FAIL ✗") . "\n";

// ────────────────────────────────────────────────────────────
// TC 19.5 - Cannot use the same X-EXTERNAL-ID
// ────────────────────────────────────────────────────────────
$sameExternalId = generateExternalId();

// Request 1 — pakai partnerReferenceNo unik
$bodyDup = $baseBody;
$bodyDup['partnerReferenceNo'] = 'DON-DUP1-' . time();
$bodyDupJson = json_encode($bodyDup, JSON_UNESCAPED_SLASHES);
$timestamp1 = generateTimestamp();
$sig1       = generateSignature('POST', $endpoint, $bodyDupJson, $timestamp1, $PRIVATE_KEY_PATH);
$headers1   = buildHeaders($timestamp1, $sig1, $sameExternalId, $MERCHANT_ID, $CHANNEL_ID);
$response1  = sendRequest($API_URL, $headers1, $bodyDupJson);

sleep(1);

// Request 2 — X-EXTERNAL-ID sama
$bodyDup2 = $baseBody;
$bodyDup2['partnerReferenceNo'] = 'DON-DUP2-' . time();
$bodyDup2Json = json_encode($bodyDup2, JSON_UNESCAPED_SLASHES);
$timestamp2 = generateTimestamp();
$sig2       = generateSignature('POST', $endpoint, $bodyDup2Json, $timestamp2, $PRIVATE_KEY_PATH);
$headers2   = buildHeaders($timestamp2, $sig2, $sameExternalId, $MERCHANT_ID, $CHANNEL_ID);
$response2  = sendRequest($API_URL, $headers2, $bodyDup2Json);

echo "\n═══════════════════════════════════════════════════════════\n";
echo "TEST CASE 19.5: Cannot use the same X-EXTERNAL-ID\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "--- REQUEST 1 (X-EXTERNAL-ID: {$sameExternalId}) ---\n";
foreach ($headers1 as $k => $v) echo "{$k}: {$v}\n";
echo "\n" . json_encode(json_decode($bodyDupJson), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "\n--- RESPONSE 1 (HTTP {$response1['status']}) ---\n";
echo json_encode($response1['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "\n--- REQUEST 2 (X-EXTERNAL-ID sama: {$sameExternalId}) ---\n";
foreach ($headers2 as $k => $v) echo "{$k}: {$v}\n";
echo "\n" . json_encode(json_decode($bodyDup2Json), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "\n--- RESPONSE 2 (HTTP {$response2['status']}) ---\n";
echo json_encode($response2['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
$pass = $response2['status'] === 409 || ($response2['body']['responseCode'] ?? '') === '4095400';
echo "--- RESULT ---\n" . ($pass ? "PASS ✓" : "FAIL ✗") . "\n";

// ────────────────────────────────────────────────────────────
// TC 19.6 - Direct Debit Payment Sukses (donasi real)
// ────────────────────────────────────────────────────────────
$bodySuccess = $baseBody;
$bodySuccess['partnerReferenceNo'] = $REAL_PARTNER_REF;
$bodySuccessJson = json_encode($bodySuccess, JSON_UNESCAPED_SLASHES);
$timestamp  = generateTimestamp();
$externalId = generateExternalId();
$sig        = generateSignature('POST', $endpoint, $bodySuccessJson, $timestamp, $PRIVATE_KEY_PATH);
$headers    = buildHeaders($timestamp, $sig, $externalId, $MERCHANT_ID, $CHANNEL_ID);
$response   = sendRequest($API_URL, $headers, $bodySuccessJson);

printResult('19.6', 'Direct Debit Payment - debit account terdaftar (SUKSES)', $headers, $bodySuccessJson, $response);
$rc   = $response['body']['responseCode'] ?? '';
$pass = $rc === '2005400';
echo "--- RESULT ---\n" . ($pass ? "PASS ✓" : "FAIL ✗ - Got: {$rc}") . "\n";

echo "\n\n═══════════════════════════════════════════════════════════\n";
echo "SELESAI - Copy request & response di atas ke kolom Excel\n";
echo "═══════════════════════════════════════════════════════════\n";