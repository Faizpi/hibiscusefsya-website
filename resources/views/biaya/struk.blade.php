<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk Biaya</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            width: 384px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            background: #fff;
            color: #000;
            padding: 10px;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 8px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .contact {
            font-size: 11px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-row {
            margin: 3px 0;
            font-size: 11px;
        }
        .info-label {
            display: inline-block;
            width: 90px;
        }
        .item {
            margin: 8px 0;
        }
        .item-name {
            font-weight: bold;
            font-size: 12px;
        }
        .item-detail {
            font-size: 11px;
            margin: 2px 0;
        }
        .item-price {
            text-align: right;
            font-size: 11px;
        }
        .total-section {
            margin-top: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin: 3px 0;
        }
        .grand-total {
            font-weight: bold;
            font-size: 13px;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('assets/images/logo.png')))
        <img src="{{ asset('assets/images/logo.png') }}" class="logo" alt="Logo">
        @endif
        <div class="company-name">HIBISCUS EFSYA</div>
        <div class="contact">marketing@hibiscusefsya.com</div>
    </div>

    <div class="separator"></div>

    <div class="center title">BUKTI PENGELUARAN</div>

    <div class="info-row">
        <span class="info-label">Nomor</span>: EXP-{{ $biaya->user_id }}-{{ $biaya->created_at->format('Ymd') }}-{{ str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT) }}
    </div>
    <div class="info-row">
        <span class="info-label">Tanggal</span>: {{ $biaya->tgl_transaksi->format('d/m/Y') }} {{ $biaya->created_at->format('H:i') }}
    </div>
    <div class="info-row">
        <span class="info-label">Penerima</span>: {{ $biaya->penerima ?? '-' }}
    </div>
    <div class="info-row">
        <span class="info-label">Sales</span>: {{ $biaya->user->name ?? '-' }}
    </div>
    <div class="info-row">
        <span class="info-label">Status</span>: {{ $biaya->status }}
    </div>
    @if($biaya->approver_id && $biaya->approver)
    <div class="info-row">
        <span class="info-label">Disetujui</span>: {{ $biaya->approver->name }}
    </div>
    @endif

    <div class="separator"></div>

    @foreach($biaya->items as $item)
    <div class="item">
        <div class="item-name">{{ $item->kategori }}</div>
        @if($item->deskripsi)
        <div class="item-detail">{{ $item->deskripsi }}</div>
        @endif
        <div class="item-price">
            <strong>Jumlah Rp {{ number_format($item->jumlah, 0, ',', '.') }}</strong>
        </div>
    </div>
    @endforeach

    <div class="separator"></div>

    <div class="total-section">
        <div class="total-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format($biaya->items->sum('jumlah'), 0, ',', '.') }}</span>
        </div>
        @if($biaya->tax_percentage > 0)
        @php
            $pajakNominal = $biaya->items->sum('jumlah') * ($biaya->tax_percentage / 100);
        @endphp
        <div class="total-row">
            <span>Pajak ({{ $biaya->tax_percentage }}%)</span>
            <span>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="separator"></div>
        <div class="total-row grand-total">
            <span>GRAND TOTAL</span>
            <span>Rp {{ number_format($biaya->grand_total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="separator"></div>

    <div class="footer">
        <div>marketing@hibiscusefsya.com</div>
        <div class="bold">-- Terima Kasih --</div>
    </div>
</body>
</html>
