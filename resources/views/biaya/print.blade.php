<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Biaya</title>
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
                <td class="right">{{ optional($biaya->user)->name ?? '-' }}</td>
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
