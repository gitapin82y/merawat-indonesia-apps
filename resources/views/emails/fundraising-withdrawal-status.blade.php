<!-- resources/views/emails/fundraising-withdrawal-status.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Pencairan Dana Fundraising</title>
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
            color: #ffffff;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }
        .header-approved {
            background-color: #28a745;
        }
        .header-rejected {
            background-color: #dc3545;
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
        }
        .amount-approved {
            color: #28a745;
        }
        .amount-rejected {
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
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
        }
        .status-approved {
            background-color: #28a745;
        }
        .status-rejected {
            background-color: #dc3545;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
            color: white;
        }
        .button-approved {
            background-color: #28a745;
        }
        .button-rejected {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $withdrawal->status == 'disetujui' ? 'header-approved' : 'header-rejected' }}">
            <h1>Update Status Pencairan Dana</h1>
        </div>
        <div class="content">
            <p>Halo {{ $withdrawal->user->name }},</p>
            
            <p>Status permintaan pencairan dana fundraising Anda telah diperbarui.</p>
            
            <div class="withdrawal-info">
                <div class="withdrawal-detail">
                    <strong>Jumlah Dana:</strong> <span class="amount {{ $withdrawal->status == 'disetujui' ? 'amount-approved' : 'amount-rejected' }}">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</span>
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
                    <strong>Tanggal Permintaan:</strong> {{ $withdrawal->created_at->format('d F Y H:i') }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Status:</strong> 
                    <span class="status-badge {{ $withdrawal->status == 'disetujui' ? 'status-approved' : 'status-rejected' }}">
                        {{ strtoupper($withdrawal->status) }}
                    </span>
                </div>
                @if($withdrawal->status == 'ditolak' && $withdrawal->rejection_reason)
                <div class="withdrawal-detail">
                    <strong>Alasan Penolakan:</strong> {{ $withdrawal->rejection_reason }}
                </div>
                @endif
            </div>
            
            @if($withdrawal->status == 'disetujui')
            <p>Dana akan segera ditransfer ke rekening yang Anda berikan dalam waktu 1x24 jam kerja. Terima kasih telah berpartisipasi dalam program fundraising kami.</p>
            <div style="text-align: center;">
                <a href="{{ route('profile.fundraising.index') }}" class="button button-approved">Lihat Detail Fundraising</a>
            </div>
            @else
            <p>Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi tim dukungan kami.</p>
            <div style="text-align: center;">
                <a href="{{ route('profile.fundraising.index') }}" class="button button-rejected">Lihat Detail Fundraising</a>
            </div>
            @endif
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>