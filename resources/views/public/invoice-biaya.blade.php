<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bukti Pengeluaran - {{ $biaya->penerima }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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

        /* Header */
        .receipt-header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: #fff;
            padding: 20px 15px;
            text-align: center;
        }

        .receipt-header img {
            max-width: 100px;
            margin-bottom: 10px;
        }

        .receipt-header h1 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .invoice-number {
            font-size: 13px;
            opacity: 0.9;
            font-family: 'Courier New', monospace;
        }

        /* Status Badge */
        .status-section {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px dashed #e0e0e0;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-canceled { background: #f8d7da; color: #721c24; }

        /* Info Section */
        .info-section {
            padding: 15px;
            border-bottom: 1px dashed #e0e0e0;
        }

        .info-title {
            font-size: 11px;
            color: #f5576c;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
        }

        .info-row .label {
            color: #666;
        }

        .info-row .value {
            font-weight: 500;
            color: #333;
            text-align: right;
            max-width: 60%;
            word-break: break-word;
        }

        /* Items Section */
        .items-section {
            padding: 15px;
            border-bottom: 1px dashed #e0e0e0;
        }

        .items-title {
            font-size: 11px;
            color: #f5576c;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .item-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .item-card:last-child {
            margin-bottom: 0;
        }

        .item-kategori {
            font-weight: 600;
            font-size: 13px;
            color: #333;
            margin-bottom: 4px;
        }

        .item-desc {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }

        .item-jumlah {
            font-weight: 600;
            font-size: 14px;
            color: #f5576c;
            text-align: right;
        }

        /* Totals Section */
        .totals-section {
            padding: 15px;
            border-bottom: 1px dashed #e0e0e0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
        }

        .total-row .label {
            color: #666;
        }

        .total-row .value {
            font-weight: 500;
        }

        .grand-total {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #fff;
            padding: 15px;
            margin: 15px -15px -15px -15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .grand-total .label {
            font-size: 14px;
            font-weight: 600;
        }

        .grand-total .value {
            font-size: 18px;
            font-weight: 700;
        }

        /* QR Section */
        .qr-section {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px dashed #e0e0e0;
        }

        .qr-section img {
            width: 120px;
            height: 120px;
            padding: 8px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .qr-text {
            font-size: 11px;
            color: #888;
            margin-top: 8px;
        }

        /* Download Button */
        .download-section {
            padding: 15px;
            text-align: center;
        }

        .btn-download {
            display: inline-block;
            width: 100%;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #fff;
            padding: 14px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(245, 87, 108, 0.4);
        }

        .btn-download:active {
            transform: translateY(0);
        }

        /* Footer */
        .receipt-footer {
            padding: 15px;
            text-align: center;
            background: #f8f9fa;
        }

        .receipt-footer .company {
            font-weight: 600;
            font-size: 13px;
            color: #333;
        }

        .receipt-footer .email {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .receipt-footer .timestamp {
            font-size: 11px;
            color: #999;
            margin-top: 8px;
        }

        /* Responsive */
        @media (max-width: 420px) {
            body {
                padding: 10px;
            }
            
            .receipt-container {
                border-radius: 8px;
            }
        }
    </style>
</head>

<body>
    @php
        $dateCode = $biaya->created_at->format('Ymd');
        $noUrut = str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}";

        $subtotal = $biaya->items->sum('jumlah');
        $pajakNominal = $subtotal * (($biaya->tax_percentage ?? 0) / 100);

        $statusClass = 'pending';
        if ($biaya->status == 'Approved') {
            $statusClass = 'approved';
        } elseif ($biaya->status == 'Canceled') {
            $statusClass = 'canceled';
        }

        $currentUrl = url()->current();
    @endphp

    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" alt="HE" onerror="this.style.display='none'">
            <h1>BUKTI PENGELUARAN</h1>
            <div class="invoice-number">{{ $nomorInvoice }}</div>
        </div>

        <!-- Status -->
        <div class="status-section">
            <span class="status-badge status-{{ $statusClass }}">{{ $biaya->status }}</span>
        </div>

        <!-- Informasi Dokumen -->
        <div class="info-section">
            <div class="info-title">📋 Informasi Dokumen</div>
            <div class="info-row">
                <span class="label">Tanggal</span>
                <span class="value">{{ $biaya->tgl_transaksi->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Waktu</span>
                <span class="value">{{ $biaya->created_at->format('H:i') }} WIB</span>
            </div>
            <div class="info-row">
                <span class="label">Pembayaran</span>
                <span class="value">{{ $biaya->cara_pembayaran ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Bayar Dari</span>
                <span class="value">{{ $biaya->bayar_dari ?? '-' }}</span>
            </div>
        </div>

        <!-- Informasi Penerima -->
        <div class="info-section">
            <div class="info-title">👤 Informasi Penerima</div>
            <div class="info-row">
                <span class="label">Penerima</span>
                <span class="value">{{ $biaya->penerima ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Pembuat</span>
                <span class="value">{{ $biaya->user->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Disetujui</span>
                <span class="value">{{ $biaya->status != 'Pending' && $biaya->approver ? $biaya->approver->name : '-' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tag</span>
                <span class="value">{{ $biaya->tag ?? '-' }}</span>
            </div>
        </div>

        <!-- Items -->
        <div class="items-section">
            <div class="items-title">📝 Detail Pengeluaran</div>
            @foreach($biaya->items as $item)
                <div class="item-card">
                    <div class="item-kategori">{{ $item->kategori }}</div>
                    @if($item->deskripsi)
                        <div class="item-desc">{{ $item->deskripsi }}</div>
                    @endif
                    <div class="item-jumlah">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <span class="label">Subtotal</span>
                <span class="value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            @if(($biaya->tax_percentage ?? 0) > 0)
                <div class="total-row">
                    <span class="label">Pajak ({{ $biaya->tax_percentage }}%)</span>
                    <span class="value">Rp {{ number_format($pajakNominal, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="grand-total">
                <span class="label">GRAND TOTAL</span>
                <span class="value">Rp {{ number_format($biaya->grand_total, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- QR Code -->
        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($currentUrl) }}" alt="QR Code">
            <div class="qr-text">Scan untuk melihat bukti ini</div>
        </div>

        <!-- Download Button -->
        <div class="download-section">
            <a href="{{ route('public.invoice.biaya.download', $biaya->id) }}" class="btn-download">
                📥 Download PDF
            </a>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="company">HIBISCUS EFSYA</div>
            <div class="email">marketing@hibiscusefsya.com</div>
            <div class="timestamp">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>
</body>

</html>