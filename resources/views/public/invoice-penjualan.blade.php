<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Invoice Penjualan - {{ $penjualan->pelanggan }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --bg-light: #f9fafb;
            --bg-white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            min-height: 100vh;
            padding: 20px;
        }

        .invoice-container {
            max-width: 480px;
            margin: 0 auto;
            background: var(--bg-white);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .invoice-header {
            background: var(--bg-white);
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .invoice-header img {
            max-width: 140px;
            margin-bottom: 12px;
        }

        .invoice-header h1 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .invoice-number {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .invoice-body {
            padding: 24px;
        }

        .info-card {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .info-card-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-card-title i {
            color: var(--primary);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-row .label {
            color: var(--text-secondary);
        }

        .info-row .value {
            font-weight: 600;
            color: var(--text-primary);
            text-align: right;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
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
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .items-title i {
            color: var(--primary);
        }

        .item-card {
            background: var(--bg-light);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 10px;
        }

        .item-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .item-code {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .item-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        .item-total {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 13px;
            padding-top: 10px;
            border-top: 1px dashed var(--border-color);
            color: var(--text-primary);
        }

        .totals-card {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .total-row.discount {
            color: var(--danger);
        }

        .total-row.grand {
            background: var(--primary);
            color: white;
            padding: 14px 16px;
            border-radius: 10px;
            margin: 12px -16px -16px -16px;
            font-size: 16px;
            font-weight: 700;
        }

        .qr-section {
            text-align: center;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .qr-section img {
            width: 100px;
            height: 100px;
            margin-bottom: 8px;
            border-radius: 8px;
        }

        .qr-section p {
            font-size: 11px;
            color: var(--text-muted);
        }

        .btn-download {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 14px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-download:hover {
            background: var(--primary-dark);
            color: white;
            text-decoration: none;
        }

        .invoice-footer {
            text-align: center;
            padding: 20px 24px;
            border-top: 1px solid var(--border-color);
            font-size: 12px;
            color: var(--text-muted);
        }

        .invoice-footer strong {
            color: var(--text-primary);
            display: block;
            margin-bottom: 4px;
        }

        @media (max-width: 400px) {
            body {
                padding: 12px;
            }

            .invoice-body {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    @php
        $dateCode = $penjualan->created_at->format('Ymd');
        $noUrut = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "INV-{$penjualan->user_id}-{$dateCode}-{$noUrut}";
        $invoiceUrl = url('invoice/penjualan/' . $penjualan->uuid);

        $subtotal = $penjualan->items->sum('jumlah_baris');
        $kenaPajak = max(0, $subtotal - $penjualan->diskon_akhir);
        $pajakNominal = $kenaPajak * ($penjualan->tax_percentage / 100);

        // Status logic
        $statusClass = 'pending';
        $statusText = $penjualan->status;
        if ($penjualan->status == 'Lunas') {
            $statusClass = 'lunas';
            $statusText = 'Lunas';
        } elseif ($penjualan->status == 'Approved') {
            $statusClass = 'approved';
            $statusText = 'Belum Lunas';
        } elseif ($penjualan->status == 'Canceled') {
            $statusClass = 'canceled';
        }
    @endphp

    <div class="invoice-container">
        <div class="invoice-header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" alt="Hibiscus Efsya" onerror="this.style.display='none'">
            <h1>Invoice Penjualan</h1>
            <div class="invoice-number">{{ $nomorInvoice }}</div>
        </div>

        <div class="invoice-body">
            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-receipt"></i> Informasi Transaksi</div>
                <div class="info-row">
                    <span class="label">Tanggal</span>
                    <span class="value">{{ $penjualan->tgl_transaksi->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Waktu</span>
                    <span class="value">{{ $penjualan->created_at->format('H:i') }} WIB</span>
                </div>
                <div class="info-row">
                    <span class="label">Jatuh Tempo</span>
                    <span
                        class="value">{{ $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d M Y') : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Pembayaran</span>
                    <span class="value">{{ $penjualan->syarat_pembayaran ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $statusClass }}">{{ $statusText }}</span>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-user"></i> Pelanggan</div>
                <div class="info-row">
                    <span class="label">Nama</span>
                    <span class="value">{{ $penjualan->pelanggan }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Sales</span>
                    <span class="value">{{ $penjualan->user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Gudang</span>
                    <span class="value">{{ $penjualan->gudang->nama_gudang ?? '-' }}</span>
                </div>
            </div>

            <div class="items-section">
                <div class="items-title"><i class="fas fa-box"></i> Daftar Item</div>
                @foreach($penjualan->items as $item)
                    <div class="item-card">
                        <div class="item-name">
                            {{ $item->produk->nama_produk }}
                            @if($item->produk->item_code)
                                <span class="item-code">({{ $item->produk->item_code }})</span>
                            @endif
                        </div>
                        <div class="item-meta">
                            <span>{{ $item->kuantitas }} {{ $item->unit ?? 'Pcs' }} × Rp
                                {{ number_format($item->harga_satuan, 0, ',', '.') }}</span>
                            @if($item->diskon > 0)
                                <span style="color: var(--danger);">-{{ $item->diskon }}%</span>
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
                @if($penjualan->diskon_akhir > 0)
                    <div class="total-row discount">
                        <span>Diskon</span>
                        <span>- Rp {{ number_format($penjualan->diskon_akhir, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($penjualan->tax_percentage > 0)
                    <div class="total-row">
                        <span>Pajak ({{ $penjualan->tax_percentage }}%)</span>
                        <span>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="total-row grand">
                    <span>Grand Total</span>
                    <span>Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="qr-section">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($invoiceUrl) }}"
                    alt="QR Code">
                <p>Scan untuk melihat invoice ini</p>
            </div>

            <a href="{{ route('public.invoice.penjualan.download', $penjualan->uuid) }}" class="btn-download">
                <i class="fas fa-download"></i> Download PDF
            </a>
        </div>

        <div class="invoice-footer">
            <strong>HIBISCUS EFSYA</strong>
            marketing@hibiscusefsya.com
        </div>
    </div>
</body>

</html>