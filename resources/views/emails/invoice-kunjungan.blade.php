<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kunjungan - {{ $transaksi->kontak->nama ?? 'Kunjungan' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
            color: #1f2937;
        }

        .invoice-container {
            max-width: 500px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .invoice-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            padding: 24px;
            text-align: center;
            color: white;
        }

        .invoice-header h1 {
            font-size: 20px;
            margin: 0 0 8px 0;
        }

        .invoice-number {
            font-size: 14px;
            opacity: 0.9;
        }

        .notification-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
        }

        .badge-created { background: rgba(255,255,255,0.2); color: white; }
        .badge-needs-approval { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #dcfce7; color: #166534; }

        .invoice-body { padding: 24px; }

        .info-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .info-card-title {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .info-row:last-child { border-bottom: none; }
        .info-row .label { color: #6b7280; }
        .info-row .value { font-weight: 600; color: #1f2937; }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-approved { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-canceled { background: #fee2e2; color: #991b1b; }

        .tujuan-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #ede9fe;
            color: #7c3aed;
        }

        .items-section { margin-bottom: 16px; }

        .items-title {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .item-card {
            background: #f9fafb;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 10px;
        }

        .item-name {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .item-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .memo-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .memo-title {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .memo-content {
            font-size: 13px;
            color: #1f2937;
            line-height: 1.5;
        }

        .action-note {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 13px;
            color: #92400e;
        }

        .success-note {
            background: #dcfce7;
            border: 1px solid #10b981;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 13px;
            color: #166534;
        }

        .info-note {
            background: #dbeafe;
            border: 1px solid #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 13px;
            color: #1e40af;
        }

        .invoice-footer {
            text-align: center;
            padding: 20px;
            background: #1f2937;
            color: #9ca3af;
            font-size: 12px;
        }

        .invoice-footer strong {
            color: white;
            display: block;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    @php
        $dateCode = $transaksi->created_at->format('Ymd');
        $noUrut = str_pad($transaksi->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "VST-{$transaksi->user_id}-{$dateCode}-{$noUrut}";

        $statusClass = 'pending';
        if ($transaksi->status == 'Approved') $statusClass = 'approved';
        elseif ($transaksi->status == 'Canceled') $statusClass = 'canceled';

        $notificationTitles = [
            'created' => 'Kunjungan Baru Dicatat',
            'needs_approval' => 'Menunggu Persetujuan',
            'approved' => 'Kunjungan Disetujui'
        ];
    @endphp

    <div class="invoice-container">
        <div class="invoice-header">
            <h1>Laporan Kunjungan</h1>
            <div class="invoice-number">{{ $nomorInvoice }}</div>
            <span class="notification-badge badge-{{ $notificationType }}">
                {{ $notificationTitles[$notificationType] ?? 'Notifikasi' }}
            </span>
        </div>

        <div class="invoice-body">
            <div class="info-card">
                <div class="info-card-title">📋 Informasi Kunjungan</div>
                <div class="info-row">
                    <span class="label">Tanggal</span>
                    <span class="value">{{ $transaksi->tgl_kunjungan->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Waktu</span>
                    <span class="value">{{ $transaksi->created_at->format('H:i') }} WIB</span>
                </div>
                <div class="info-row">
                    <span class="label">Tujuan</span>
                    <span class="value">
                        <span class="tujuan-badge">{{ $transaksi->tujuan }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $statusClass }}">{{ $transaksi->status }}</span>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-title">👤 Detail Kunjungan</div>
                <div class="info-row">
                    <span class="label">Kontak</span>
                    <span class="value">{{ $transaksi->kontak->nama ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Sales</span>
                    <span class="value">{{ $transaksi->sales_nama ?? $transaksi->user->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Gudang</span>
                    <span class="value">{{ $transaksi->gudang->nama_gudang ?? '-' }}</span>
                </div>
                @if($notificationType == 'approved' && $transaksi->approver)
                <div class="info-row">
                    <span class="label">Disetujui oleh</span>
                    <span class="value">{{ $transaksi->approver->name }}</span>
                </div>
                @endif
            </div>

            @if($transaksi->items && $transaksi->items->count() > 0)
            <div class="items-section">
                <div class="items-title">📦 Produk Ditawarkan</div>
                @foreach($transaksi->items as $item)
                <div class="item-card">
                    <div class="item-name">{{ $item->produk->nama_produk ?? 'Produk' }}</div>
                    <div class="item-meta">
                        Qty: {{ $item->jumlah }} {{ $item->produk->satuan ?? 'Pcs' }}
                        @if($item->keterangan)
                        • {{ $item->keterangan }}
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            @if($transaksi->memo)
            <div class="memo-card">
                <div class="memo-title">📝 Catatan</div>
                <div class="memo-content">{{ $transaksi->memo }}</div>
            </div>
            @endif

            @if($notificationType == 'needs_approval')
            <div class="action-note">
                <strong>⚠️ Tindakan Diperlukan:</strong><br>
                Silakan login ke sistem untuk menyetujui atau menolak laporan kunjungan ini.
            </div>
            @elseif($notificationType == 'approved')
            <div class="success-note">
                <strong>✅ Kunjungan Disetujui</strong><br>
                Laporan kunjungan telah dicatat dalam sistem. Dokumen terlampir dalam email ini.
            </div>
            @else
            <div class="info-note">
                <strong>📝 Info:</strong><br>
                Kunjungan ini menunggu persetujuan dari admin. Anda akan menerima notifikasi setelah disetujui.
            </div>
            @endif
        </div>

        <div class="invoice-footer">
            <strong>HIBISCUS EFSYA</strong>
            marketing@hibiscusefsya.com<br>
            © {{ date('Y') }} Hibiscus Efsya. All rights reserved.
        </div>
    </div>
</body>

</html>
