<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pembelian {{ $pembelian->staf_penyetuju }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #11998e;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #11998e;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
        }

        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .info-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .info-box h3 {
            background: #11998e;
            color: #fff;
            padding: 8px 12px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .info-row {
            padding: 5px 12px;
        }

        .info-row .label {
            display: inline-block;
            width: 100px;
            color: #666;
        }

        .info-row .value {
            font-weight: 500;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.items th {
            background: #11998e;
            color: #fff;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
        }

        table.items td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }

        table.items .text-right {
            text-align: right;
        }

        table.items .text-center {
            text-align: center;
        }

        .totals {
            width: 300px;
            margin-left: auto;
        }

        .totals .row {
            display: table;
            width: 100%;
            padding: 8px 0;
        }

        .totals .row .label {
            display: table-cell;
        }

        .totals .row .value {
            display: table-cell;
            text-align: right;
        }

        .totals .grand {
            background: #11998e;
            color: #fff;
            padding: 12px;
            font-size: 14px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
        }

        .status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-lunas {
            background: #d4edda;
            color: #155724;
        }

        .status-approved {
            background: #cce5ff;
            color: #004085;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-canceled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    @php
        $dateCode = $pembelian->created_at->format('Ymd');
        $noUrut = str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "PR-{$pembelian->user_id}-{$dateCode}-{$noUrut}";

        $subtotal = $pembelian->items->sum('jumlah_baris');
        $kenaPajak = max(0, $subtotal - ($pembelian->diskon_akhir ?? 0));
        $pajakNominal = $kenaPajak * (($pembelian->tax_percentage ?? 0) / 100);

        $statusClass = 'pending';
        $statusText = $pembelian->status;
        if ($pembelian->status == 'Lunas') {
            $statusClass = 'lunas';
        } elseif ($pembelian->status == 'Approved') {
            $statusClass = 'approved';
        } elseif ($pembelian->status == 'Canceled') {
            $statusClass = 'canceled';
        }
    @endphp

    <div class="header">
        <h1>HIBISCUS EFSYA</h1>
        <p>PERMINTAAN PEMBELIAN</p>
        <div class="invoice-number">{{ $nomorInvoice }}</div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h3>INFORMASI DOKUMEN</h3>
            <div class="info-row"><span class="label">Tanggal</span><span
                    class="value">{{ $pembelian->tgl_transaksi->format('d F Y') }}</span></div>
            <div class="info-row"><span class="label">Waktu</span><span
                    class="value">{{ $pembelian->created_at->format('H:i') }} WIB</span></div>
            <div class="info-row"><span class="label">Jatuh Tempo</span><span
                    class="value">{{ $pembelian->tgl_jatuh_tempo ? $pembelian->tgl_jatuh_tempo->format('d F Y') : '-' }}</span>
            </div>
            <div class="info-row"><span class="label">Pembayaran</span><span
                    class="value">{{ $pembelian->syarat_pembayaran ?? '-' }}</span></div>
            <div class="info-row"><span class="label">Status</span><span class="value"><span
                        class="status status-{{ $statusClass }}">{{ $statusText }}</span></span></div>
        </div>
        <div class="info-box">
            <h3>INFORMASI VENDOR</h3>
            <div class="info-row"><span class="label">Vendor</span><span
                    class="value">{{ $pembelian->staf_penyetuju ?? '-' }}</span></div>
            <div class="info-row"><span class="label">Pembuat</span><span
                    class="value">{{ $pembelian->user->name }}</span></div>
            <div class="info-row"><span class="label">Disetujui</span><span
                    class="value">{{ $pembelian->status != 'Pending' && $pembelian->approver ? $pembelian->approver->name : '-' }}</span>
            </div>
            <div class="info-row"><span class="label">Gudang</span><span
                    class="value">{{ $pembelian->gudang->nama_gudang ?? '-' }}</span></div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-center">Disc</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembelian->items as $item)
                <tr>
                    <td>{{ $item->produk->nama_produk }} @if($item->produk->item_code)({{ $item->produk->item_code }})@endif
                    </td>
                    <td class="text-center">{{ $item->kuantitas }} {{ $item->unit }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->diskon }}%</td>
                    <td class="text-right">Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="row"><span class="label">Subtotal</span><span class="value">Rp
                {{ number_format($subtotal, 0, ',', '.') }}</span></div>
        @if(($pembelian->diskon_akhir ?? 0) > 0)
            <div class="row"><span class="label">Diskon</span><span class="value">- Rp
                    {{ number_format($pembelian->diskon_akhir, 0, ',', '.') }}</span></div>
        @endif
        @if(($pembelian->tax_percentage ?? 0) > 0)
            <div class="row"><span class="label">Pajak ({{ $pembelian->tax_percentage }}%)</span><span class="value">Rp
                    {{ number_format($pajakNominal, 0, ',', '.') }}</span></div>
        @endif
        <div class="row grand"><span class="label">GRAND TOTAL</span><span class="value">Rp
                {{ number_format($pembelian->grand_total, 0, ',', '.') }}</span></div>
    </div>

    <div class="footer">
        <p><strong>HIBISCUS EFSYA</strong></p>
        <p>marketing@hibiscusefsya.com</p>
    </div>
</body>

</html>