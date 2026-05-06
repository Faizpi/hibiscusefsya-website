<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            background: #fff;
            font-family: "Courier New", monospace;
            font-size: 10px;
            color: #111;
        }

        .receipt {
            width: 58mm;
            margin: 0 auto;
            padding: 3mm 2mm;
            box-sizing: border-box;
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
            margin: 2px 0;
            white-space: nowrap;
            overflow: hidden;
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
        }

        td.right {
            text-align: right;
            white-space: nowrap;
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

        @media screen {
            body {
                background: #ececec;
            }

            .receipt {
                background: #fff;
                box-shadow: 0 0 8px rgba(0, 0, 0, .2);
                margin: 12px auto;
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
                <td class="right">{{ optional($pembayaran->user)->name ?? '-' }}</td>
            </tr>
            @if($pembayaran->penjualan)
                <tr>
                    <td>No. Invoice</td>
                    <td class="right">{{ $pembayaran->penjualan->nomor ?? $pembayaran->penjualan->custom_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td class="right">{{ $pembayaran->penjualan->pelanggan ?? '-' }}</td>
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
        <div class="center bold">Periksa Invoice &amp; Ambil Promo !!!</div>
        <div class="footer-gap"></div>
        <div class="center muted">- - - - - - - - - - - - - - - -</div>
        <div class="footer-gap"></div>
        <div class="center">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode('https://customer.hibiscusefsya.com/') }}"
                alt="QR Customer" style="width:95px;height:95px;">
        </div>
        <div class="center muted">customer.hibiscusefsya.com</div>
        <div class="footer-gap"></div>
        <div class="center muted">- - - - - - - - - - - - - - - -</div>
        <div class="footer-gap"></div>
        <div class="center">marketing@hibiscusefsya.com</div>
        <div class="footer-gap"></div>
        <div class="center">Official WA Chat: +6285195550202</div>
        <div class="footer-gap"></div>
        <div class="center bold">Terima kasih</div>
    </div>
</body>

</html>
