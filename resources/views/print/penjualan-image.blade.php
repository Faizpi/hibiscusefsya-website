<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk Penjualan</title>

<style>
    * {
        box-sizing: border-box;
        font-family: "Courier New", monospace;
        color: #000;
    }

    body {
        margin: 0;
        padding: 12px;
        width: 384px;          /* 58mm thermal printer */
        background: #fff;
        font-size: 14px;
    }

    .header {
        text-align: center;
        margin-bottom: 10px;
    }

    .logo {
        max-width: 280px;
        margin-bottom: 5px;
    }

    .title {
        font-weight: bold;
        font-size: 16px;
    }

    .divider {
        border-top: 1px dashed #000;
        margin: 8px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    td {
        padding: 2px 0;
        vertical-align: top;
    }

    .label { width: 35%; }
    .colon { width: 5%; text-align: center; }
    .value { width: 60%; }

    .item-name {
        font-weight: bold;
        margin-top: 6px;
    }

    .lbl { width: 40%; }
    .val { width: 60%; text-align: right; }

    .grand-total {
        font-weight: bold;
        font-size: 16px;
        border-top: 1px dashed #000;
        padding-top: 6px;
    }

    .footer {
        text-align: center;
        margin-top: 12px;
        font-size: 13px;
    }
</style>
</head>

<body>

@php
    $dateCode = $penjualan->created_at->format('Ymd');
    $noUrut = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
    $nomorInvoice = "INV-{$penjualan->user_id}-{$dateCode}-{$noUrut}";
@endphp

<div class="header">
    @if(file_exists(public_path('assets/img/logoHE1.png')))
    <img src="{{ public_path('assets/img/logoHE1.png') }}" class="logo">
    @endif
    <div class="title">INVOICE PENJUALAN</div>
</div>

<table>
<tr><td class="label">Nomor</td><td class="colon">:</td><td class="value">{{ $nomorInvoice }}</td></tr>
<tr><td class="label">Tanggal</td><td class="colon">:</td><td class="value">{{ $penjualan->tgl_transaksi->format('d/m/Y') }} {{ $penjualan->created_at->format('H:i') }}</td></tr>
<tr><td class="label">Jatuh Tempo</td><td class="colon">:</td><td class="value">{{ $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td></tr>
<tr><td class="label">Pembayaran</td><td class="colon">:</td><td class="value">{{ $penjualan->metode_pembayaran ?? 'Net 7' }}</td></tr>
<tr><td class="label">Pelanggan</td><td class="colon">:</td><td class="value">{{ $penjualan->pelanggan ?? '-' }}</td></tr>
<tr><td class="label">Sales</td><td class="colon">:</td><td class="value">{{ $penjualan->user->name ?? '-' }}</td></tr>
<tr><td class="label">Gudang</td><td class="colon">:</td><td class="value">{{ $penjualan->gudang->nama_gudang ?? '-' }}</td></tr>
<tr><td class="label">Status</td><td class="colon">:</td><td class="value">{{ $penjualan->status }}</td></tr>
</table>

<div class="divider"></div>

@foreach($penjualan->items as $item)
<div class="item-name">
    {{ $item->produk->nama_produk }} ({{ $item->produk->item_code ?? '-' }})
</div>

<table>
<tr><td class="lbl">Qty</td><td class="val">{{ $item->kuantitas }} Pcs</td></tr>
<tr><td class="lbl">Harga</td><td class="val">Rp {{ number_format($item->harga_satuan,0,',','.') }}</td></tr>
@if($item->diskon_per_item > 0)
<tr><td class="lbl">Diskon</td><td class="val">- Rp {{ number_format($item->diskon_per_item,0,',','.') }}</td></tr>
@endif
<tr><td class="lbl"><b>Jumlah</b></td><td class="val"><b>Rp {{ number_format($item->jumlah_baris,0,',','.') }}</b></td></tr>
</table>
@endforeach

<div class="divider"></div>

<table>
<tr><td class="lbl">Subtotal</td><td class="val">Rp {{ number_format($penjualan->items->sum('jumlah_baris'),0,',','.') }}</td></tr>
@if($penjualan->diskon_akhir > 0)
<tr><td class="lbl">Diskon</td><td class="val">- Rp {{ number_format($penjualan->diskon_akhir,0,',','.') }}</td></tr>
@endif
@if($penjualan->tax_percentage > 0)
@php
    $kenaPajak = max(0, $penjualan->items->sum('jumlah_baris') - $penjualan->diskon_akhir);
    $pajakNominal = $kenaPajak * ($penjualan->tax_percentage / 100);
@endphp
<tr><td class="lbl">Pajak ({{ $penjualan->tax_percentage }}%)</td><td class="val">Rp {{ number_format($pajakNominal,0,',','.') }}</td></tr>
@endif
<tr>
    <td class="lbl grand-total">GRAND TOTAL</td>
    <td class="val grand-total">Rp {{ number_format($penjualan->grand_total,0,',','.') }}</td>
</tr>
</table>

<div class="footer">
    marketing@hibiscusefsya.com<br>
    -- Terima Kasih --
</div>

</body>
</html>
