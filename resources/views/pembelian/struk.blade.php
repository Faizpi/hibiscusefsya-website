<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk Pembelian</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            page-break-before: avoid !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
        }
        html, body {
            width: 58mm;
            height: auto !important;
            min-height: 0 !important;
            overflow: hidden !important;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            background: #fff;
            color: #000;
            padding: 10px;
            margin: 0;
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

    <div class="center title">PERMINTAAN PEMBELIAN</div>

    <div class="info-row">
        <span class="info-label">Nomor</span>: PR-{{ $pembelian->user_id }}-{{ $pembelian->created_at->format('Ymd') }}-{{ str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT) }}
    </div>
    <div class="info-row">
        <span class="info-label">Tanggal</span>: {{ $pembelian->tgl_transaksi->format('d/m/Y') }} {{ $pembelian->created_at->format('H:i') }}
    </div>
    <div class="info-row">
        <span class="info-label">Supplier</span>: {{ $pembelian->supplier ?? '-' }}
    </div>
    <div class="info-row">
        <span class="info-label">Sales</span>: {{ $pembelian->user->name ?? '-' }}
    </div>
    <div class="info-row">
        <span class="info-label">Gudang</span>: {{ $pembelian->gudang->nama_gudang ?? '-' }}
    </div>
    <div class="info-row">
        <span class="info-label">Status</span>: {{ $pembelian->status }}
    </div>
    @if($pembelian->approver_id && $pembelian->approver)
    <div class="info-row">
        <span class="info-label">Disetujui</span>: {{ $pembelian->approver->name }}
    </div>
    @endif

    <div class="separator"></div>

    @foreach($pembelian->items as $item)
    <div class="item">
        <div class="item-name">{{ $item->produk->nama_produk }}</div>
        @if($item->produk->item_code)
        <div class="item-detail">({{ $item->produk->item_code }})</div>
        @endif
        <div class="item-detail">
            Qty {{ $item->kuantitas }} Pcs
        </div>
        <div class="item-detail">
            Harga Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
        </div>
        @if($item->diskon_per_item > 0)
        <div class="item-detail">
            Diskon - Rp {{ number_format($item->diskon_per_item, 0, ',', '.') }}
        </div>
        @endif
        <div class="item-price">
            <strong>Jumlah Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</strong>
        </div>
    </div>
    @endforeach

    <div class="separator"></div>

    <div class="total-section">
        <div class="total-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format($pembelian->items->sum('jumlah_baris'), 0, ',', '.') }}</span>
        </div>
        @if($pembelian->diskon_akhir > 0)
        <div class="total-row">
            <span>Diskon</span>
            <span>- Rp {{ number_format($pembelian->diskon_akhir, 0, ',', '.') }}</span>
        </div>
        @endif
        @if($pembelian->tax_percentage > 0)
        @php
            $kenaPajak = max(0, $pembelian->items->sum('jumlah_baris') - $pembelian->diskon_akhir);
            $pajakNominal = $kenaPajak * ($pembelian->tax_percentage / 100);
        @endphp
        <div class="total-row">
            <span>Pajak ({{ $pembelian->tax_percentage }}%)</span>
            <span>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="separator"></div>
        <div class="total-row grand-total">
            <span>GRAND TOTAL</span>
            <span>Rp {{ number_format($pembelian->grand_total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="separator"></div>

    <div class="footer">
        <div>marketing@hibiscusefsya.com</div>
        <div class="bold">-- Dokumen Internal --</div>
    </div>
</body>
</html>
