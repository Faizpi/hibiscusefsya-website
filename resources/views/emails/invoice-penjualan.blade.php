<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Penjualan - {{ $transaksi->pelanggan }}</title>
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
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
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

        .badge-created {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .badge-needs-approval {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-approved {
            background: #dcfce7;
            color: #166534;
        }

        .invoice-body {
            padding: 24px;
        }

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

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            color: #6b7280;
        }

        .info-row .value {
            font-weight: 600;
            color: #1f2937;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-lunas {
            background: #dcfce7;
            color: #166534;
        }

        .status-approved {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-canceled {
            background: #fee2e2;
            color: #991b1b;
        }

        .items-section {
            margin-bottom: 16px;
        }

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
            margin-bottom: 8px;
        }

        .item-code {
            font-size: 11px;
            color: #9ca3af;
        }

        .item-meta {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .item-total {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 13px;
            padding-top: 10px;
            border-top: 1px dashed #e5e7eb;
        }

        .totals-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            color: #6b7280;
        }

        .total-row.discount {
            color: #ef4444;
        }

        .total-row.grand {
            background: #3b82f6;
            color: white;
            padding: 14px 16px;
            border-radius: 10px;
            margin: 12px -16px -16px -16px;
            font-size: 16px;
            font-weight: 700;
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
        /** @var \App\Penjualan $transaksi */
        /** @var string $notificationType */

        $dateCode = $transaksi->created_at->format('Ymd');
        $noUrut = str_pad($transaksi->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "INV-{$transaksi->user_id}-{$dateCode}-{$noUrut}";

        $subtotal = $transaksi->items->sum('jumlah_baris');
        $kenaPajak = max(0, $subtotal - $transaksi->diskon_akhir);
        $pajakNominal = $kenaPajak * ($transaksi->tax_percentage / 100);

        $statusClass = 'pending';
        $statusText = $transaksi->status;
        if ($transaksi->status == 'Lunas') {
            $statusClass = 'lunas';
            $statusText = 'Lunas';
        } elseif ($transaksi->status == 'Approved') {
            $statusClass = 'approved';
            $statusText = 'Belum Lunas';
        } elseif ($transaksi->status == 'Canceled') {
            $statusClass = 'canceled';
        }

        $notificationTitles = [
            'created' => 'Transaksi Baru Dibuat',
            'needs_approval' => 'Menunggu Persetujuan',
            'approved' => 'Transaksi Disetujui'
        ];
    @endphp

    <div class="invoice-container">
        <div class="invoice-header">
            <h1>Invoice Penjualan</h1>
            <div class="invoice-number">{{ $nomorInvoice }}</div>
            <span class="notification-badge badge-{{ $notificationType }}">
                {{ $notificationTitles[$notificationType] ?? 'Notifikasi' }}
            </span>
        </div>

        <div class="invoice-body">
            <div class="info-card">
                <div class="info-card-title">📋 Informasi Transaksi</div>
                <div class="info-row">
                    <span class="label">Tanggal</span>
                    <span class="value">{{ $transaksi->tgl_transaksi->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Waktu</span>
                    <span class="value">{{ $transaksi->created_at->format('H:i') }} WIB</span>
                </div>
                <div class="info-row">
                    <span class="label">Jatuh Tempo</span>
                    <span
                        class="value">{{ $transaksi->tgl_jatuh_tempo ? $transaksi->tgl_jatuh_tempo->format('d M Y') : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Pembayaran</span>
                    <span class="value">{{ $transaksi->syarat_pembayaran ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $statusClass }}">{{ $statusText }}</span>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-title">👤 Pelanggan</div>
                <div class="info-row">
                    <span class="label">Nama</span>
                    <span class="value">{{ $transaksi->pelanggan }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Sales</span>
                    <span class="value">{{ $transaksi->user->name ?? '-' }}</span>
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

            <div class="items-section">
                <div class="items-title">📦 Daftar Item</div>
                @foreach($transaksi->items as $item)
                    <div class="item-card">
                        <div class="item-name">
                            {{ $item->produk->nama_produk ?? 'Produk' }}
                            @if($item->produk && $item->produk->item_code)
                                <span class="item-code">({{ $item->produk->item_code }})</span>
                            @endif
                        </div>
                        <div class="item-meta">
                            {{ $item->kuantitas }} {{ $item->unit ?? 'Pcs' }} × Rp
                            {{ number_format($item->harga_satuan, 0, ',', '.') }}
                            @if($item->diskon > 0)
                                <span style="color: #ef4444;"> -{{ $item->diskon }}%</span>
                            @endif
                        </div>
                        <div class="item-total">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="totals-card">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if($transaksi->diskon_akhir > 0)
                    <div class="total-row discount">
                        <span>Diskon</span>
                        <span>- Rp {{ number_format($transaksi->diskon_akhir, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($transaksi->tax_percentage > 0)
                    <div class="total-row">
                        <span>Pajak ({{ $transaksi->tax_percentage }}%)</span>
                        <span>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="total-row grand">
                    <span>Grand Total</span>
                    <span>Rp {{ number_format($transaksi->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($notificationType == 'needs_approval')
                <div class="action-note">
                    <strong>⚠️ Tindakan Diperlukan:</strong><br>
                    Silakan login ke sistem untuk menyetujui atau menolak transaksi ini.
                </div>
            @elseif($notificationType == 'approved')
                <div class="success-note">
                    <strong>✅ Transaksi Berhasil Disetujui</strong><br>
                    Stok produk telah dikurangi dari gudang. Invoice terlampir dalam email ini.
                </div>
            @else
                <div class="info-note">
                    <strong>📝 Info:</strong><br>
                    Transaksi ini menunggu persetujuan dari admin. Anda akan menerima notifikasi setelah disetujui.
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