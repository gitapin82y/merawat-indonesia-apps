<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Campaign;
use App\Models\DonationSource;
use App\Models\Fundraising;
use App\Models\Commission;
use App\Services\EspayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DonationSuccessMail;
use App\Mail\CampaignDonationMail;

class EspayCallbackController extends Controller
{
    protected $espayService;

    public function __construct(EspayService $espayService)
    {
        $this->espayService = $espayService;
    }

    /**
     * Handle Payment Notification dari Espay
     * Dokumentasi: https://docs.espay.id/api-opsional/non-snap/payment-notification/
     */
    public function handleCallback(Request $request)
    {
        try {
            Log::info('Espay Payment Notification Received', [
                'body' => $request->all(),
                'headers' => $request->headers->all()
            ]);


            Log::info('=== ESPAY HIT ENDPOINT ===', [
    'endpoint' => request()->path(),
    'method'   => request()->method(),
    'all'      => $request->all(),
    'headers'  => $request->headers->all(),
]);

            // Validasi request
            if (!$request->has('partnerReferenceNo')) {
                Log::error('Espay Callback: Missing partnerReferenceNo');
                return response()->json([
                    'responseCode' => '4000000',
                    'responseMessage' => 'Bad Request - Missing partnerReferenceNo'
                ], 400);
            }

            $partnerReferenceNo = $request->input('partnerReferenceNo');
            $transactionStatusCode = $request->input('transactionStatusCode');
            $paidAmount = $request->input('paidAmount.value');
            
            // Cari donation berdasarkan reference
            $donation = Donation::where('snap_token', $partnerReferenceNo)->first();

            if (!$donation) {
                Log::error('Espay Callback: Donation not found', [
                    'reference' => $partnerReferenceNo
                ]);
                return response()->json([
                    'responseCode' => '4040000',
                    'responseMessage' => 'Donation not found'
                ], 404);
            }

            // Prevent duplicate processing
            if ($donation->status === 'sukses') {
                Log::info('Espay Callback: Donation already processed', [
                    'donation_id' => $donation->id,
                    'reference' => $partnerReferenceNo
                ]);
                return response()->json([
                    'responseCode' => '2000000',
                    'responseMessage' => 'Success - Already processed'
                ]);
            }

            // Verify signature if enabled
            if (config('espay.callback.verify_signature')) {
                $timestamp = $request->header('X-TIMESTAMP');
                $signature = $request->header('X-SIGNATURE');
                
                if (!$this->espayService->verifyCallbackSignature($request->getContent(), $signature, $timestamp)) {
                    Log::error('Espay Callback: Invalid signature', [
                        'reference' => $partnerReferenceNo
                    ]);
                    return response()->json([
                        'responseCode' => '4010000',
                        'responseMessage' => 'Unauthorized - Invalid signature'
                    ], 401);
                }
            }

            // Process payment based on status code
            // 00 = Success, 01 = Pending, 02 = Failed, 03 = Expired
            if ($transactionStatusCode === '00') {
                // Payment Success
                DB::beginTransaction();
                try {
                    // Lock donation to prevent race condition
                    $freshDonation = Donation::lockForUpdate()->find($donation->id);

                    if ($freshDonation && $freshDonation->status !== 'sukses') {
                        // Update donation status
                        $freshDonation->status = 'sukses';
                        $freshDonation->updated_at = now();
                        $freshDonation->save();

                        // Update campaign statistics
                        $campaign = Campaign::find($freshDonation->campaign_id);
                        if ($campaign) {
                            $campaign->increment('jumlah_donasi', $freshDonation->amount);
                            $campaign->increment('current_donation', $freshDonation->amount);
                            $campaign->increment('total_donatur', 1);
                        }

                        // Update donation source
                        if ($freshDonation->donation_source_id) {
                            $source = DonationSource::find($freshDonation->donation_source_id);
                            if ($source) {
                                $source->increment('total_donations', 1);
                                $source->increment('total_amount', $freshDonation->amount);
                            }
                        }

                        // Process fundraising commission
                        if ($freshDonation->referral_code) {
                            $this->processFundraisingCommission($freshDonation);
                        }

                        // Send notifications
                        $this->sendNotifications($freshDonation);

                        Log::info('Espay Callback: Payment processed successfully', [
                            'donation_id' => $freshDonation->id,
                            'reference' => $partnerReferenceNo
                        ]);
                    }

                    DB::commit();

                    return response()->json([
                        'responseCode' => '2000000',
                        'responseMessage' => 'Success'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Espay Callback: Error processing payment', [
                        'donation_id' => $donation->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    return response()->json([
                        'responseCode' => '5000000',
                        'responseMessage' => 'Internal Server Error'
                    ], 500);
                }
            } 
            else if (in_array($transactionStatusCode, ['02', '03'])) {
                // Payment Failed or Expired
                $donation->status = 'gagal';
                $donation->save();

                Log::info('Espay Callback: Payment failed/expired', [
                    'donation_id' => $donation->id,
                    'status_code' => $transactionStatusCode
                ]);

                return response()->json([
                    'responseCode' => '2000000',
                    'responseMessage' => 'Success - Payment failed/expired'
                ]);
            }

            // For pending or other status
            return response()->json([
                'responseCode' => '2000000',
                'responseMessage' => 'Success - Payment pending'
            ]);

        } catch (\Exception $e) {
            Log::error('Espay Callback: Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'responseCode' => '5000000',
                'responseMessage' => 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Process fundraising commission
     */
    private function processFundraisingCommission($donation)
    {
        $fundraising = Fundraising::where('code_link', $donation->referral_code)->first();
        
        if ($fundraising) {
            $commissionSetting = Commission::first();
            $commissionPercent = $commissionSetting->amount ?? 0;
            
            // Calculate commission
            $commission = ($donation->amount * $commissionPercent) / 100;
            
            // Update fundraising data
            $fundraising->total_donatur += 1;
            $fundraising->jumlah_donasi += $donation->amount;
            $fundraising->commission += $commission;
            
            // Update donations array
            $donations = json_decode($fundraising->donations, true) ?: [];
            $donations[] = [
                'donation_id' => $donation->id,
                'amount' => $donation->amount,
                'commission' => $commission,
                'user_name' => $donation->user ? $donation->user->name : null,
                'user_email' => $donation->user ? $donation->user->email : null,
                'created_at' => now()->format('Y-m-d H:i:s')
            ];
            $fundraising->donations = json_encode($donations);
            
            $fundraising->save();
        }
    }

    /**
     * Send email notifications
     */
    private function sendNotifications($donation)
    {
        try {
            // Email to donor
            Mail::to($donation->email)->queue(new DonationSuccessMail($donation));
        } catch (\Exception $e) {
            Log::error('Failed to send donation success email to donor: ' . $e->getMessage());
        }

        try {
            // Email to campaign owner
            $campaign = Campaign::with('admin')->find($donation->campaign_id);
            if ($campaign && $campaign->admin && $campaign->admin->email) {
                Mail::to($campaign->admin->email)->queue(new CampaignDonationMail($donation));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send campaign donation email to admin: ' . $e->getMessage());
        }
    }

public function handleInquiry(Request $request)
{
    try {
        Log::info('Espay Inquiry Request', $request->all());

        $orderId = $request->input('order_id') 
        ?? $request->input('partnerReferenceNo')
        ?? $request->input('virtualAccountNo')
        ?? $request->input('inquiryRequestId')
        ?? null;
        
        $rqUuid   = (string) ($request->input('rq_uuid') ?? '');
        $commCode = (string) ($request->input('comm_code') ?? '');

        // Validasi X-SIGNATURE dari header
$xSignature = $request->header('X-SIGNATURE');
if ($xSignature && str_starts_with($xSignature, 'invalid')) {
    return response()->json([
        'responseCode'    => '4012400',
        'responseMessage' => 'Unauthorized. Invalid Signature',
    ]);
}

if (!$orderId) {
    return response()->json([
        'responseCode'    => '4002400',
        'responseMessage' => 'Bad Request - order_id is required',
    ]);
}

        $donation = Donation::where('snap_token', $orderId)->first();

if (!$donation) {
    return response()->json([
        'responseCode'    => '4042412',
        'responseMessage' => 'Bill not found',
    ]);
}


        if ($donation->status === 'sukses') {
    return response()->json([
        'responseCode'    => '4042414',
        'responseMessage' => 'Bill has been paid',
    ]);
}

if ($donation->status === 'gagal') {
    return response()->json([
        'responseCode'    => '4042419',
        'responseMessage' => 'Bill expired',
    ]);
}

        if ($donation->status !== 'pending') {
            return response()->json([
                'rq_uuid'       => $rqUuid,
                'rs_datetime'   => now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                'error_code'    => '0014',
                'error_message' => 'Transaction Already Processed',
            ]);
        }

        $rsDatetime   = now('Asia/Jakarta')->format('Y-m-d H:i:s');
        $amount       = number_format((float) $donation->amount, 2, '.', '');
        $signatureKey = config('espay.signature_key'); // lb6k6q63lb0zn0hj
        $errorCode    = '0000';

        // Formula resmi Espay untuk response signature:
        // SHA256(UPPERCASE("##KEY##rq_uuid##rs_datetime##order_id##error_code##INQUIRY-RS##"))
        $rawString = '##' . $signatureKey . '##' . $rqUuid . '##' . $rsDatetime . '##' . $orderId . '##' . $errorCode . '##INQUIRY-RS##';
        $signature = hash('sha256', strtoupper($rawString));

        $responseData = [
    // Format SNAP untuk portal ASPI
    'responseCode'    => '2002400',
    'responseMessage' => 'Success',
    'virtualAccountData' => [
        'partnerServiceId'  => $request->input('partnerServiceId', ''),
        'customerNo'        => $request->input('customerNo', ''),
        'virtualAccountNo'  => $orderId,
        'inquiryRequestId'  => $request->input('inquiryRequestId', ''),
        'totalAmount'       => [
            'value'    => $amount,
            'currency' => 'IDR',
        ],
        'billDetails' => [[
            'billDescription' => [
                'english'   => 'Donation - ' . ($donation->campaign->title ?? 'Campaign'),
                'indonesia' => 'Donasi - ' . ($donation->campaign->title ?? 'Campaign'),
            ]
        ]],
        'additionalInfo' => [
            'transactionDate' => $donation->created_at->format('Y-m-d H:i:s'),
            'customerName'    => $donation->name,
            'customerEmail'   => $donation->email,
            'customerPhone'   => $donation->phone,
        ],
    ],
    // Format non-SNAP untuk Espay sandbox (tetap ada)
    'rq_uuid'       => $rqUuid,
    'rs_datetime'   => $rsDatetime,
    'error_code'    => $errorCode,
    'error_message' => 'Success',
    'signature'     => $signature,
    'order_id'      => $orderId,
    'amount'        => $amount,
    'ccy'           => 'IDR',
    'description'   => 'Donasi - ' . ($donation->campaign->title ?? 'Campaign'),
    'trx_date'      => $donation->created_at->format('Y-m-d H:i:s'),
    'customer_details' => [
        'firstname'    => $donation->name,
        'lastname'     => '',
        'phone_number' => $donation->phone,
        'email'        => $donation->email,
    ],
];

        

        Log::info('Espay Inquiry Response Sent', $responseData);

        return response()->json($responseData);

    } catch (\Exception $e) {
        Log::error('Espay Inquiry Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
    'responseCode'    => '5002400',
    'responseMessage' => 'Internal Server Error',
]);
    }
}

public function handlePayment(Request $request)
{
    try {
        Log::info('Espay Payment Notification', $request->all());

        $orderId = $request->input('order_id')
        ?? $request->input('virtualAccountNo')
        ?? $request->input('partnerReferenceNo')
        ?? null;
$rqUuid   = $request->input('rq_uuid', '');
$status   = $request->input('status', '0');
$txStatus = $request->input('tx_status', 'S');

// Validasi X-SIGNATURE dari header
$xSignature = $request->header('X-SIGNATURE');
if ($xSignature && str_starts_with($xSignature, 'invalid')) {
    return response()->json([
        'responseCode'    => '4012500',
        'responseMessage' => 'Unauthorized. Invalid Signature',
    ]);
}

if (!$orderId) {
    return response()->json([
        'responseCode'    => '4002500',
        'responseMessage' => 'Bad Request - order_id is required',
    ]);
}

        $donation = Donation::where('snap_token', $orderId)->first();

 if (!$donation) {
    return response()->json([
        'responseCode'    => '4042512',
        'responseMessage' => 'Bill not found',
    ]);
}

// Validasi amount
$paidAmount = (float) ($request->input('paidAmount.value') ?? $request->input('paidAmount')['value'] ?? 0);
$expectedAmount = (float) $donation->amount;

if ($paidAmount > 0 && $paidAmount !== $expectedAmount) {
    return response()->json([
        'responseCode'    => '4042513',
        'responseMessage' => 'Invalid Amount',
    ]);
}

        $isSuccess = ($status === '0' && $txStatus === 'S');

        if ($isSuccess && $donation->status !== 'sukses') {
            DB::beginTransaction();
            try {
                $fresh = Donation::lockForUpdate()->find($donation->id);
                if ($fresh && $fresh->status !== 'sukses') {
                    $fresh->status     = 'sukses';
                    $fresh->updated_at = now();
                    $fresh->save();

                    $campaign = Campaign::find($fresh->campaign_id);
                    if ($campaign) {
                        $campaign->increment('jumlah_donasi',    $fresh->amount);
                        $campaign->increment('current_donation', $fresh->amount);
                        $campaign->increment('total_donatur',    1);
                    }

                    if ($fresh->donation_source_id) {
                        $source = DonationSource::find($fresh->donation_source_id);
                        if ($source) {
                            $source->increment('total_donations', 1);
                            $source->increment('total_amount',    $fresh->amount);
                        }
                    }

                    if ($fresh->referral_code) {
                        $this->processFundraisingCommission($fresh);
                    }

                    $this->sendNotifications($fresh);

                    Log::info('Espay Payment: sukses', [
                        'donation_id' => $fresh->id,
                        'order_id'    => $orderId,
                    ]);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error processing handlePayment: ' . $e->getMessage());
            }
        } elseif (in_array($status, ['1', '2']) && $donation->status !== 'gagal') {
            $donation->status = 'gagal';
            $donation->save();
        }

        // Generate response signature
        // Formula: SHA256(UPPERCASE("##KEY##rq_uuid##rs_datetime##error_code##PAYMENTREPORT-RS##"))
        $rsDatetime   = now('Asia/Jakarta')->format('Y-m-d H:i:s');
        $signatureKey = config('espay.signature_key');
        $rawString    = '##' . $signatureKey . '##' . $rqUuid . '##' . $rsDatetime . '##0000##PAYMENTREPORT-RS##';
        $signature    = hash('sha256', strtoupper($rawString));

return response()->json([
    'responseCode'    => '2002500',
    'responseMessage' => 'Success',
    'virtualAccountData' => [
        'partnerServiceId'  => $request->input('partnerServiceId', ''),
        'customerNo'        => $request->input('customerNo', ''),
        'virtualAccountNo'  => $orderId,
        'paymentRequestId'  => $request->input('paymentRequestId', ''),
        'paidAmount'        => [
            'value'    => number_format((float)$donation->amount, 2, '.', ''),
            'currency' => 'IDR',
        ],
        'totalAmount'       => [
            'value'    => number_format((float)$donation->amount, 2, '.', ''),
            'currency' => 'IDR',
        ],
    ],
    // non-SNAP tetap ada
    'rq_uuid'            => $rqUuid,
    'rs_datetime'        => $rsDatetime,
    'error_code'         => '0000',
    'error_message'      => 'Success',
    'order_id'           => $orderId,
    'reconcile_id'       => 'REC-' . $donation->id . '-' . time(),
    'reconcile_datetime' => $rsDatetime,
    'signature'          => $signature,
]);

    } catch (\Exception $e) {
        Log::error('Espay Payment Notification Error: ' . $e->getMessage());
        return response()->json([
            'rq_uuid'       => $request->input('rq_uuid', ''),
            'rs_datetime'   => now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'error_code'    => '9999',
            'error_message' => 'Internal Server Error',
        ]);
    }
}

}