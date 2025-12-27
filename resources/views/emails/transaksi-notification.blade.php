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
            border-radius: 10px 10px 0 0;
            color: white;
        }

        .header-created {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .header-needs-approval {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .header-approved {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
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
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            color: white;
        }

        .total-created {
            background: #667eea;
        }

        .total-needs-approval {
            background: #f5576c;
        }

        .total-approved {
            background: #11998e;
        }

        .total-box .amount {
            font-size: 28px;
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .badge-created {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-needs-approval {
            background: #fff3e0;
            color: #f57c00;
        }

        .badge-approved {
            background: #e8f5e9;
            color: #388e3c;
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

        .action-note {
            background: #ffebee;
            border: 1px solid #f44336;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
            color: #c62828;
        }

        .success-note {
            background: #e8f5e9;
            border: 1px solid #4caf50;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
            color: #2e7d32;
        }
    </style>
</head>

<body>
    @php
        $typeLabels = [
            'penjualan' => 'Penjualan',
            'pembelian' => 'Pembelian',
            'biaya' => 'Biaya',
            'kunjungan' => 'Kunjungan'
        ];

        $notificationTitles = [
            'created' => 'Transaksi Baru Dibuat',
            'needs_approval' => 'Menunggu Persetujuan Anda',
            'approved' => 'Transaksi Telah Disetujui'
        ];

        $notificationDesc = [
            'created' => 'Transaksi baru telah dibuat di sistem.',
            'needs_approval' => 'Transaksi ini membutuhkan persetujuan Anda.',
            'approved' => 'Selamat! Transaksi Anda telah disetujui.'
        ];

        $label = $typeLabels[$type] ?? 'Transaksi';
        $title = $notificationTitles[$notificationType] ?? 'Notifikasi';
        $desc = $notificationDesc[$notificationType] ?? '';
        $nomor = $transaksi->nomor ?? $transaksi->custom_number ?? $transaksi->id;
    @endphp

    <div class="header header-{{ $notificationType }}">
        <h1>{{ $title }}</h1>
        <p>{{ $desc }}</p>
    </div>

    <div class="content">
        <div class="info-box">
            <div class="info-row">
                <span class="label">Jenis Transaksi</span>
                <span class="value">{{ $label }}</span>
            </div>
            <div class="info-row">
                <span class="label">Nomor</span>
                <span class="value">#{{ $nomor }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tanggal</span>
                <span class="value">
                    @if($type == 'kunjungan')
                        {{ \Carbon\Carbon::parse($transaksi->tgl_kunjungan)->format('d M Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d M Y') }}
                    @endif
                </span>
            </div>
            @if($type == 'penjualan' && $transaksi->pelanggan)
                <div class="info-row">
                    <span class="label">Pelanggan</span>
                    <span class="value">{{ $transaksi->pelanggan }}</span>
                </div>
            @elseif($type == 'pembelian' && $transaksi->staf_penyetuju)
                <div class="info-row">
                    <span class="label">Staff Penyetuju</span>
                    <span class="value">{{ $transaksi->staf_penyetuju }}</span>
                </div>
            @elseif($type == 'kunjungan' && $transaksi->kontak)
                <div class="info-row">
                    <span class="label">Kontak</span>
                    <span class="value">{{ $transaksi->kontak->nama ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Tujuan Kunjungan</span>
                    <span class="value">{{ $transaksi->tujuan ?? '-' }}</span>
                </div>
            @endif
            @if($transaksi->gudang)
                <div class="info-row">
                    <span class="label">Gudang</span>
                    <span class="value">{{ $transaksi->gudang->nama_gudang ?? '-' }}</span>
                </div>
            @endif
            <div class="info-row">
                <span class="label">Dibuat oleh</span>
                <span class="value">{{ $transaksi->user->name ?? '-' }}</span>
            </div>
            @if($notificationType == 'approved' && $transaksi->approver)
                <div class="info-row">
                    <span class="label">Disetujui oleh</span>
                    <span class="value">{{ $transaksi->approver->name ?? '-' }}</span>
                </div>
            @endif
            <div class="info-row">
                <span class="label">Status</span>
                <span class="value">
                    <span class="status-badge badge-{{ $notificationType }}">
                        {{ $transaksi->status ?? 'Pending' }}
                    </span>
                </span>
            </div>
        </div>

        @if($type != 'kunjungan')
            <div class="total-box total-{{ $notificationType }}">
                <div style="font-size: 14px; opacity: 0.9;">Total {{ $label }}</div>
                <div class="amount">Rp {{ number_format($transaksi->grand_total ?? 0, 0, ',', '.') }}</div>
            </div>
        @endif

        @if($notificationType == 'needs_approval')
            <div class="action-note">
                <strong>⚠️ Tindakan Diperlukan:</strong><br>
                Silakan login ke sistem untuk menyetujui atau menolak transaksi ini.
            </div>
        @elseif($notificationType == 'approved')
            <div class="success-note">
                <strong>✅ Transaksi Berhasil:</strong><br>
                @if($type == 'penjualan')
                    Stok produk telah dikurangi dari gudang.
                @elseif($type == 'pembelian')
                    Stok produk telah ditambahkan ke gudang.
                @elseif($type == 'kunjungan')
                    Kunjungan telah disetujui dan dicatat dalam sistem.
                @else
                    Biaya telah dicatat dalam sistem.
                @endif
            </div>
        @else
            <div class="note">
                <strong>📝 Info:</strong><br>
                Transaksi ini menunggu persetujuan dari admin. Anda akan menerima notifikasi setelah disetujui.
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Email ini dikirim otomatis dari sistem <strong>Hibiscus Efsya</strong>.</p>
        <p>© {{ date('Y') }} Hibiscus Efsya. All rights reserved.</p>
    </div>
</body>

</html>