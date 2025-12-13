<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk Penjualan</title>
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

    <div class="center title">INVOICE PENJUALAN</div>

    <div class="info-row">
        <span class="info-label">Nomor</span>: INV-{{ $penjualan->user_id }}-{{ $penjualan->created_at->format('Ymd') }}-{{ str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT) }}
    </div>
    <div class="info-row">
        <span class="info-label">Tanggal</span>: {{ $penjualan->tgl_transaksi->format('d/m/Y') }} {{ $penjualan->created_at->format('H:i') }}
    </div>
    <div class="info-row">
        <span class="info-label">Jatuh Tempo</span>: {{ $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d/m/Y') : '-' }}
    </div>
    <div class="info-row">
        <span class="info-label">Pembayaran</span>: {{ $penjualan->metode_pembayaran ?? 'Net 7' }}
    </div>
    <div class="info-row">
        <span class="info-label">Pelanggan</span>: {{ $penjualan->pelanggan ?? '-' }}
    </div>
    <div class="info-row">
        <span class="info-label">Sales</span>: {{ $penjualan->user->name ?? '-' }}
    </div>
    <div class="info-row">
        <span class="info-label">Gudang</span>: {{ $penjualan->gudang->nama_gudang ?? '-' }}
    </div>
    <div class="info-row">
        <span class="info-label">Status</span>: {{ $penjualan->status }}
    </div>
    @if($penjualan->approver_id && $penjualan->approver)
    <div class="info-row">
        <span class="info-label">Disetujui</span>: {{ $penjualan->approver->name }}
    </div>
    @endif

    <div class="separator"></div>

    @foreach($penjualan->items as $item)
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
            <span>Rp {{ number_format($penjualan->items->sum('jumlah_baris'), 0, ',', '.') }}</span>
        </div>
        @if($penjualan->diskon_akhir > 0)
        <div class="total-row">
            <span>Diskon</span>
            <span>- Rp {{ number_format($penjualan->diskon_akhir, 0, ',', '.') }}</span>
        </div>
        @endif
        @if($penjualan->tax_percentage > 0)
        @php
            $kenaPajak = max(0, $penjualan->items->sum('jumlah_baris') - $penjualan->diskon_akhir);
            $pajakNominal = $kenaPajak * ($penjualan->tax_percentage / 100);
        @endphp
        <div class="total-row">
            <span>Pajak ({{ $penjualan->tax_percentage }}%)</span>
            <span>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="separator"></div>
        <div class="total-row grand-total">
            <span>GRAND TOTAL</span>
            <span>Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="separator"></div>

    <div class="footer">
        <div>marketing@hibiscusefsya.com</div>
        <div class="bold">-- Terima Kasih --</div>
    </div>
</body>
</html>
