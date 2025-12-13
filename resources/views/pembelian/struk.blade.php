<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian</title>

    <style>
        /* =========================
           PAGE & BASE SETUP (WAJIB)
        ==========================*/
        @page {
            size: 58mm auto;
            margin: 0;
        }

        html,
        body {
            width: 100%;
            max-width: 100vw;
            height: auto !important;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            color: #000;
            padding: 3mm;
            box-sizing: border-box;
            background: #fff;
            text-align: center;
            line-height: 1.3;
        }

        /* CEGAH PAGE BREAK ANDROID */
        * {
            page-break-before: avoid !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* =========================
           HEADER
        ==========================*/
        .header {
            text-align: center;
            margin: 0 auto 5px;
            width: 100%;
        }

        .store-name {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }

        .store-address {
            font-size: 8pt;
            margin: 2px 0;
        }

        .title {
            font-size: 10pt;
            font-weight: bold;
            margin: 5px 0 3px;
        }

        /* =========================
           DIVIDER
        ==========================*/
        .divider {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        /* =========================
           INFO TABLE
        ==========================*/
        .info-table {
            width: 100%;
            font-size: 8pt;
            text-align: left;
            margin: 0 auto;
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

        /* =========================
           ITEM
        ==========================*/
        .item-container {
            margin-bottom: 5px;
        }

        .item-name {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 2px;
        }

        .details-table {
            width: 100%;
            font-size: 8pt;
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

        /* =========================
           TOTAL
        ==========================*/
        .total-table {
            width: 100%;
            font-size: 8pt;
        }

        .total-table td {
            padding: 1px 0;
        }

        .grand-total {
            font-weight: bold;
            font-size: 10pt;
            border-top: 1px dashed #000;
            padding-top: 3px;
        }

        /* =========================
           FOOTER
        ==========================*/
        .footer {
            text-align: center;
            font-size: 8pt;
            margin-top: 6px;
        }

        /* =========================
           PRINT ONLY
        ==========================*/
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    @php
        $dateCode = $pembelian->created_at->format('Ymd');
        $noUrut = str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorPembelian = "REQ-{$pembelian->user_id}-{$dateCode}-{$noUrut}";
    @endphp

    <!-- HEADER -->
    <div class="header">
        <div class="store-name">HIBISCUS EFSYA</div>
        <div class="store-address">marketing@hibiscusefsya.com</div>
        <div class="title">PERMINTAAN PEMBELIAN</div>
    </div>

    <!-- INFO -->
    <table class="info-table">
        <tr><td class="label">Nomor</td><td class="colon">:</td><td class="value">{{ $nomorPembelian }}</td></tr>
        <tr><td class="label">Tanggal</td><td class="colon">:</td>
            <td class="value">{{ $pembelian->tgl_transaksi->format('d/m/Y') }} | {{ $pembelian->created_at->format('H:i') }}</td>
        </tr>
        <tr><td class="label">Jatuh Tempo</td><td class="colon">:</td>
            <td class="value">{{ $pembelian->tgl_jatuh_tempo ? $pembelian->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr><td class="label">Pembayaran</td><td class="colon">:</td><td class="value">{{ $pembelian->metode_pembayaran ?? 'Net 30' }}</td></tr>
        <tr><td class="label">Pemasok</td><td class="colon">:</td><td class="value">{{ $pembelian->supplier ?? '-' }}</td></tr>
        <tr><td class="label">Diminta</td><td class="colon">:</td><td class="value">{{ $pembelian->user->name ?? '-' }}</td></tr>
        <tr><td class="label">Gudang</td><td class="colon">:</td><td class="value">{{ $pembelian->gudang->nama_gudang ?? '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="colon">:</td><td class="value">{{ $pembelian->status }}</td></tr>
        @if($pembelian->approver_id && $pembelian->approver)
        <tr><td class="label">Disetujui</td><td class="colon">:</td><td class="value">{{ $pembelian->approver->name }}</td></tr>
        @endif
    </table>

    <div class="divider"></div>

    <!-- ITEMS -->
    @foreach($pembelian->items as $item)
        <div class="item-container">
            <div class="item-name">
                {{ $item->produk->nama_produk }} ({{ $item->produk->item_code ?? '-' }})
            </div>

            <table class="details-table">
                <tr><td class="lbl">Qty</td><td class="val">{{ $item->kuantitas }} Pcs</td></tr>
                <tr><td class="lbl">Harga</td><td class="val">Rp {{ number_format($item->harga_satuan,0,',','.') }}</td></tr>
                <tr><td class="lbl" style="font-weight:bold">Jumlah</td>
                    <td class="val" style="font-weight:bold">Rp {{ number_format($item->jumlah_baris,0,',','.') }}</td>
                </tr>
            </table>
        </div>
    @endforeach

    <div class="divider"></div>

    <!-- TOTAL -->
    <table class="total-table">
        <tr><td class="lbl">Subtotal</td><td class="val">Rp {{ number_format($pembelian->items->sum('jumlah_baris'),0,',','.') }}</td></tr>
        @if($pembelian->diskon_akhir > 0)
            <tr><td class="lbl">Diskon</td><td class="val">- Rp {{ number_format($pembelian->diskon_akhir,0,',','.') }}</td></tr>
        @endif
        @if($pembelian->tax_percentage > 0)
        @php
            $kenaPajak = max(0, $pembelian->items->sum('jumlah_baris') - $pembelian->diskon_akhir);
            $pajakNominal = $kenaPajak * ($pembelian->tax_percentage / 100);
        @endphp
            <tr><td class="lbl">Pajak ({{ $pembelian->tax_percentage }}%)</td><td class="val">Rp {{ number_format($pajakNominal,0,',','.') }}</td></tr>
        @endif
        <tr>
            <td class="lbl grand-total">GRAND TOTAL</td>
            <td class="val grand-total">Rp {{ number_format($pembelian->grand_total,0,',','.') }}</td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <div>procurement@hibiscusefsya.com</div>
        <div>-- Dokumen Internal --</div>
    </div>

<script>
// Auto-print setelah halaman load
window.addEventListener('load', () => {
    setTimeout(() => {
        window.print();
    }, 300);
});
</script>

</body>
</html>
