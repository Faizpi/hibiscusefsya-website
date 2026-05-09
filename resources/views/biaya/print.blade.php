<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Biaya</title>
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

        .item-name {
            font-weight: 700;
            margin-top: 4px;
            margin-bottom: 1px;
            white-space: normal;
            word-break: break-word;
        }

        .bold {
            font-weight: 700;
        }

        .footer-gap {
            height: 4px;
        }

        .receipt-footer {
            font-size: 8px;
            line-height: 1.25;
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
        $nomor = 'EXP-' . $biaya->user_id . '-' . $biaya->created_at->format('Ymd') . '-' . str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $tanggal = $biaya->tgl_transaksi ? $biaya->tgl_transaksi->format('d/m/Y') : '-';
        $jam = $biaya->created_at ? $biaya->created_at->format('H:i') : '';
        $subtotal = (float) $biaya->items->sum('jumlah');
        $taxPct = (float) ($biaya->tax_percentage ?? 0);
        $taxNominal = $subtotal * ($taxPct / 100);
        $jenisBiaya = strtolower((string) ($biaya->jenis_biaya ?? '')) === 'masuk' ? 'Biaya Masuk' : 'Biaya Keluar';
        $formatMoney = function ($value) {
            return 'Rp ' . number_format((float) $value, 3, ',', '.');
        };
        $formatPct = function ($pct) {
            $pct = (float) $pct;
            return fmod($pct, 1.0) === 0.0 ? number_format($pct, 0) : rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.');
        };
    @endphp

    <div class="receipt">
        <div class="center big">HIBISCUS EFSYA</div>
        <div class="center title">STRUK BIAYA</div>
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
                <td>Jenis Biaya</td>
                <td class="right">{{ $jenisBiaya }}</td>
            </tr>
            <tr>
                <td>Bayar Dari</td>
                <td class="right">{{ $biaya->bayar_dari ?? '-' }}</td>
            </tr>
            <tr>
                <td>Cara Bayar</td>
                <td class="right">{{ $biaya->cara_pembayaran ?? '-' }}</td>
            </tr>
            <tr>
                <td>Penerima</td>
                <td class="right">{{ $biaya->penerima ?? '-' }}</td>
            </tr>
            <tr>
                <td>Dibuat oleh</td>
                <td class="right">{{ receipt_limit_text(optional($biaya->user)->name ?: '-') }}</td>
            </tr>
            @if(!empty($biaya->tag))
                <tr>
                    <td>Tag</td>
                    <td class="right">{{ $biaya->tag }}</td>
                </tr>
            @endif
            @if(!empty($biaya->koordinat))
                <tr>
                    <td>Koordinat</td>
                    <td class="right">{{ $biaya->koordinat }}</td>
                </tr>
            @endif
            @if(!empty($biaya->memo))
                <tr>
                    <td>Memo</td>
                    <td class="right">{{ $biaya->memo }}</td>
                </tr>
            @endif
        </table>

        <div class="line">--------------------------------</div>

        @foreach($biaya->items as $item)
            <div class="item-name">{{ $item->kategori ?? '-' }}</div>
            @if(!empty($item->deskripsi))
                <table>
                    <tr>
                        <td>Ket</td>
                        <td class="right">{{ $item->deskripsi }}</td>
                    </tr>
                </table>
            @endif
            <table>
                <tr>
                    <td>Jumlah</td>
                    <td class="right">{{ $formatMoney($item->jumlah) }}</td>
                </tr>
            </table>
            <div class="footer-gap"></div>
        @endforeach

        <div class="line">--------------------------------</div>
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="right">{{ $formatMoney($subtotal) }}</td>
            </tr>
            @if($taxNominal > 0)
                <tr>
                    <td>Pajak ({{ $formatPct($taxPct) }}%)</td>
                    <td class="right">{{ $formatMoney($taxNominal) }}</td>
                </tr>
            @endif
        </table>

        <div class="line">--------------------------------</div>
        <table>
            <tr>
                <td class="bold">GRAND TOTAL</td>
                <td class="right bold">{{ $formatMoney($biaya->grand_total) }}</td>
            </tr>
        </table>

        <div class="line">--------------------------------</div>
        <div class="receipt-footer">
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
    </div>
</body>

</html>
