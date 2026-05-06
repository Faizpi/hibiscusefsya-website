<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian</title>
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
        $nomor = 'PR-' . $pembelian->user_id . '-' . $pembelian->created_at->format('Ymd') . '-' . str_pad($pembelian->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $tanggal = $pembelian->tgl_transaksi ? $pembelian->tgl_transaksi->format('d/m/Y') : '-';
        $jam = $pembelian->created_at ? $pembelian->created_at->format('H:i') : '';
        $subtotal = (float) $pembelian->items->sum('jumlah_baris');
        $diskonAkhir = (float) ($pembelian->diskon_akhir ?? 0);
        $taxPct = (float) ($pembelian->tax_percentage ?? 0);
        $taxNominal = max(0, $subtotal - $diskonAkhir) * ($taxPct / 100);
        $formatMoney = function ($value) {
            return 'Rp ' . number_format((float) $value, 3, ',', '.');
        };
        $formatQty = function ($qty) {
            $qty = (float) $qty;
            if (fmod($qty, 1.0) === 0.0) {
                return number_format($qty, 0, ',', '.');
            }
            return rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
        };
        $formatPct = function ($pct) {
            $pct = (float) $pct;
            return fmod($pct, 1.0) === 0.0 ? number_format($pct, 0) : rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.');
        };
    @endphp

    <div class="receipt">
        <div class="center big">HIBISCUS EFSYA</div>
        <div class="center title">INVOICE PEMBELIAN</div>
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
                <td>Jatuh Tempo</td>
                <td class="right">{{ $pembelian->tgl_jatuh_tempo ? $pembelian->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Pembayaran</td>
                <td class="right">{{ $pembelian->syarat_pembayaran ?? '-' }}</td>
            </tr>
            <tr>
                <td>Vendor</td>
                <td class="right">{{ $pembelian->staf_penyetuju ?? '-' }}</td>
            </tr>
            <tr>
                <td>Dibuat oleh</td>
                <td class="right">{{ optional($pembelian->user)->name ?? '-' }}</td>
            </tr>
            @if(!empty($pembelian->tahun_anggaran))
                <tr>
                    <td>Thn Anggaran</td>
                    <td class="right">{{ $pembelian->tahun_anggaran }}</td>
                </tr>
            @endif
            @if(!empty($pembelian->memo))
                <tr>
                    <td>Memo</td>
                    <td class="right">{{ $pembelian->memo }}</td>
                </tr>
            @endif
        </table>

        <div class="line">--------------------------------</div>

        @foreach($pembelian->items as $item)
            @php
                $namaProduk = (optional($item->produk)->nama_produk ?? '-') . (optional($item->produk)->item_code ? ' (' . optional($item->produk)->item_code . ')' : '');
                $qtyText = $formatQty($item->kuantitas);
                $unitText = $item->unit ?? $item->satuan ?? 'Pcs';
                $diskonItem = (float) ($item->diskon ?? 0);
                $batch = $item->batch_number ?? $item->batch ?? null;
                $expDate = $item->expired_date ?? $item->exp ?? null;
            @endphp
            <div class="item-name">{{ $namaProduk }}</div>
            <table>
                <tr>
                    <td>Qty</td>
                    <td class="right">{{ $qtyText }} {{ $unitText }}</td>
                </tr>
            </table>
            @if($diskonItem > 0)
                <table>
                    <tr>
                        <td>Diskon</td>
                        <td class="right">{{ $formatPct($diskonItem) }}%</td>
                    </tr>
                </table>
            @endif
            @if(!empty($batch))
                <table>
                    <tr>
                        <td>Batch</td>
                        <td class="right">{{ $batch }}</td>
                    </tr>
                </table>
            @endif
            @if(!empty($expDate))
                <table>
                    <tr>
                        <td>Exp</td>
                        <td class="right">{{ \Carbon\Carbon::parse($expDate)->format('d/m/Y') }}</td>
                    </tr>
                </table>
            @endif
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
                    <td class="right">{{ $formatMoney($item->jumlah_baris) }}</td>
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
            @if($diskonAkhir > 0)
                <tr>
                    <td>Diskon</td>
                    <td class="right">- {{ $formatMoney($diskonAkhir) }}</td>
                </tr>
            @endif
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
                <td class="right bold">{{ $formatMoney($pembelian->grand_total) }}</td>
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
