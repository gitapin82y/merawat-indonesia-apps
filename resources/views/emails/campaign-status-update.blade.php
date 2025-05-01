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
            background-color: {{ $isApproved ? '#28a745' : ($isPending ? '#ffc107' : '#dc3545') }};
            color: #ffffff;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .campaign-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Status Kampanye</h1>
        </div>
        <div class="content">
            <p>Halo {{ $campaign->admin->name }},</p>
            
            @if ($isApproved)
                <p>Selamat! Kampanye Anda dengan judul "<strong>{{ $campaign->title }}</strong>" telah <strong>disetujui</strong>.</p>
                
                <p>Kampanye Anda sekarang aktif dan sudah dapat dilihat oleh donatur.</p>
                
                <div class="campaign-details">
                    <p><strong>Judul:</strong> {{ $campaign->title }}</p>
                    <p><strong>Kategori:</strong> {{ $campaign->category->name ?? 'Tidak ada kategori' }}</p>
                    <p><strong>Status:</strong> Disetujui</p>
                    <p><strong>Tanggal Persetujuan:</strong> {{ now()->format('d F Y H:i') }}</p>
                </div>
                
                <p>Silakan pantau kampanye Anda secara berkala dan perbarui kabar terbaru tentang kampanye untuk menjaga kepercayaan donatur.</p>
                
                <div style="text-align: center;">
                    <a href="{{ url('/kampanye/' . $campaign->slug) }}" class="button" style="background-color: #28a745;">Lihat Kampanye</a>
                </div>
            
            @elseif ($isPending)
                <p>Kampanye Anda dengan judul "<strong>{{ $campaign->title }}</strong>" telah berhasil diajukan dan sedang menunggu persetujuan admin.</p>
                
                <div class="campaign-details">
                    <p><strong>Judul:</strong> {{ $campaign->title }}</p>
                    <p><strong>Kategori:</strong> {{ $campaign->category->name ?? 'Tidak ada kategori' }}</p>
                    <p><strong>Status:</strong> Sedang Divalidasi</p>
                    <p><strong>Tanggal Pengajuan:</strong> {{ now()->format('d F Y H:i') }}</p>
                </div>
                
                <p>Kami akan meninjau kampanye Anda secepatnya. Anda akan menerima notifikasi saat status kampanye Anda berubah.</p>
            
            @else
                <p>Mohon maaf, kampanye Anda dengan judul "<strong>{{ $campaign->title }}</strong>" telah <strong>ditolak</strong>.</p>
                
                <div class="campaign-details">
                    <p><strong>Judul:</strong> {{ $campaign->title }}</p>
                    <p><strong>Kategori:</strong> {{ $campaign->category->name ?? 'Tidak ada kategori' }}</p>
                    <p><strong>Status:</strong> Ditolak</p>
                    <p><strong>Tanggal Keputusan:</strong> {{ now()->format('d F Y H:i') }}</p>
                </div>
                
                <p>Silakan periksa kembali informasi kampanye Anda dan pastikan semua sesuai dengan ketentuan platform kami. Anda dapat mengajukan kampanye baru atau menghubungi tim dukungan kami untuk informasi lebih lanjut.</p>
                
                <div style="text-align: center;">
                    <a href="{{ route('galang-dana') }}" class="button" style="background-color: #6c757d;">Dashboard</a>
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