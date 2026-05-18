<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penerimaan Barang</title>
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
        $nomor = $penerimaan->custom_number ?: ('RCV-' . $penerimaan->id);
        $tanggal = $penerimaan->tgl_penerimaan ? $penerimaan->tgl_penerimaan->format('d/m/Y') : '-';
        $jam = $penerimaan->created_at ? $penerimaan->created_at->format('H:i') : '';
        $formatQty = function ($qty) {
            $qty = (float) $qty;
            if (fmod($qty, 1.0) === 0.0) {
                return number_format($qty, 0, ',', '.');
            }
            return rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
        };
    @endphp

    <div class="receipt">
        <div class="center big">HIBISCUS EFSYA</div>
        <div class="center title">INVOICE PENERIMAAN BARANG</div>
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
                <td>No. Surat Jalan</td>
                <td class="right">{{ $penerimaan->no_surat_jalan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Dibuat oleh</td>
                <td class="right">{{ receipt_limit_text(optional($penerimaan->user)->name ?: '-') }}</td>
            </tr>
            @if($penerimaan->pembelian)
                <tr>
                    <td>No. Pembelian</td>
                    <td class="right">{{ $penerimaan->pembelian->nomor ?? $penerimaan->pembelian->custom_number ?? '-' }}</td>
                </tr>
            @endif
            @if(!empty($penerimaan->keterangan))
                <tr>
                    <td>Keterangan</td>
                    <td class="right">{{ $penerimaan->keterangan }}</td>
                </tr>
            @endif
        </table>

        <div class="line">--------------------------------</div>

        @php
            $totalDiterima = 0;
            $totalReject = 0;
        @endphp

        @foreach($penerimaan->items as $item)
            @php
                $totalDiterima += (float) ($item->qty_diterima ?? 0);
                $totalReject += (float) ($item->qty_reject ?? 0);
                $namaProduk = optional($item->produk)->item_nama ?? optional($item->produk)->nama_produk ?? '-';
                $satuan = optional($item->produk)->satuan ?? 'Pcs';
                $stokType = ucfirst($item->tipe_stok ?? 'penjualan');
            @endphp
            <div class="item-name">{{ $namaProduk }}</div>
            <table>
                <tr>
                    <td>Tipe</td>
                    <td class="right">{{ $stokType }}</td>
                </tr>
                @if(!empty($item->batch_number))
                    <tr>
                        <td>Batch</td>
                        <td class="right">{{ $item->batch_number }}</td>
                    </tr>
                @endif
                @if(!empty($item->expired_date))
                    <tr>
                        <td>Exp</td>
                        <td class="right">{{ $item->expired_date->format('d/m/Y') }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Diterima</td>
                    <td class="right">{{ $formatQty($item->qty_diterima) }} {{ $satuan }}</td>
                </tr>
                @if(($item->qty_reject ?? 0) > 0)
                    <tr>
                        <td>Reject</td>
                        <td class="right">{{ $formatQty($item->qty_reject) }} {{ $satuan }}</td>
                    </tr>
                @endif
                @if(!empty($item->keterangan))
                    <tr>
                        <td>Ket</td>
                        <td class="right">{{ $item->keterangan }}</td>
                    </tr>
                @endif
            </table>
            <div class="footer-gap"></div>
        @endforeach

        <div class="line">--------------------------------</div>
        <table>
            <tr>
                <td class="bold">Total Diterima</td>
                <td class="right bold">{{ $formatQty($totalDiterima) }} item</td>
            </tr>
            @if($totalReject > 0)
                <tr>
                    <td class="bold">Total Reject</td>
                    <td class="right bold">{{ $formatQty($totalReject) }} item</td>
                </tr>
            @endif
        </table>

        <div class="line">--------------------------------</div>
        <div class="receipt-footer">
            <div class="footer-gap"></div>
            <div class="center bold">Periksa Invoice &amp; Ambil Promo !</div>
            <div class="footer-gap"></div>
            <div class="center muted">- - - - - - - - - - - - - - - -</div>
            <div class="footer-gap"></div>
            <div class="center">customer.hibiscusefsya.com</div>
            <div class="footer-gap"></div>
            <div class="center">marketing@hibiscusefsya.com</div>
            <div class="footer-gap"></div>
            <div class="center">Official WA Chat:</div>
            <div class="center">{{ receipt_format_phone('+6285195550202') }}</div>
            <div class="footer-gap"></div>
            <div class="center bold">Terima kasih</div>
        </div>
    </div>
</body>

</html>
