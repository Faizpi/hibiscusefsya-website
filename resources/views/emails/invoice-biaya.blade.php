<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $transaksi->jenis_biaya == 'masuk' ? 'Bukti Pemasukan' : 'Bukti Pengeluaran' }}</title>
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
            padding: 24px;
            text-align: center;
            color: white;
        }

        .invoice-header.masuk {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .invoice-header.keluar {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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

        .status-approved {
            background: #dcfce7;
            color: #166534;
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

        .total-row.grand {
            color: white;
            padding: 14px 16px;
            border-radius: 10px;
            margin: 12px -16px -16px -16px;
            font-size: 16px;
            font-weight: 700;
        }

        .total-row.grand.masuk {
            background: #10b981;
        }

        .total-row.grand.keluar {
            background: #ef4444;
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
        /** @var \App\Biaya $transaksi */
        /** @var string $notificationType */

        $dateCode = $transaksi->created_at->format('Ymd');
        $noUrut = str_pad($transaksi->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "EXP-{$transaksi->user_id}-{$dateCode}-{$noUrut}";

        $subtotal = $transaksi->items->sum('nominal');
        $pajakNominal = $subtotal * (($transaksi->tax_percentage ?? 0) / 100);

        $statusClass = 'pending';
        if ($transaksi->status == 'Approved')
            $statusClass = 'approved';
        elseif ($transaksi->status == 'Canceled')
            $statusClass = 'canceled';

        $jenisBiaya = $transaksi->jenis_biaya ?? 'keluar';
        $judulInvoice = $jenisBiaya == 'masuk' ? 'Bukti Pemasukan' : 'Bukti Pengeluaran';

        $notificationTitles = [
            'created' => 'Biaya Baru Dicatat',
            'needs_approval' => 'Menunggu Persetujuan',
            'approved' => 'Biaya Disetujui'
        ];
    @endphp

    <div class="invoice-container">
        <div class="invoice-header {{ $jenisBiaya }}">
            <h1>{{ $judulInvoice }}</h1>
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
                    <span class="label">Jenis</span>
                    <span class="value">{{ $jenisBiaya == 'masuk' ? 'Biaya Masuk' : 'Biaya Keluar' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $statusClass }}">{{ $transaksi->status }}</span>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-title">👤 Detail Biaya</div>
                <div class="info-row">
                    <span class="label">Bayar Dari</span>
                    <span class="value">{{ $transaksi->bayar_dari ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Penerima</span>
                    <span class="value">{{ $transaksi->penerima ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Dibuat oleh</span>
                    <span class="value">{{ $transaksi->user->name ?? '-' }}</span>
                </div>
                @if($notificationType == 'approved' && $transaksi->approver)
                    <div class="info-row">
                        <span class="label">Disetujui oleh</span>
                        <span class="value">{{ $transaksi->approver->name }}</span>
                    </div>
                @endif
            </div>

            <div class="items-section">
                <div class="items-title">📝 Rincian Biaya</div>
                @foreach($transaksi->items as $item)
                    <div class="item-card">
                        <div class="item-name">{{ $item->nama_biaya ?? 'Item Biaya' }}</div>
                        <div class="item-total">
                            <span>Nominal</span>
                            <span>Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="totals-card">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if(($transaksi->tax_percentage ?? 0) > 0)
                    <div class="total-row">
                        <span>Pajak ({{ $transaksi->tax_percentage }}%)</span>
                        <span>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="total-row grand {{ $jenisBiaya }}">
                    <span>Grand Total</span>
                    <span>Rp {{ number_format($transaksi->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($notificationType == 'needs_approval')
                <div class="action-note">
                    <strong>⚠️ Tindakan Diperlukan:</strong><br>
                    Silakan login ke sistem untuk menyetujui atau menolak biaya ini.
                </div>
            @elseif($notificationType == 'approved')
                <div class="success-note">
                    <strong>✅ Biaya Disetujui</strong><br>
                    Biaya telah dicatat dalam sistem. Invoice terlampir dalam email ini.
                </div>
            @else
                <div class="info-note">
                    <strong>📝 Info:</strong><br>
                    Biaya ini menunggu persetujuan dari admin. Anda akan menerima notifikasi setelah disetujui.
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