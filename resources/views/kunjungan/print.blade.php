<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Kunjungan</title>
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

        .value-wrap {
            max-width: none;
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
        $nomor = 'VST-' . $kunjungan->user_id . '-' . $kunjungan->created_at->format('Ymd') . '-' . str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $tanggal = $kunjungan->tgl_kunjungan ? $kunjungan->tgl_kunjungan->format('d/m/Y') : '-';
        $jam = $kunjungan->created_at ? $kunjungan->created_at->format('H:i') : '';
        $noTelepon = $kunjungan->sales_no_telepon ?? optional($kunjungan->kontak)->no_telp ?? '-';
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
        <div class="center title">STRUK KUNJUNGAN</div>
        <div class="spacer"></div>
        <div class="line">--------------------------------</div>

        <table>
            <tr>
                <td>Nomor</td>
                <td class="right value-wrap">{{ $nomor }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="right value-wrap">{{ trim($tanggal . ' | ' . $jam) }}</td>
            </tr>
            <tr>
                <td>Tujuan</td>
                <td class="right value-wrap">{{ $kunjungan->tujuan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Pembuat</td>
                <td class="right value-wrap">{{ receipt_limit_text(optional($kunjungan->user)->name ?: '-') }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td class="right value-wrap">{{ receipt_limit_text($kunjungan->sales_nama ?: (optional($kunjungan->kontak)->nama ?: '-')) }}</td>
            </tr>
            <tr>
                <td>No. Telepon</td>
                <td class="right value-wrap">{{ receipt_format_phone($noTelepon) ?: 'N/A' }}</td>
            </tr>
            @if(!empty($kunjungan->sales_alamat))
                <tr>
                    <td>Alamat</td>
                    <td class="right value-wrap">{{ $kunjungan->sales_alamat }}</td>
                </tr>
            @endif
            @if(!empty($kunjungan->koordinat))
                <tr>
                    <td>Koordinat</td>
                    <td class="right value-wrap">{{ $kunjungan->koordinat }}</td>
                </tr>
            @endif
            @if(!empty($kunjungan->memo))
                <tr>
                    <td>Memo</td>
                    <td class="right value-wrap">{{ $kunjungan->memo }}</td>
                </tr>
            @endif
        </table>

        <div class="line">--------------------------------</div>

        @foreach($kunjungan->items as $item)
            @php
                $namaProduk = optional($item->produk)->nama_produk ?? '-';
                $qtyText = $formatQty($item->jumlah ?? 1);
                $satuan = optional($item->produk)->satuan ?? 'Pcs';
                $batch = $item->batch_number ?: 'N/A';
                $exp = $item->expired_date ? $item->expired_date->format('d/m/Y') : 'N/A';
            @endphp
            <div class="item-name">{{ $namaProduk }}</div>
            <table>
                <tr>
                    <td>Qty</td>
                    <td class="right value-wrap">{{ $qtyText }} {{ $satuan }}</td>
                </tr>
                <tr>
                    <td>Batch</td>
                    <td class="right value-wrap">{{ $batch }}</td>
                </tr>
                <tr>
                    <td>Exp</td>
                    <td class="right value-wrap">{{ $exp }}</td>
                </tr>
                @if(!empty($item->keterangan))
                    <tr>
                        <td>Ket</td>
                        <td class="right value-wrap">{{ $item->keterangan }}</td>
                    </tr>
                @endif
            </table>
            <div class="footer-gap"></div>
        @endforeach

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
        <div class="center">Official WA Chat: {{ receipt_format_phone('+6285195550202') }}</div>
        <div class="footer-gap"></div>
        <div class="center bold">Terima kasih</div>
    </div>
</body>

</html>
