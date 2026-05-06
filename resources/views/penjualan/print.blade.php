<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan</title>
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

        .muted {
            color: #444;
        }

        .bold {
            font-weight: 700;
        }

        .footer-gap {
            height: 4px;
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
        $nomor = 'INV-' . $penjualan->user_id . '-' . $penjualan->created_at->format('Ymd') . '-' . str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $tanggal = $penjualan->tgl_transaksi ? $penjualan->tgl_transaksi->format('d/m/Y') : '-';
        $jam = $penjualan->created_at ? $penjualan->created_at->format('H:i') : '';
        $noTelepon = $penjualan->no_telepon ?? $penjualan->email ?? 'N/A';
        $salesPhone = optional($penjualan->user)->no_telp ?? optional($penjualan->user)->no_telepon ?? 'N/A';
        $subtotal = (float) $penjualan->items->sum('jumlah_baris');
        $diskonAkhir = (float) ($penjualan->diskon_akhir ?? 0);
        $taxPct = (float) ($penjualan->tax_percentage ?? 0);
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
        <div class="center title">INVOICE PENJUALAN</div>
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
                <td class="right">{{ $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Pembayaran</td>
                <td class="right">{{ $penjualan->syarat_pembayaran ?? '-' }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td class="right">{{ $penjualan->pelanggan ?? '-' }}</td>
            </tr>
            <tr>
                <td>No. Telepon</td>
                <td class="right">{{ $noTelepon ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td>Sales</td>
                <td class="right">{{ optional($penjualan->user)->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>No. Telp Sales</td>
                <td class="right">{{ $salesPhone ?: 'N/A' }}</td>
            </tr>
            @if(!empty($penjualan->no_referensi))
                <tr>
                    <td>No. Ref</td>
                    <td class="right">{{ $penjualan->no_referensi }}</td>
                </tr>
            @endif
            @if(!empty($penjualan->memo))
                <tr>
                    <td>Memo</td>
                    <td class="right">{{ $penjualan->memo }}</td>
                </tr>
            @endif
        </table>

        <div class="line">--------------------------------</div>

        @foreach($penjualan->items as $item)
            @php
                $namaProduk = (optional($item->produk)->nama_produk ?? '-') . (optional($item->produk)->item_code ? ' (' . optional($item->produk)->item_code . ')' : '');
                $batch = $item->batch_number ?: 'N/A';
                $exp = $item->expired_date ? $item->expired_date->format('d/m/Y') : 'N/A';
                $qtyText = $formatQty($item->kuantitas);
                $diskonItem = (float) ($item->diskon ?? 0);
            @endphp
            <div class="item-name">{{ $namaProduk }}</div>
            <div>{{ $batch }} - {{ $exp }}&nbsp;&nbsp;{{ $qtyText }} x {{ $formatMoney($item->harga_satuan) }}</div>
            @if($diskonItem > 0)
                <table>
                    <tr>
                        <td>Diskon</td>
                        <td class="right">{{ $formatPct($diskonItem) }}%</td>
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
                <td class="right bold">{{ $formatMoney($penjualan->grand_total) }}</td>
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
