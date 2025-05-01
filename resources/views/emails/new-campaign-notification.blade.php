<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Kampanye Baru</title>
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
            background-color: #007bff;
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
        .admin-details {
            background-color: #f0f7ff;
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
        .buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }
        .button {
            display: inline-block;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .approve {
            background-color: #28a745;
        }
        .reject {
            background-color: #dc3545;
        }
        .view {
            background-color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pengajuan Kampanye Baru</h1>
        </div>
        <div class="content">
            <p>Halo Admin,</p>
            
            <p>Ada pengajuan kampanye baru yang memerlukan persetujuan Anda.</p>
            
            <div class="campaign-details">
                <h3>Detail Kampanye:</h3>
                <p><strong>Judul:</strong> {{ $campaign->title }}</p>
                <p><strong>Kategori:</strong> {{ $campaign->category->name ?? 'Tidak ada kategori' }}</p>
                <p><strong>Target Donasi:</strong> Rp {{ number_format($campaign->jumlah_target_donasi, 0, ',', '.') }}</p>
                <p><strong>Tanggal Pengajuan:</strong> {{ $campaign->created_at->format('d F Y H:i') }}</p>
                <p><strong>Deadline:</strong> {{ $campaign->deadline ? $campaign->deadline->format('d F Y') : 'Tidak ada batas waktu' }}</p>
            </div>
            
            <div class="admin-details">
                <h3>Detail Pengaju:</h3>
                <p><strong>Nama Yayasan:</strong> {{ $admin->name }}</p>
                <p><strong>Email:</strong> {{ $admin->email }}</p>
                <p><strong>Telepon:</strong> {{ $admin->phone }}</p>
                <p><strong>Nama Pimpinan:</strong> {{ $admin->leader_name }}</p>
            </div>
            
            <p>Silakan tinjau kampanye ini dan tentukan apakah kampanye ini dapat disetujui atau ditolak.</p>
            
            <div class="buttons">
                <a href="{{ url('/kampanye/' . $campaign->slug) }}" class="button view">Lihat Kampanye</a>
                <a href="{{ url('/kampanye') }}" class="button approve">Kelola Kampanye</a>
            </div>
            
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>