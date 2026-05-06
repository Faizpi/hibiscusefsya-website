<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Kunjungan</title>
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
        $nomor = 'VST-' . $kunjungan->user_id . '-' . $kunjungan->created_at->format('Ymd') . '-' . str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $tanggal = $kunjungan->tgl_kunjungan ? $kunjungan->tgl_kunjungan->format('d/m/Y') : '-';
        $jam = $kunjungan->created_at ? $kunjungan->created_at->format('H:i') : '';
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
                <td class="right">{{ $nomor }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="right">{{ trim($tanggal . ' | ' . $jam) }}</td>
            </tr>
            <tr>
                <td>Tujuan</td>
                <td class="right">{{ $kunjungan->tujuan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Pembuat</td>
                <td class="right">{{ optional($kunjungan->user)->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td class="right">{{ $kunjungan->sales_nama ?? optional($kunjungan->kontak)->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>No. Telepon</td>
                <td class="right">{{ $kunjungan->sales_no_telepon ?? '-' }}</td>
            </tr>
            @if(!empty($kunjungan->sales_alamat))
                <tr>
                    <td>Alamat</td>
                    <td class="right">{{ $kunjungan->sales_alamat }}</td>
                </tr>
            @endif
            @if(!empty($kunjungan->koordinat))
                <tr>
                    <td>Koordinat</td>
                    <td class="right">{{ $kunjungan->koordinat }}</td>
                </tr>
            @endif
            @if(!empty($kunjungan->memo))
                <tr>
                    <td>Memo</td>
                    <td class="right">{{ $kunjungan->memo }}</td>
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
                    <td class="right">{{ $qtyText }} {{ $satuan }}</td>
                </tr>
                <tr>
                    <td>Batch</td>
                    <td class="right">{{ $batch }}</td>
                </tr>
                <tr>
                    <td>Exp</td>
                    <td class="right">{{ $exp }}</td>
                </tr>
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
