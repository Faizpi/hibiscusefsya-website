<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Pembayaran</title>

    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        html,
        body {
            width: 100%;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        body {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 10pt;
            color: #000;
        }

        * {
            word-wrap: break-word;
            overflow-wrap: break-word;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .receipt {
            width: 58mm;
            margin: 0 auto;
            padding: 3mm 1mm;
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
            margin-bottom: 8px;
        }

        .logo {
            max-width: 45mm;
            margin-bottom: 4px;
        }

        .title {
            font-size: 12pt;
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            font-size: 9pt;
        }

        td {
            padding-bottom: 2px;
            vertical-align: top;
        }

        .label {
            width: 35%;
        }

        .colon {
            width: 5%;
            text-align: center;
        }

        .value {
            width: 60%;
        }

        .val {
            text-align: right;
        }

        .grand-total {
            font-weight: bold;
            font-size: 12pt;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }

        .qr-section {
            text-align: center;
            margin-top: 10px;
        }

        .qr-section p {
            font-size: 8pt;
            margin-top: 4px;
        }

        .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 9pt;
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
            $invoiceUrl = url('invoice/pembayaran/' . $pembayaran->uuid);
        @endphp

        <div class="header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" class="logo">
            <div class="title">BUKTI PEMBAYARAN</div>
        </div>

        <table>
            <tr>
                <td class="label">Nomor</td>
                <td class="colon">:</td>
                <td class="value">{{ $pembayaran->custom_number }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="colon">:</td>
                <td class="value">{{ $pembayaran->tgl_pembayaran->format('d/m/Y') }} |
                    {{ $pembayaran->created_at->format('H:i') }}
                </td>
            </tr>
            <tr>
                <td class="label">Metode</td>
                <td class="colon">:</td>
                <td class="value">{{ $pembayaran->metode_pembayaran }}</td>
            </tr>
            <tr>
                <td class="label">Dibuat oleh</td>
                <td class="colon">:</td>
                <td class="value">{{ $pembayaran->user->name }}</td>
            </tr>
            <tr>
                <td class="label">Disetujui</td>
                <td class="colon">:</td>
                <td class="value">{{ $pembayaran->status == 'Pending' ? '-' : ($pembayaran->approver->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">Gudang</td>
                <td class="colon">:</td>
                <td class="value">{{ $pembayaran->gudang->nama_gudang ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">{{ $pembayaran->status }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <table>
            <tr>
                <td colspan="3"><b>Referensi Invoice:</b></td>
            </tr>
            @if($pembayaran->penjualan)
            <tr>
                <td class="label">No. Invoice</td>
                <td class="colon">:</td>
                <td class="value">{{ $pembayaran->penjualan->custom_number }}</td>
            </tr>
            <tr>
                <td class="label">Pelanggan</td>
                <td class="colon">:</td>
                <td class="value">{{ $pembayaran->penjualan->pelanggan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Total Invoice</td>
                <td class="colon">:</td>
                <td class="value">Rp {{ number_format($pembayaran->penjualan->grand_total, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>

        <div class="divider"></div>

        <table>
            <tr>
                <td class="grand-total">JUMLAH BAYAR</td>
                <td class="val grand-total">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</td>
            </tr>
        </table>

        @if($pembayaran->keterangan)
        <div class="divider"></div>
        <p style="font-size: 8pt;"><b>Keterangan:</b> {{ $pembayaran->keterangan }}</p>
        @endif

        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($invoiceUrl) }}"
                style="width:90px;height:90px;">
            <p>Scan untuk melihat bukti pembayaran</p>
        </div>

        <div class="footer">
            <p>marketing@hibiscusefsya.com</p>
            <p>-- Terima Kasih --</p>
        </div>

    </div>
</body>

</html>
