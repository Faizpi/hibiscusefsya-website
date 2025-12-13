<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembelian</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        html, body {
            width: 58mm;
            height: auto !important;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            color: #000;
            padding: 3mm 2mm;
            box-sizing: border-box;
            background: #fff;
        }
        * {
            page-break-before: avoid !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .header {
            text-align: center;
            margin-bottom: 6px;
        }
        .logo {
            max-width: 45mm;
            height: auto;
            margin-bottom: 4px;
        }
        .title {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .info-table {
            width: 100%;
            font-size: 9pt;
        }
        .info-table td {
            vertical-align: top;
            padding: 1px 0;
        }
        .label {
            width: 35%;
        }
        .colon {
            width: 5%;
            text-align: center;
        }
        .value {
            width: 60%;
        }
        .item-container {
            margin-bottom: 6px;
        }
        .item-name {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 2px;
        }
        .details-table {
            width: 100%;
            font-size: 9pt;
        }
        .details-table td {
            padding: 1px 0;
        }
        .lbl {
            width: 35%;
        }
        .val {
            width: 65%;
            text-align: right;
        }
        .total-table {
            width: 100%;
            font-size: 9pt;
        }
        .total-table td {
            padding: 1px 0;
        }
        .grand-total {
            font-weight: bold;
            font-size: 11pt;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }
        .footer {
            text-align: center;
            font-size: 9pt;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    @php
        $dateCode = $pembelian->created_at->format('Ymd');
        $noUrut = str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "PR-{$pembelian->user_id}-{$dateCode}-{$noUrut}";
    @endphp

    <div class="header">
        @if(file_exists(public_path('assets/images/logo.png')))
        <img src="{{ asset('assets/images/logo.png') }}" class="logo" alt="Logo">
        @endif
        <div class="title">PERMINTAAN PEMBELIAN</div>
    </div>

    <table class="info-table">
        <tr><td class="label">Nomor</td><td class="colon">:</td><td class="value">{{ $nomorInvoice }}</td></tr>
        <tr><td class="label">Tanggal</td><td class="colon">:</td><td class="value">{{ $pembelian->tgl_transaksi->format('d/m/Y') }} | {{ $pembelian->created_at->format('H:i') }}</td></tr>
        <tr><td class="label">Supplier</td><td class="colon">:</td><td class="value">{{ $pembelian->supplier ?? '-' }}</td></tr>
        <tr><td class="label">Sales</td><td class="colon">:</td><td class="value">{{ $pembelian->user->name ?? '-' }}</td></tr>
        <tr><td class="label">Gudang</td><td class="colon">:</td><td class="value">{{ $pembelian->gudang->nama_gudang ?? '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="colon">:</td><td class="value">{{ $pembelian->status }}</td></tr>
        @if($pembelian->approver_id && $pembelian->approver)
        <tr><td class="label">Disetujui</td><td class="colon">:</td><td class="value">{{ $pembelian->approver->name }}</td></tr>
        @endif
    </table>

    <div class="divider"></div>

    @foreach($pembelian->items as $item)
    <div class="item-container">
        <div class="item-name">{{ $item->produk->nama_produk }} @if($item->produk->item_code)({{ $item->produk->item_code }})@endif</div>
        <table class="details-table">
            <tr><td class="lbl">Qty</td><td class="val">{{ $item->kuantitas }} Pcs</td></tr>
            <tr><td class="lbl">Harga</td><td class="val">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td></tr>
            @if($item->diskon_per_item > 0)
            <tr><td class="lbl">Diskon</td><td class="val">- Rp {{ number_format($item->diskon_per_item, 0, ',', '.') }}</td></tr>
            @endif
            <tr><td class="lbl" style="font-weight:bold">Jumlah</td><td class="val" style="font-weight:bold">Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</td></tr>
        </table>
    </div>
    @endforeach

    <div class="divider"></div>

    <table class="total-table">
        <tr><td class="lbl">Subtotal</td><td class="val">Rp {{ number_format($pembelian->items->sum('jumlah_baris'), 0, ',', '.') }}</td></tr>
        @if($pembelian->diskon_akhir > 0)
        <tr><td class="lbl">Diskon</td><td class="val">- Rp {{ number_format($pembelian->diskon_akhir, 0, ',', '.') }}</td></tr>
        @endif
        @if($pembelian->tax_percentage > 0)
        @php
            $kenaPajak = max(0, $pembelian->items->sum('jumlah_baris') - $pembelian->diskon_akhir);
            $pajakNominal = $kenaPajak * ($pembelian->tax_percentage / 100);
        @endphp
        <tr><td class="lbl">Pajak ({{ $pembelian->tax_percentage }}%)</td><td class="val">Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td></tr>
        @endif
        <tr><td class="lbl grand-total">GRAND TOTAL</td><td class="val grand-total">Rp {{ number_format($pembelian->grand_total, 0, ',', '.') }}</td></tr>
    </table>

    <div class="footer">
        <div>marketing@hibiscusefsya.com</div>
        <div>-- Dokumen Internal --</div>
    </div>
</body>
</html>
