<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        :root {
            --receipt-screen-width: 72mm;
            --receipt-print-width: 58mm;
            --paper: #fff;
            --ink: #111;
        }

        @page {
            size: 58mm auto;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: "Courier New", monospace;
            font-size: 10px;
            color: var(--ink);
        }

        body {
            background: #ececec;
            padding: 8px 0 12px;
        }

        .receipt {
            position: relative;
            width: var(--receipt-screen-width);
            margin: 8px auto;
            padding: 4mm 3mm 5mm;
            box-sizing: border-box;
            background: var(--paper);
            box-shadow: 0 8px 22px rgba(0, 0, 0, .18);
            border-left: 1px solid rgba(0, 0, 0, .06);
            border-right: 1px solid rgba(0, 0, 0, .06);
            overflow: visible;
        }

        .receipt::before,
        .receipt::after {
            content: "";
            position: absolute;
            left: -1px;
            right: -1px;
            height: 3mm;
            background: linear-gradient(-45deg, transparent 1.5mm, var(--paper) 0) 0 0 / 3mm 100% repeat-x,
                linear-gradient(45deg, transparent 1.5mm, var(--paper) 0) 1.5mm 0 / 3mm 100% repeat-x;
        }

        .receipt::before {
            top: -3mm;
        }

        .receipt::after {
            bottom: -3mm;
            transform: rotate(180deg);
        }

        .center {
            text-align: center;
        }

        .title {
            font-weight: 700;
            letter-spacing: .5px;
        }

        .big {
            font-size: 13px;
            font-weight: 700;
        }

        .line {
            margin: 6px 0;
            border-top: 1px dashed #111;
            height: 0;
            font-size: 0;
            line-height: 0;
            overflow: visible;
        }

        .spacer {
            height: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 1px 0;
            line-height: 1.35;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        td:first-child {
            width: 40%;
            padding-right: 4px;
        }

        td:last-child {
            width: 60%;
        }

        td.right {
            text-align: right;
        }

        .bold {
            font-weight: 700;
        }

        .footer-gap {
            height: 4px;
        }

        .muted {
            color: #444;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .receipt {
                width: var(--receipt-print-width);
                margin: 3mm auto;
                box-shadow: none;
                border: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    @php
        $nomor = $pembayaran->custom_number ?: ('PYM-' . $pembayaran->id);
        $tanggal = $pembayaran->tgl_pembayaran ? $pembayaran->tgl_pembayaran->format('d/m/Y') : '-';
        $jam = $pembayaran->created_at ? $pembayaran->created_at->format('H:i') : '';
        $formatMoney = function ($value) {
            return 'Rp ' . number_format((float) $value, 3, ',', '.');
        };
    @endphp

    <div class="receipt">
        <div class="center big">HIBISCUS EFSYA</div>
        <div class="center title">INVOICE PEMBAYARAN</div>
        <div class="spacer"></div>
        <div class="line">--------------------------------</div>

        <table>
            <tr>
                <td>Nomor</td>
                <td class="right">{{ $nomor }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="right">{{ trim($tanggal . ' | ' . $jam) }}</td>
            </tr>
            <tr>
                <td>Metode</td>
                <td class="right">{{ $pembayaran->metode_pembayaran ?? '-' }}</td>
            </tr>
            <tr>
                <td>Dibuat oleh</td>
                <td class="right">{{ receipt_limit_text(optional($pembayaran->user)->name ?: '-') }}</td>
            </tr>
            @if($pembayaran->penjualan)
                <tr>
                    <td>No. Invoice</td>
                    <td class="right">{{ $pembayaran->penjualan->nomor ?? $pembayaran->penjualan->custom_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td class="right">{{ receipt_limit_text($pembayaran->penjualan->pelanggan ?: '-') }}</td>
                </tr>
            @endif
            @if(!empty($pembayaran->keterangan))
                <tr>
                    <td>Keterangan</td>
                    <td class="right">{{ $pembayaran->keterangan }}</td>
                </tr>
            @endif
        </table>

        <div class="line">--------------------------------</div>
        <table>
            <tr>
                <td class="bold">JUMLAH BAYAR</td>
                <td class="right bold">{{ $formatMoney($pembayaran->jumlah_bayar) }}</td>
            </tr>
        </table>

        <div class="line">--------------------------------</div>
        <div class="footer-gap"></div>
        <div class="center bold">Periksa Invoice &amp; Ambil Promo !</div>
        <div class="footer-gap"></div>
        <div class="center muted">- - - - - - - - - - - - - - - -</div>
        <div class="footer-gap"></div>
        <div class="center">marketing@hibiscusefsya.com</div>
        <div class="footer-gap"></div>
        <div class="center muted">- - - - - - - - - - - - - - - -</div>
        <div class="footer-gap"></div>
        <div class="center">Official WA Chat:</div>
        <div class="center">{{ receipt_format_phone('+6285195550202') }}</div>
        <div class="footer-gap"></div>
        <div class="center bold">Terima kasih</div>
    </div>
</body>

</html>
