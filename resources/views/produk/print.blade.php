<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    @php
        $itemKode = $produk->item_kode ?? $produk->item_code ?? 'PRD'.$produk->id;
        $itemNama = $produk->item_nama ?? $produk->nama_produk;
        $barcodeUrl = 'https://barcodeapi.org/api/128/' . urlencode($itemKode);
        $qrData = "PRODUK\nKode: {$itemKode}\nNama: {$itemNama}\nHarga: Rp " . number_format($produk->harga ?? 0, 0, ',', '.');
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrData);
    @endphp
    <title>Print Barcode Produk - {{ $itemKode }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        
        html, body {
            width: 100%;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            color: #000;
        }
        
        .receipt {
            width: 58mm;
            margin: 0 auto;
            padding: 3mm 2mm;
            box-sizing: border-box;
        }
        
        @media screen {
            html { background: #e0e0e0; }
            .receipt {
                background: #fff;
                box-shadow: 0 0 6px rgba(0,0,0,.3);
                margin: 2rem auto;
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        
        .header .title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .barcode-section {
            text-align: center;
            padding: 10px 0;
        }
        
        .barcode-section img {
            max-width: 100%;
            height: auto;
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
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <div class="header">
            <div class="title">LABEL PRODUK</div>
            <div style="font-size: 9pt;">{{ config('app.name') }}</div>
        </div>
        
        <div class="barcode-section">
            <img src="{{ $barcodeUrl }}" alt="Barcode">
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
                    if($produk->gudangProduks) {
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
