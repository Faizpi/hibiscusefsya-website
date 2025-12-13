<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Biaya</title>

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
            font-size: 8pt;
            color: #000;
            padding: 2mm;
            box-sizing: border-box;
            background: #fff;
            text-align: center;
            line-height: 1.2;
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
            margin: 0 auto 6px;
            width: 100%;
        }

        .logo {
            max-width: 30mm;
            height: auto;
            margin-bottom: 2px;
        }

        .title {
            font-size: 10pt;
            font-weight: bold;
            margin: 0 0 3px;
        }

        /* =========================
           DIVIDER
        ==========================*/
        .divider {
            border-top: 1px dashed #000;
            margin: 3px 0;
        }

        /* =========================
           INFO TABLE
        ==========================*/
        .info-table {
            width: 100%;
            font-size: 7pt;
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
            margin-bottom: 3px;
        }

        .item-name {
            font-weight: bold;
            font-size: 8pt;
            margin-bottom: 1px;
        }

        .details-table {
            width: 100%;
            font-size: 7pt;
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
            font-size: 7pt;
        }

        .total-table td {
            padding: 1px 0;
        }

        .grand-total {
            font-weight: bold;
            font-size: 9pt;
            border-top: 1px dashed #000;
            padding-top: 2px;
        }

        /* =========================
           FOOTER
        ==========================*/
        .footer {
            text-align: center;
            font-size: 7pt;
            margin-top: 5px;
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
        $dateCode = $biaya->created_at->format('Ymd');
        $noUrut = str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorBiaya = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}";
    @endphp

    <!-- HEADER -->
    <div class="header">
        @if(file_exists(public_path('assets/img/logoHE1.png')))
        <img src="{{ asset('assets/img/logoHE1.png') }}" class="logo">
        @endif
        <div class="title">BUKTI PENGELUARAN</div>
    </div>

    <!-- INFO -->
    <table class="info-table">
        <tr><td class="label">Nomor</td><td class="colon">:</td><td class="value">{{ $nomorBiaya }}</td></tr>
        <tr><td class="label">Tanggal</td><td class="colon">:</td>
            <td class="value">{{ $biaya->tgl_transaksi->format('d/m/Y') }} | {{ $biaya->created_at->format('H:i') }}</td>
        </tr>
        <tr><td class="label">Jatuh Tempo</td><td class="colon">:</td>
            <td class="value">{{ $biaya->tgl_jatuh_tempo ? $biaya->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr><td class="label">Pembayaran</td><td class="colon">:</td><td class="value">{{ $biaya->metode_pembayaran ?? 'Cash' }}</td></tr>
        <tr><td class="label">Kontak</td><td class="colon">:</td><td class="value">{{ $biaya->nama_pemasok ?? '-' }}</td></tr>
        <tr><td class="label">Diinput</td><td class="colon">:</td><td class="value">{{ $biaya->user->name ?? '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="colon">:</td><td class="value">{{ $biaya->status }}</td></tr>
        @if($biaya->approver_id && $biaya->approver)
        <tr><td class="label">Disetujui</td><td class="colon">:</td><td class="value">{{ $biaya->approver->name }}</td></tr>
        @endif
    </table>

    <div class="divider"></div>

    <!-- ITEMS -->
    @foreach($biaya->items as $item)
        <div class="item-container">
            <div class="item-name">
                {{ $item->kategori ?? 'Kategori' }}
            </div>

            <table class="details-table">
                <tr><td class="lbl">Deskripsi</td><td class="val">{{ $item->deskripsi ?? '-' }}</td></tr>
                <tr><td class="lbl" style="font-weight:bold">Jumlah</td>
                    <td class="val" style="font-weight:bold">Rp {{ number_format($item->jumlah,0,',','.') }}</td>
                </tr>
            </table>
        </div>
    @endforeach

    <div class="divider"></div>

    <!-- TOTAL -->
    <table class="total-table">
        <tr>
            <td class="lbl grand-total">TOTAL BIAYA</td>
            <td class="val grand-total">Rp {{ number_format($biaya->items->sum('jumlah'),0,',','.') }}</td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <div>accounting@hibiscusefsya.com</div>
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
