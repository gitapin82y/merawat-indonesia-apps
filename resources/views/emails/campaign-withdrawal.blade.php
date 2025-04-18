<!-- resources/views/emails/campaign-withdrawal.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Pencairan Dana Kampanye</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #dc3545;
            color: #ffffff;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .withdrawal-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .withdrawal-detail {
            margin-bottom: 10px;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 10px;
        }
        .withdrawal-detail:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
            text-align: center;
        }
        .button {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Permintaan Pencairan Dana Kampanye</h1>
        </div>
        <div class="content">
            <p>Halo Admin,</p>
            
            <p>Ada permintaan pencairan dana kampanye baru yang memerlukan persetujuan Anda.</p>
            
            <div class="withdrawal-info">
                <div class="withdrawal-detail">
                    <strong>Nama Admin:</strong> {{ $withdrawal->admin->name }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Email:</strong> {{ $withdrawal->admin->user->email }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Kampanye:</strong> {{ $withdrawal->campaign->title }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Jumlah Dana:</strong> <span class="amount">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</span>
                </div>
                <div class="withdrawal-detail">
                    <strong>Metode Pembayaran:</strong> {{ strtoupper($withdrawal->payment_method) }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Nama Rekening:</strong> {{ $withdrawal->account_name }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Nomor Rekening:</strong> {{ $withdrawal->account_number }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Dokumen RAB:</strong> 
                    @if($withdrawal->document_rab)
                        <a href="{{ asset('storage/'.$withdrawal->document_rab) }}" target="_blank">Lihat Dokumen</a>
                    @else
                        Tidak ada
                    @endif
                </div>
                <div class="withdrawal-detail">
                    <strong>Tanggal Permintaan:</strong> {{ $withdrawal->created_at->format('d F Y H:i') }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Status:</strong> <span style="color: #ff9800; font-weight: bold;">Menunggu</span>
                </div>
            </div>
            
            <p>Silakan tinjau permintaan ini dan berikan persetujuan atau penolakan melalui panel admin.</p>
            
            <div style="text-align: center;">
                <a href="{{ route('pencairan-kampanye.index') }}" class="button">Kelola Pencairan Dana</a>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>