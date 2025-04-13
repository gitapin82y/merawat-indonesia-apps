<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Campaign;
use App\Models\Fundraising;
use App\Models\Commission;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DonationController extends Controller
{
    protected $apiKey;
    protected $privateKey;
    protected $merchantCode;
    protected $apiUrl;

    public function __construct()
    {
        // Konfigurasi Tripay
        $this->apiKey = env('TRIPAY_API_KEY');
        $this->privateKey = env('TRIPAY_PRIVATE_KEY');
        $this->merchantCode = env('TRIPAY_MERCHANT_CODE');
        $this->apiUrl = env('TRIPAY_API_URL', 'https://tripay.co.id/api-sandbox/');
    }

    public function showDonationForm($title)
    {
        $campaign = Campaign::where('title',$title)->first();
        
        // Ambil daftar channel pembayaran dari Tripay
        $channels = $this->getPaymentChannels();

        return view('donatur.donasi.index', compact('campaign', 'channels'));
    }

    public function processDonation(Request $request)
    {

        if($request->has('is_anonymous') && $request['is_anonymous'] == "on"){
            $request['is_anonymous'] = true;
        }else{
            $request['is_anonymous'] = false;
        }

        // Validasi input
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'amount' => 'required|numeric|min:10000',
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'required',
            'is_anonymous' => 'nullable',
            'doa' => 'nullable|string',
            'payment_method' => 'required|string'
        ]);
        
        $campaign = Campaign::findOrFail($request->campaign_id);
        
        // Cek apakah ada referral code di session
        $referralCode = session('referral_code');
        $fundraisingId = null;
        
        // Jika ada referral code, dapatkan fundraisingId
        if ($referralCode) {
            $fundraising = Fundraising::where('code_link', $referralCode)->first();
            if ($fundraising) {
                $fundraisingId = $fundraising->id;
            }
        }

        
        // Buat donasi baru dengan status pending
        $donation = Donation::create([
            'campaign_id' => $request->campaign_id,
            'user_id' => auth()->id(), // Jika user login
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'doa' => $request->doa,
            'is_anonymous' => $request->has('is_anonymous'),
            'amount' => $request->amount,
            'payment_type' => 'payment_gateway',
            'payment_method' => $request->payment_method,
            'status' => 'pending',
            'snap_token' => Str::random(32) // Placeholder untuk snap_token
        ]);


        
        // Buat transaksi di Tripay
        $transaction = $this->createTransaction($donation, $campaign);
        
        if (isset($transaction['success']) && $transaction['success'] && isset($transaction['data'])) {
            // Update donasi dengan reference dan checkout URL
            $donation->snap_token = $transaction['data']['reference'];
            $donation->save();
            
            // Redirect ke halaman pembayaran Tripay
            return redirect($transaction['data']['checkout_url']);
        } else {
            // Jika gagal, hapus donasi dan tampilkan error
            $donation->delete();
            return redirect()->back()->with('error', 'Gagal membuat transaksi pembayaran: ' . ($transaction['message'] ?? 'Terjadi kesalahan sistem'));
        }
    }

    protected function getPaymentChannels()
    {
        try {
            // Pastikan URL berakhir dengan slash
            $apiUrl = rtrim($this->apiUrl, '/') . '/';
            $endpoint = 'merchant/payment-channel';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($apiUrl . $endpoint);
    
            // Log full URL dan response
            Log::info('Tripay Request URL: ' . $apiUrl . $endpoint);
            Log::info('Tripay Response: ' . $response->body());
            
            if ($response->successful() && isset($response['success']) && $response['success']) {
                return $response['data'];
            }
            
            Log::error('Tripay payment channels error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting payment channels: ' . $e->getMessage());
            return [];
        }
    }



    protected function createTransaction($donation, $campaign)
    {
        $merchantRef = 'DON-' . $donation->id . '-' . time();
        $amount = (int)$donation->amount;
        $donaturName = $donation->is_anonymous ? 'Orang Baik' : $donation->name;
        
        $data = [
            'method' => $donation->payment_method,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $donaturName,
            'customer_email' => $donation->email,
            'customer_phone' => $donation->phone,
            'order_items' => [
                [
                    'name' => 'Donasi untuk ' . $campaign->title,
                    'price' => $amount,
                    'quantity' => 1
                ]
            ],
            'callback_url' => route('tripay.callback'),
            'return_url' => route('donations.status', ['id' => $donation->id]),
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature' => hash_hmac('sha256', $this->merchantCode . $merchantRef . $amount, $this->privateKey)
        ];

        try {
            $apiUrl = rtrim($this->apiUrl, '/') . '/';
            $endpoint = 'transaction/create';
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->post($apiUrl . $endpoint, $data);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error creating transaction: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function callback(Request $request)
    {
        // // Ambil data callback dari Tripay
        // $callbackSignature = $request->header('X-Callback-Signature');
        // $json = $request->getContent();
        
        // // Verifikasi signature
        // $signature = hash_hmac('sha256', $json, $this->privateKey);
        
        // if ($signature !== $callbackSignature) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid signature'
        //     ], 400);
        // }
        
        // $data = json_decode($json, true);
        
        // // Validasi data
        // if (!isset($data['reference'])) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'No reference found'
        //     ], 400);
        // }
        
        // // Cari donasi berdasarkan reference
        // $donation = Donation::where('snap_token', $data['reference'])->first();
        
        // if (!$donation) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Donation not found'
        //     ], 404);
        // }
        
        // // Update status donasi
        // if ($data['status'] == 'PAID') {
        //     $donation->status = 'sukses';
            
        //     // Update data campaign
        //     $campaign = $donation->campaign;
        //     $campaign->jumlah_donasi += $donation->amount;
        //     $campaign->total_donatur += 1;
        //     $campaign->save();
            
        //     // Jika ada fundraising, update data fundraising
        //     if ($donation->fundraising_id) {
        //         $fundraising = Fundraising::find($donation->fundraising_id);
                
        //         if ($fundraising) {
        //             // Hitung komisi 10%
        //             $commission = $donation->amount * 0.1;
                    
        //             // Update data fundraising
        //             $fundraising->total_donatur += 1;
        //             $fundraising->jumlah_donasi += $donation->amount;
        //             $fundraising->commission += $commission;
                    
        //             // Update array donations
        //             $donations = json_decode($fundraising->donations, true) ?: [];
        //             $donations[] = [
        //                 'donation_id' => $donation->id,
        //                 'amount' => $donation->amount,
        //                 'commission' => $commission,
        //                 'created_at' => now()->format('Y-m-d H:i:s')
        //             ];
        //             $fundraising->donations = json_encode($donations);
                    
        //             $fundraising->save();
        //         }
        //     }
        // } else if ($data['status'] == 'EXPIRED' || $data['status'] == 'FAILED') {
        //     $donation->status = 'gagal';
        // }
        
        // $donation->save();
        
        // return response()->json(['success' => true]);
    }

    public function status(Request $request, $id)
    {
        $reference = $request->tripay_reference;
        $donation = Donation::with('campaign')->where('id',$id)->first();
        $campaign = $donation->campaign;

        // Pastikan URL berakhir dengan slash
        $apiUrl = rtrim($this->apiUrl, '/') . '/';
        $endpoint = 'transaction/detail';
        
        $payload = [
            'reference' => $reference
        ];
        
        $signature = hash_hmac('sha256', $this->merchantCode . $reference, $this->privateKey);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey
        ])->get($apiUrl . $endpoint, [
            'reference' => $reference,
            'signature' => $signature
        ]);

        $responseData = $response->json();

        if (isset($responseData['success']) && $responseData['success'] === true) {

                // Ambil data transaksi dari respons
                $transaction = $responseData['data'];

                $status = 'PENDING';
                
                
                if ($transaction['status'] === 'PAID') {
                    $status = 'PAID';

                    $campaign = $donation->campaign;
                    $campaign->jumlah_donasi += $donation->amount;
                    $campaign->current_donation += $donation->amount;
                    $campaign->total_donatur += 1;
                    $campaign->save();

                        
                    // Cek apakah ada referral code di session
                    $referralCode = session('referral_code');
                    $fundraisingId = null;

                    
                    if ($referralCode) {
                        $fundraising = Fundraising::where('code_link', $referralCode)->first();
                        
                        if ($fundraising) {

                            $commissionSetting = Commission::first(); // atau ->find($id) kalau kamu pakai banyak record
                            $commissionPercent = $commissionSetting->amount ?? 0;

                            // Hitung komisi sesuai persentase dari database
                            $commission = ($donation->amount * $commissionPercent) / 100;
                            
                            
                            // Update data fundraising
                            $fundraising->total_donatur += 1;
                            $fundraising->jumlah_donasi += $donation->amount;
                            $fundraising->commission += $commission;
                            
                            // Update array donations
                            $donations = json_decode($fundraising->donations, true) ?: [];
                            $donations[] = [
                                'donation_id' => $donation->id,
                                'amount' => $donation->amount,
                                'commission' => $commission,
                                'user_name' => $user->name ?? null,
                                'user_email' => $user->email ?? null,
                                'created_at' => now()->format('Y-m-d H:i:s')
                            ];
                            $fundraising->donations = json_encode($donations);
                            
                            $fundraising->save();
                        }
                    }
                    
                    if ($donation) {
                        $donation->status = 'sukses';
                        $donation->updated_at = now();
                        $donation->save();
                    }
                } else if (in_array($transaction['status'], ['EXPIRED', 'FAILED', 'REFUND'])) {
                    $status = 'EXPIRED';
                
                    if ($donation) {
                        $donation->status = 'gagal';
                        $donation->save();
                    }
                }
                
                return view('donatur.donasi.status', compact('donation', 'campaign'));
          
        }

        return view('donatur.donasi.status', compact('donation', 'campaign'));
    }

    public function checkStatus($reference)
{
    try {
        // Pastikan URL berakhir dengan slash
        $apiUrl = rtrim($this->apiUrl, '/') . '/';
        $endpoint = 'transaction/detail';
        
        $payload = [
            'reference' => $reference
        ];
        
        $signature = hash_hmac('sha256', $this->merchantCode . $reference, $this->privateKey);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey
        ])->get($apiUrl . $endpoint, [
            'reference' => $reference,
            'signature' => $signature
        ]);

        $responseData = $response->json();

        if (isset($responseData['success']) && $responseData['success'] === true) {

            
            if ($responseData['success'] === true) {
                // Ambil data transaksi dari respons
                $transaction = $responseData['data'];

                $status = 'PENDING';
                
                
                if ($transaction['status'] === 'PAID') {
                    $status = 'PAID';
                    
                    $donation = Donation::where('snap_token', $reference)->first();
                    if ($donation) {
                        $donation->status = 'sukses';
                        $donation->updated_at = now();
                        $donation->save();
                    }
                } else if (in_array($transaction['status'], ['EXPIRED', 'FAILED', 'REFUND'])) {
                    $status = 'EXPIRED';
                    
                    $donation = Donation::where('snap_token', $reference)->first();
                    if ($donation) {
                        $donation->status = 'gagal';
                        $donation->save();
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => $status,
                        'reference' => $reference,
                        'payment_method' => $transaction['payment_method'],
                        'amount' => $transaction['amount'],
                        'paid_at' => $transaction['paid_at'] ?? null,
                        'note' => $transaction['note'] ?? null
                    ]
                ]);
                
            }
            
            return response()->json([
                'success' => false,
                'message' => $responseData['message'] ?? 'Transaksi tidak ditemukan'
            ]);
        }
        
        // Jika API mengembalikan error
        return response()->json([
            'success' => false,
            'message' => 'Gagal mendapatkan status pembayaran: ' . $response->status(),
            'details' => $response->json()
        ]);
    } catch (\Exception $e) {
        Log::error('Error checking transaction status: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem'
        ]);
    }
}



    public function index(Request $request)
    {
        Carbon::setLocale('id');
        if ($request->ajax()) {
            $query = Donation::with('campaign')->get();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('campaign_title', function ($row) {
                    return $row->campaign ? $row->campaign->title : '-';
                })    
                ->addColumn('created_at', function($row) {
                    return $row->created_at 
                    ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
                    : '-';
                })
                ->addColumn('action', function($row) {
                    $actionBtn = '
                        <div class="btn-group" role="group">
                        <a href="/galang-dana/'.$row->name.'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
                            <a href="'.route('admin.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>
                            <button onclick="deleteAdmin('.$row->id.')" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    ';
                    return $actionBtn;
                })
                ->rawColumns(['campaign_title','created_at','action'])
                 ->make(true);
        }
        
        return view('super_admin.donasi_kampanye.index');
    }

    public function ceklis(Request $request)
    {
        Carbon::setLocale('id');
        if ($request->ajax()) {
            $query = Donation::get();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('method', function ($row) {
                    return $row->payment_type . '('. $row->payment_method . ')';
                })    
                ->addColumn('created_at', function($row) {
                    return $row->created_at 
                    ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
                    : '-';
                })
                ->addColumn('status', function($row) {
                    $statusColor = [
                        'pending' => 'warning',
                        'sukses' => 'success', 
                        'gagal' => 'danger'
                    ];
                    return '<span class="badge bg-'.$statusColor[$row->status].' text-white">'.$row->status.'</span>';
                })
                ->addColumn('action', function($row) {
                    $whatsappUrl = "https://wa.me/". $row->phone;
                    
                    $actionBtn = '<div class="btn-group" role="group">';
                
                    // Jika status masih pending, tampilkan tombol ceklis & silang lebih dulu
                    if ($row->status == 'pending') {
                        $actionBtn .= '
                            <button onclick="updateStatus('.$row->id.', \'sukses\')" class="btn btn-primary btn-sm">
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="updateStatus('.$row->id.', \'gagal\')" class="btn btn-danger btn-sm">
                                <i class="fas fa-times"></i>
                            </button>';
                    }
                
                    // Tambahkan tombol WhatsApp & Hapus setelahnya
                    $actionBtn .= '
                        <a href="'.$whatsappUrl.'" target="_blank" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <button onclick="deleteDonasi('.$row->id.')" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>'; // Tutup div.btn-group
                
                    return $actionBtn;
                })            
                ->rawColumns(['status','method','created_at','action'])
                 ->make(true);
        }
        
        return view('super_admin.ceklis_donasi.index');
    }

    public function updateStatus(Request $request)
    {
        $donation = Donation::find($request->id);
        
        if (!$donation) {
            return response()->json(['success' => false, 'message' => 'Donasi tidak ditemukan']);
        }

        $donation->status = $request->status;
        $donation->save();

        return response()->json(['success' => true, 'message' => 'Status donasi berhasil diperbarui']);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, $title)
    {
        Carbon::setLocale('id');
    
        if ($request->ajax()) {
            $query = Donation::with('campaign')
                ->whereHas('campaign', function ($q) use ($title) {
                    $q->where('title', $title);
                })
                ->get();
    
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('campaign_title', function ($row) {
                    return $row->campaign ? $row->campaign->title : '-';
                })    
                ->addColumn('created_at', function ($row) {
                    return $row->created_at 
                        ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
                        : '-';
                })
                ->rawColumns(['campaign_title', 'created_at'])
                ->make(true);
        }
    
        return view('super_admin.donasi_kampanye.show', compact('title'));
    }    


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Donation $donation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Donation $donation)
    {
        //
    }

    public function destroy($id)
{
    DB::beginTransaction();
    try {
        // Cari data donasi berdasarkan ID
        $donation = Donation::findOrFail($id);

        // Menghapus data donasi
        $donation->delete();

        DB::commit();
        return response()->json(['status' => 'success', 'message' => 'Donasi berhasil dihapus']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => 'error', 'message' => 'Gagal menghapus donasi: ' . $e->getMessage()], 500);
    }
}



}


