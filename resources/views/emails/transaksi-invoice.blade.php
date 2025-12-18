<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 30px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            color: #666;
            font-weight: 500;
        }

        .value {
            font-weight: bold;
            color: #333;
        }

        .total-box {
            background: #667eea;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }

        .total-box .amount {
            font-size: 28px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background: #333;
            color: #aaa;
            border-radius: 0 0 10px 10px;
            font-size: 12px;
        }

        .footer a {
            color: #667eea;
        }

        .note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🌺 HIBISCUS EFSYA</h1>
        <p style="margin: 5px 0 0;">Invoice {{ ucfirst($type) }}</p>
    </div>

    <div class="content">
        <p>Halo <strong>{{ $transaksi->user->name ?? 'Pelanggan' }}</strong>,</p>

        <p>Terima kasih telah melakukan transaksi dengan kami. Berikut adalah detail invoice Anda:</p>

        <div class="info-box">
            <div class="info-row">
                <span class="label">Nomor Invoice</span>
                <span class="value">{{ $transaksi->nomor ?? $transaksi->custom_number ?? $transaksi->id }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tanggal Transaksi</span>
                <span class="value">{{ $transaksi->tgl_transaksi->format('d F Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Jenis Transaksi</span>
                <span class="value">{{ ucfirst($type) }}</span>
            </div>
            @if($type == 'biaya')
                <div class="info-row">
                    <span class="label">Jenis Biaya</span>
                    <span class="value">{{ $transaksi->jenis_biaya == 'masuk' ? 'Biaya Masuk' : 'Biaya Keluar' }}</span>
                </div>
            @endif
            <div class="info-row">
                <span class="label">Status</span>
                <span class="value">{{ $transaksi->status }}</span>
            </div>
        </div>

        <div class="total-box">
            <div style="font-size: 14px; opacity: 0.9;">Total Pembayaran</div>
            <div class="amount">Rp {{ number_format($transaksi->grand_total, 0, ',', '.') }}</div>
        </div>

        <div class="note">
            <strong>📎 Lampiran:</strong> Invoice PDF telah dilampirkan di email ini. Silakan simpan sebagai bukti
            transaksi Anda.
        </div>

        <p style="text-align: center;">
            <a href="{{ url('invoice/' . $type . '/' . $transaksi->id) }}" class="btn">Lihat Invoice Online</a>
        </p>

        <p>Jika Anda memiliki pertanyaan, silakan hubungi kami melalui:</p>
        <ul>
            <li>Email: marketing@hibiscusefsya.com</li>
            <li>WhatsApp: +62 xxx-xxxx-xxxx</li>
        </ul>

        <p>Terima kasih atas kepercayaan Anda! 🙏</p>

        <p>Salam hangat,<br>
            <strong>Tim Hibiscus Efsya</strong>
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Hibiscus Efsya. All rights reserved.</p>
        <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
    </div>
</body>

</html>