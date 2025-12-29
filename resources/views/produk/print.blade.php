<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    @php
        use Milon\Barcode\DNS1D;
        $itemKode = $produk->item_kode ?? $produk->item_code ?? 'PRD' . $produk->id;
        $itemNama = $produk->item_nama ?? $produk->nama_produk;
        $eanData = preg_replace('/\D/', '', $itemKode);
        $dns1d = new DNS1D();
        $barcodeSvg = (strlen($eanData) === 12 || strlen($eanData) === 13)
            ? $dns1d->getBarcodeSVG($eanData, 'EAN13', 2, 80, 'black', false)
            : null;
        $qrData = "PRODUK\nKode: {$itemKode}\nNama: {$itemNama}\nHarga: Rp " . number_format($produk->harga ?? 0, 0, ',', '.');
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrData);
    @endphp
    <title>Print Barcode Produk - {{ $itemKode }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        html,
        body {
            width: 100%;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            color: #000;
        }

        .receipt {
            width: 100%;
            max-width: 180mm;
            margin: 0 auto;
            padding: 10mm;
            box-sizing: border-box;
        }

        @media screen {
            html {
                background: #e0e0e0;
            }

            .receipt {
                background: #fff;
                box-shadow: 0 0 6px rgba(0, 0, 0, .3);
                margin: 2rem auto;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .header .logo {
            max-width: 80mm;
            margin-bottom: 10px;
        }

        .header .title {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .barcode-section {
            text-align: center;
            padding: 20px 0;
        }

        .barcode-section svg {
            max-width: 80%;
            height: auto;
            max-height: 80px;
        }

        .code {
            font-size: 14pt;
            font-weight: bold;
            margin: 8px 0 4px;
            letter-spacing: 2px;
        }

        .nama {
            font-size: 10pt;
            margin-bottom: 4px;
        }

        .harga {
            font-size: 12pt;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 8px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .info-section {
            font-size: 9pt;
        }

        .info-section table {
            width: 100%;
        }

        .info-section td {
            padding: 2px 0;
            vertical-align: top;
        }

        .info-section .label {
            width: 35%;
            font-weight: bold;
        }

        .qr-section {
            text-align: center;
            padding: 8px 0;
        }

        .qr-section img {
            width: 80px;
            height: 80px;
        }

        .qr-section p {
            font-size: 8pt;
            margin-top: 4px;
            color: #666;
        }

        .footer {
            text-align: center;
            font-size: 8pt;
            color: #666;
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="receipt">
        <div class="header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" class="logo">
            <div class="title">LABEL PRODUK</div>
        </div>

        <div class="barcode-section">
            @if($barcodeSvg)
                <div style="background:#fff;padding:10px;display:inline-block;">
                    {!! $barcodeSvg !!}
                </div>
            @else
                <div class="alert alert-warning" style="display:inline-block;">
                    Barcode EAN-13 hanya untuk kode numerik 12/13 digit.
                </div>
            @endif
            <div class="code">{{ $itemKode }}</div>
            <div class="nama">{{ $itemNama }}</div>
            <div class="harga">Rp {{ number_format($produk->harga ?? 0, 0, ',', '.') }}</div>
        </div>

        <div class="divider"></div>

        <div class="info-section">
            <table>
                @if($produk->deskripsi)
                    <tr>
                        <td class="label">Deskripsi</td>
                        <td>: {{ Str::limit($produk->deskripsi, 40) }}</td>
                    </tr>
                @endif
                @php
                    $totalStok = 0;
                    if ($produk->gudangProduks) {
                        $totalStok = $produk->gudangProduks->sum('stok');
                    }
                @endphp
                <tr>
                    <td class="label">Total Stok</td>
                    <td>: {{ $totalStok }} unit</td>
                </tr>
            </table>
        </div>

        <div class="divider"></div>

        <div class="qr-section">
            <img src="{{ $qrUrl }}" alt="QR Code">
            <p>Scan untuk info produk</p>
        </div>

        <div class="footer">
            Dicetak: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>