<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Permintaan Penghapusan Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc3545;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #dc3545;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Permintaan Penghapusan Data Baru</h1>
        </div>
        
        <div class="content">
            <p>Sistem telah menerima permintaan penghapusan data baru dari pengguna. Berikut adalah detail permintaan:</p>
            
            <table>
                <tr>
                    <th>Tanggal Permintaan</th>
                    <td>{{ $requestDate }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $requestData['email'] }}</td>
                </tr>
                <tr>
                    <th>Nama</th>
                    <td>{{ $requestData['name'] }}</td>
                </tr>
                @if(isset($requestData['reason']) && !empty($requestData['reason']))
                <tr>
                    <th>Alasan</th>
                    <td>{{ $requestData['reason'] }}</td>
                </tr>
                @endif
            </table>
            
            <p><strong>Catatan:</strong> Sesuai dengan kebijakan privasi dan peraturan perlindungan data, permintaan ini harus diproses dalam waktu 30 hari.</p>
            
            <p>Langkah selanjutnya:</p>
            <ol>
                <li>Verifikasi identitas pengguna</li>
                <li>Lakukan penghapusan data sesuai prosedur</li>
                <li>Beri konfirmasi ke pengguna saat penghapusan selesai</li>
                <li>Catat permintaan ini dalam log penghapusan data</li>
            </ol>
            
            <div style="text-align: center;">
                <a href="{{ route('admin.data-deletion-requests') }}" class="button">Lihat Semua Permintaan</a>
            </div>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem Merawat Indonesia. Mohon jangan membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Merawat Indonesia. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>