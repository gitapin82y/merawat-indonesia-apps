<!-- resources/views/emails/campaign-status.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Kampanye</title>
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
        .content {
            padding: 20px;
        }
        .campaign-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .campaign-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .campaign-detail {
            margin-bottom: 8px;
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
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .campaign-photo {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="background-color: {{ $isApproved ? '#28a745' : '#dc3545' }};">
            <h1>Status Kampanye</h1>
        </div>
        <div class="content">
            <p>Halo {{ $campaign->admin->name }},</p>
            
            @if ($isApproved)
                <p>Kabar baik! Kampanye yang Anda ajukan telah <strong>disetujui</strong> dan sekarang aktif di platform kami.</p>
                
                <div class="campaign-info">
                    @if($campaign->photo)
                        <img src="{{ asset('storage/' . $campaign->photo) }}" alt="{{ $campaign->title }}" class="campaign-photo">
                    @endif
                    <div class="campaign-title">{{ $campaign->title }}</div>
                    <div class="campaign-detail"><strong>Target Donasi:</strong> Rp {{ number_format($campaign->jumlah_target_donasi) }}</div>
                    <div class="campaign-detail"><strong>Deadline:</strong> {{ $campaign->deadline ? $campaign->deadline->format('d F Y') : 'Tidak ada batas waktu' }}</div>
                    <div class="campaign-detail"><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">Disetujui</span></div>
                </div>
                
                <p>Kampanye Anda akan mulai ditampilkan kepada publik dan siap menerima donasi.</p>
                
                <div style="text-align: center;">
                    <a href="{{ route('campaign.detail', $campaign->title) }}" class="button" style="background-color: {{ $isApproved ? '#28a745' : '#6c757d' }};">Lihat Kampanye</a>
                </div>
            @else
                <p>Mohon maaf, kampanye yang Anda ajukan telah <strong>ditolak</strong>.</p>
                
                <div class="campaign-info">
                    @if($campaign->photo)
                        <img src="{{ asset('storage/' . $campaign->photo) }}" alt="{{ $campaign->title }}" class="campaign-photo">
                    @endif
                    <div class="campaign-title">{{ $campaign->title }}</div>
                    <div class="campaign-detail"><strong>Target Donasi:</strong> Rp {{ number_format($campaign->jumlah_target_donasi) }}</div>
                    <div class="campaign-detail"><strong>Status:</strong> <span style="color: #dc3545; font-weight: bold;">Ditolak</span></div>
                </div>
                
                <p>Beberapa alasan umum penolakan kampanye:</p>
                <ul>
                    <li>Informasi kampanye tidak lengkap atau tidak jelas</li>
                    <li>Kampanye tidak sesuai dengan ketentuan platform</li>
                    <li>Dokumen pendukung tidak memadai</li>
                </ul>
                
                <p>Anda dapat memperbaiki kampanye Anda dan mengajukannya kembali. Jika membutuhkan bantuan tambahan, silakan hubungi tim dukungan kami.</p>
            @endif
            
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>