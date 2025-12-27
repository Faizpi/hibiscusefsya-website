<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Print Barcode Kontak - {{ $kontak->kode_kontak }}</title>
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

        .barcode-section img {
            max-width: 80%;
            height: auto;
            max-height: 80px;
        }

        .code {
            font-size: 18pt;
            font-weight: bold;
            margin: 8px 0 4px;
            letter-spacing: 2px;
        }

        .nama {
            font-size: 11pt;
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
        @php
            $barcodeUrl = 'https://barcodeapi.org/api/128/' . urlencode($kontak->kode_kontak);
            $qrData = "KONTAK\nKode: {$kontak->kode_kontak}\nNama: {$kontak->nama}\nTelp: {$kontak->no_telp}";
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrData);
        @endphp

        <div class="header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" class="logo">
            <div class="title">KARTU KONTAK</div>
        </div>

        <div class="barcode-section">
            <img src="{{ $barcodeUrl }}" alt="Barcode">
            <div class="code">{{ $kontak->kode_kontak }}</div>
            <div class="nama">{{ $kontak->nama }}</div>
        </div>

        <div class="divider"></div>

        <div class="info-section">
            <table>
                @if($kontak->email)
                    <tr>
                        <td class="label">Email</td>
                        <td>: {{ $kontak->email }}</td>
                    </tr>
                @endif
                @if($kontak->no_telp)
                    <tr>
                        <td class="label">Telepon</td>
                        <td>: {{ $kontak->no_telp }}</td>
                    </tr>
                @endif
                @if($kontak->alamat)
                    <tr>
                        <td class="label">Alamat</td>
                        <td>: {{ Str::limit($kontak->alamat, 50) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="label">Diskon</td>
                    <td>: {{ $kontak->diskon_persen ?? 0 }}%</td>
                </tr>
            </table>
        </div>

        <div class="divider"></div>

        <div class="qr-section">
            <img src="{{ $qrUrl }}" alt="QR Code">
            <p>Scan untuk info kontak</p>
        </div>

        <div class="footer">
            Dicetak: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>