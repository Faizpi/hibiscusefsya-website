<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Invoice Penjualan - {{ $penjualan->pelanggan }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 15px;
        }

        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .receipt-header {
            background: #1a202c;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .receipt-header img {
            max-width: 120px;
            margin-bottom: 10px;
        }

        .receipt-header h1 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 1px;
        }

        .receipt-header .invoice-number {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }

        .receipt-body {
            padding: 20px;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            color: #718096;
            flex-shrink: 0;
            width: 40%;
        }

        .info-row .value {
            font-weight: 500;
            text-align: right;
            word-break: break-word;
        }

        .section-title {
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #667eea;
        }

        .divider {
            height: 1px;
            background: repeating-linear-gradient(90deg,
                    #cbd5e0 0px,
                    #cbd5e0 5px,
                    transparent 5px,
                    transparent 10px);
            margin: 15px 0;
        }

        .item-card {
            background: #f7fafc;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .item-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: #2d3748;
        }

        .item-code {
            font-size: 11px;
            color: #718096;
            font-weight: normal;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #4a5568;
            margin-bottom: 4px;
        }

        .item-total {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 13px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }

        .totals-section {
            background: #f7fafc;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }

        .total-row.grand {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            margin: 10px -15px -15px -15px;
            font-size: 16px;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-lunas {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-approved {
            background: #bee3f8;
            color: #2a4365;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-canceled {
            background: #fed7d7;
            color: #742a2a;
        }

        .qr-section {
            text-align: center;
            padding: 20px;
            background: #f7fafc;
            margin-top: 15px;
            border-radius: 8px;
        }

        .qr-section img {
            width: 120px;
            height: 120px;
            margin-bottom: 10px;
        }

        .qr-section p {
            font-size: 11px;
            color: #718096;
        }

        .download-section {
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: #fff;
            text-decoration: none;
        }

        .receipt-footer {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #718096;
        }

        .receipt-footer strong {
            color: #2d3748;
        }

        @media (max-width: 360px) {
            body {
                padding: 10px;
            }

            .receipt-body {
                padding: 15px;
            }

            .info-row {
                font-size: 12px;
            }

            .item-name {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    @php
        $dateCode = $penjualan->created_at->format('Ymd');
        $noUrut = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "INV-{$penjualan->user_id}-{$dateCode}-{$noUrut}";
        $invoiceUrl = url('invoice/penjualan/' . $penjualan->id);

        $subtotal = $penjualan->items->sum('jumlah_baris');
        $kenaPajak = max(0, $subtotal - $penjualan->diskon_akhir);
        $pajakNominal = $kenaPajak * ($penjualan->tax_percentage / 100);

        // Status logic: Cash selalu Lunas (langsung bayar)
        $statusClass = 'pending';
        $statusText = $penjualan->status;
        if ($penjualan->syarat_pembayaran == 'Cash') {
            $statusClass = 'lunas';
            $statusText = 'Lunas';
        } elseif ($penjualan->status == 'Lunas') {
            $statusClass = 'lunas';
            $statusText = 'Lunas';
        } elseif ($penjualan->status == 'Approved') {
            $statusClass = 'approved';
            $statusText = 'Belum Lunas';
        } elseif ($penjualan->status == 'Canceled') {
            $statusClass = 'canceled';
        }
    @endphp

    <div class="receipt-container">
        <div class="receipt-header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" alt="Hibiscus Efsya" onerror="this.style.display='none'">
            <h1>INVOICE PENJUALAN</h1>
            <div class="invoice-number">{{ $nomorInvoice }}</div>
        </div>

        <div class="receipt-body">
            <div class="info-section">
                <div class="section-title"><i class="fas fa-info-circle mr-1"></i> Informasi</div>
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

            <div class="info-section">
                <div class="section-title"><i class="fas fa-user mr-1"></i> Pelanggan</div>
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

            <div class="divider"></div>

            <div class="section-title"><i class="fas fa-box mr-1"></i> Item</div>
            @foreach($penjualan->items as $item)
                <div class="item-card">
                    <div class="item-name">
                        {{ $item->produk->nama_produk }}
                        @if($item->produk->item_code)
                            <span class="item-code">({{ $item->produk->item_code }})</span>
                        @endif
                    </div>
                    <div class="item-details">
                        <span>{{ $item->kuantitas }} {{ $item->unit ?? 'Pcs' }} × Rp
                            {{ number_format($item->harga_satuan, 0, ',', '.') }}</span>
                        @if($item->diskon > 0)
                            <span>Disc: {{ $item->diskon }}%</span>
                        @endif
                    </div>
                    <div class="item-total">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach

            <div class="totals-section">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if($penjualan->diskon_akhir > 0)
                    <div class="total-row" style="color: #e53e3e;">
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
                    <span>GRAND TOTAL</span>
                    <span>Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="qr-section">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($invoiceUrl) }}"
                    alt="QR Code">
                <p>Scan QR untuk melihat invoice</p>
            </div>
        </div>

        <div class="download-section">
            <a href="{{ route('public.invoice.penjualan.download', $penjualan->id) }}" class="btn-download">
                <i class="fas fa-download"></i> Download PDF
            </a>
        </div>

        <div class="receipt-footer">
            <p><strong>HIBISCUS EFSYA</strong></p>
            <p>marketing@hibiscusefsya.com</p>
        </div>
    </div>
</body>

</html>