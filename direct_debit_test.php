<?php
/**
 * Script untuk generate request Uji Fungsional Direct Debit
 * Jalankan via: php artisan tinker
 * Atau: php generate_test_requests.php
 */

// ── CONFIG ───────────────────────────────────────────────────
$MERCHANT_ID    = 'SGWYAYASANBINAMULIA';
$SUB_MERCHANT_ID = '478e6640ee7aab15364bf42569559a35';
$PRIVATE_KEY_PATH = storage_path('app/keys/espay/private_key.pem');
$API_URL        = 'https://sandbox-api.espay.id/apimerchant/v1.0/debit/payment-host-to-host';
$CHANNEL_ID     = 'ESPAY';

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
        'Content-Type'   => 'application/json',
        'X-TIMESTAMP'    => $timestamp,
        'X-SIGNATURE'    => $signature,
        'X-EXTERNAL-ID'  => $externalId,
        'X-PARTNER-ID'   => $merchantId,
        'CHANNEL-ID'     => $channelId,
    ];
}

function printTest($no, $scenario, $headers, $body, $response) {
    echo "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "TEST CASE {$no}: {$scenario}\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "--- REQUEST ---\n";
    foreach ($headers as $k => $v) echo "{$k}: {$v}\n";
    echo "\n" . json_encode(json_decode($body), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    echo "\n--- RESPONSE ---\n";
    if (is_array($response)) {
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        echo $response . "\n";
    }
}

function sendRequest($url, $headers, $body) {
    $response = \Illuminate\Support\Facades\Http::withHeaders($headers)->post($url, json_decode($body, true));
    return [
        'status'   => $response->status(),
        'headers'  => [
            'Content-Type' => 'application/json',
            'X-TIMESTAMP'  => now()->format('Y-m-d\TH:i:sP'),
        ],
        'body'     => $response->json(),
    ];
}

$endpoint = '/apimerchant/v1.0/debit/payment-host-to-host';

// Base body yang valid
$baseBody = [
    'partnerReferenceNo' => 'YBMTEST-' . time(),
    'merchantId'         => $MERCHANT_ID,
    'subMerchantId'      => $SUB_MERCHANT_ID,
    'amount'             => ['value' => '25000.00', 'currency' => 'IDR'],
    'urlParam'           => [
        'url'        => 'https://merawatindonesia.com/donations/status',
        'type'       => 'PAY_RETURN',
        'isDeeplink' => 'N',
    ],
    'validUpTo'          => \Carbon\Carbon::now('Asia/Jakarta')->addHours(24)->format('Y-m-d\TH:i:sP'),
    'pointOfInitiation'  => 'Website',
    'payOptionDetails'   => [
        'payMethod'   => '014',
        'payOption'   => 'BCAATM',
        'transAmount' => ['value' => '25000.00', 'currency' => 'IDR'],
        'feeAmount'   => ['value' => '0.00', 'currency' => 'IDR'],
    ],
    'additionalInfo'     => [
        'payType'    => 'REDIRECT',
        'userName'   => 'Donatur Test',
        'userEmail'  => 'test@merawatindonesia.com',
        'userPhone'  => '081234567890',
        'inquiryUrl' => 'https://merawatindonesia.com/api/espay/inquiry',
        'paymentUrl' => 'https://merawatindonesia.com/api/espay/payment',
        'callbackUrl'=> 'https://merawatindonesia.com/api/espay/callback',
    ],
];

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║   UJI FUNGSIONAL - API DIRECT DEBIT (PAYMENT HOST TO HOST) ║\n";
echo "║   Nama Penyedia: Yayasan Bina Mulia                       ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";

// ────────────────────────────────────────────────────────────
// 19.1 - Not Applicable for S2B Pay
// ────────────────────────────────────────────────────────────
echo "\n═══════════════════════════════════════════════════════════\n";
echo "TEST CASE 19.1: Access Token Invalid\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "--- REQUEST ---\nNot Applicable for S2B Pay\n";
echo "--- RESPONSE ---\nNot Applicable for S2B Pay\n";
echo "--- RESULT ---\nN/A\n";

// ────────────────────────────────────────────────────────────
// 19.2 - Unauthorized Signature (invalid signature)
// ────────────────────────────────────────────────────────────
$timestamp  = generateTimestamp();
$externalId = generateExternalId();
$bodyJson   = json_encode($baseBody);

// Generate valid signature dulu, lalu tambah "invalid" di depan
$validSig    = generateSignature('POST', $endpoint, $bodyJson, $timestamp, $PRIVATE_KEY_PATH);
$invalidSig  = 'invalid' . $validSig;

$headers = buildHeaders($timestamp, $invalidSig, $externalId, $MERCHANT_ID, $CHANNEL_ID);
$response = sendRequest($API_URL, $headers, $bodyJson);

echo "\n═══════════════════════════════════════════════════════════\n";
echo "TEST CASE 19.2: Unauthorized Signature\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "--- REQUEST ---\n";
foreach ($headers as $k => $v) echo "{$k}: {$v}\n";
echo "\n" . json_encode(json_decode($bodyJson), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "\n--- RESPONSE (HTTP {$response['status']}) ---\n";
echo json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "--- RESULT ---\n";
echo ($response['status'] === 401 ? "PASS ✓" : "FAIL ✗ - Expected 401, got {$response['status']}") . "\n";

// ────────────────────────────────────────────────────────────
// 19.3 - Missing Mandatory Field (hapus merchantId)
// ────────────────────────────────────────────────────────────
$bodyMissing = $baseBody;
unset($bodyMissing['merchantId']); // hapus field wajib
$bodyMissingJson = json_encode($bodyMissing);

$timestamp  = generateTimestamp();
$externalId = generateExternalId();
$sig        = generateSignature('POST', $endpoint, $bodyMissingJson, $timestamp, $PRIVATE_KEY_PATH);
$headers    = buildHeaders($timestamp, $sig, $externalId, $MERCHANT_ID, $CHANNEL_ID);
$response   = sendRequest($API_URL, $headers, $bodyMissingJson);

echo "\n═══════════════════════════════════════════════════════════\n";
echo "TEST CASE 19.3: Missing Mandatory Field (merchantId dihapus)\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "--- REQUEST ---\n";
foreach ($headers as $k => $v) echo "{$k}: {$v}\n";
echo "\n" . json_encode(json_decode($bodyMissingJson), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "\n--- RESPONSE (HTTP {$response['status']}) ---\n";
echo json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "--- RESULT ---\n";
echo ($response['status'] === 400 ? "PASS ✓" : "FAIL ✗ - Expected 400, got {$response['status']}") . "\n";

// ────────────────────────────────────────────────────────────
// 19.4 - Invalid Field Format (partnerReferenceNo pakai simbol)
// ────────────────────────────────────────────────────────────
$bodyInvalid = $baseBody;
$bodyInvalid['partnerReferenceNo'] = '@#INVALID!FORMAT%'; // format tidak valid
$bodyInvalidJson = json_encode($bodyInvalid);

$timestamp  = generateTimestamp();
$externalId = generateExternalId();
$sig        = generateSignature('POST', $endpoint, $bodyInvalidJson, $timestamp, $PRIVATE_KEY_PATH);
$headers    = buildHeaders($timestamp, $sig, $externalId, $MERCHANT_ID, $CHANNEL_ID);
$response   = sendRequest($API_URL, $headers, $bodyInvalidJson);

echo "\n═══════════════════════════════════════════════════════════\n";
echo "TEST CASE 19.4: Invalid Field Format (partnerReferenceNo berisi simbol)\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "--- REQUEST ---\n";
foreach ($headers as $k => $v) echo "{$k}: {$v}\n";
echo "\n" . json_encode(json_decode($bodyInvalidJson), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "\n--- RESPONSE (HTTP {$response['status']}) ---\n";
echo json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "--- RESULT ---\n";
echo ($response['status'] === 400 ? "PASS ✓" : "FAIL ✗ - Expected 400, got {$response['status']}") . "\n";

// ────────────────────────────────────────────────────────────
// 19.5 - Cannot use the same X-EXTERNAL-ID
// ────────────────────────────────────────────────────────────
// Kirim request pertama (sukses)
$sameExternalId = generateExternalId();
$bodyDup        = $baseBody;
$bodyDup['partnerReferenceNo'] = 'YBMDUP-' . time();
$bodyDupJson    = json_encode($bodyDup);

$timestamp = generateTimestamp();
$sig       = generateSignature('POST', $endpoint, $bodyDupJson, $timestamp, $PRIVATE_KEY_PATH);
$headers   = buildHeaders($timestamp, $sig, $sameExternalId, $MERCHANT_ID, $CHANNEL_ID);
$response1 = sendRequest($API_URL, $headers, $bodyDupJson);

// Kirim request kedua dengan X-EXTERNAL-ID yang sama
sleep(1);
$bodyDup2   = $baseBody;
$bodyDup2['partnerReferenceNo'] = 'YBMDUP2-' . time();
$bodyDup2Json = json_encode($bodyDup2);

$timestamp2 = generateTimestamp();
$sig2       = generateSignature('POST', $endpoint, $bodyDup2Json, $timestamp2, $PRIVATE_KEY_PATH);
$headers2   = buildHeaders($timestamp2, $sig2, $sameExternalId, $MERCHANT_ID, $CHANNEL_ID); // sama!
$response2  = sendRequest($API_URL, $headers2, $bodyDup2Json);

echo "\n═══════════════════════════════════════════════════════════\n";
echo "TEST CASE 19.5: Cannot use the same X-EXTERNAL-ID\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "--- REQUEST 1 (X-EXTERNAL-ID: {$sameExternalId}) ---\n";
foreach ($headers as $k => $v) echo "{$k}: {$v}\n";
echo "\n" . json_encode(json_decode($bodyDupJson), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "\n--- RESPONSE 1 (HTTP {$response1['status']}) ---\n";
echo json_encode($response1['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

echo "\n--- REQUEST 2 (X-EXTERNAL-ID sama: {$sameExternalId}) ---\n";
foreach ($headers2 as $k => $v) echo "{$k}: {$v}\n";
echo "\n" . json_encode(json_decode($bodyDup2Json), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "\n--- RESPONSE 2 (HTTP {$response2['status']}) ---\n";
echo json_encode($response2['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "--- RESULT ---\n";
echo ($response2['status'] === 409 ? "PASS ✓" : "FAIL ✗ - Expected 409, got {$response2['status']}") . "\n";

// ────────────────────────────────────────────────────────────
// 19.6 - Direct Debit Payment sukses
// ────────────────────────────────────────────────────────────
$bodySuccess = $baseBody;
$bodySuccess['partnerReferenceNo'] = 'DON-57632-1775985035';
$bodySuccessJson = json_encode($bodySuccess);

$timestamp  = generateTimestamp();
$externalId = generateExternalId();
$sig        = generateSignature('POST', $endpoint, $bodySuccessJson, $timestamp, $PRIVATE_KEY_PATH);
$headers    = buildHeaders($timestamp, $sig, $externalId, $MERCHANT_ID, $CHANNEL_ID);
$response   = sendRequest($API_URL, $headers, $bodySuccessJson);

echo "\n═══════════════════════════════════════════════════════════\n";
echo "TEST CASE 19.6: Direct Debit Payment - debit account terdaftar (SUKSES)\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "--- REQUEST ---\n";
foreach ($headers as $k => $v) echo "{$k}: {$v}\n";
echo "\n" . json_encode(json_decode($bodySuccessJson), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "\n--- RESPONSE (HTTP {$response['status']}) ---\n";
echo json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "--- RESULT ---\n";
$rc = $response['body']['responseCode'] ?? '';
echo ($rc === '2005400' ? "PASS ✓" : "FAIL ✗ - Expected responseCode 2005400, got {$rc}") . "\n";

echo "\n\n═══════════════════════════════════════════════════════════\n";
echo "SELESAI - Copy request & response di atas ke kolom Excel\n";
echo "═══════════════════════════════════════════════════════════\n";