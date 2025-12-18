<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Biaya</title>

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

        .item-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-desc {
            font-size: 9pt;
            color: #333;
            margin-bottom: 2px;
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
            $dateCode = $biaya->created_at->format('Ymd');
            $noUrut = str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $nomorInvoice = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}";
            $invoiceUrl = url('invoice/biaya/' . $biaya->id);
        @endphp

        <div class="header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" class="logo">
            <div class="title">{{ $biaya->jenis_biaya == 'masuk' ? 'BUKTI PEMASUKAN' : 'BUKTI PENGELUARAN' }}</div>
        </div>

        <table>
            <tr>
                <td class="label">Jenis</td>
                <td class="colon">:</td>
                <td class="value">{{ $biaya->jenis_biaya == 'masuk' ? 'Biaya Masuk' : 'Biaya Keluar' }}</td>
            </tr>
            <tr>
                <td class="label">Nomor</td>
                <td class="colon">:</td>
                <td class="value">{{ $nomorInvoice }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="colon">:</td>
                <td class="value">{{ $biaya->tgl_transaksi->format('d/m/Y') }} | {{ $biaya->created_at->format('H:i') }}
                </td>
            </tr>
            <tr>
                <td class="label">Pembayaran</td>
                <td class="colon">:</td>
                <td class="value">{{ $biaya->cara_pembayaran ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Bayar Dari</td>
                <td class="colon">:</td>
                <td class="value">{{ $biaya->bayar_dari ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Penerima</td>
                <td class="colon">:</td>
                <td class="value">{{ $biaya->penerima ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Sales</td>
                <td class="colon">:</td>
                <td class="value">{{ $biaya->user->name }}</td>
            </tr>
            <tr>
                <td class="label">Disetujui</td>
                <td class="colon">:</td>
                <td class="value">{{ $biaya->status == 'Pending' ? '-' : ($biaya->approver->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">{{ $biaya->status }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        @php $subtotal = $biaya->items->sum('jumlah'); @endphp

        @foreach($biaya->items as $item)
            <div>
                <div class="item-name">{{ $item->kategori }}</div>
                @if($item->deskripsi)
                    <div class="item-desc">{{ $item->deskripsi }}</div>
                @endif
                <table>
                    <tr>
                        <td><b>Jumlah</b></td>
                        <td class="val"><b>Rp {{ number_format($item->jumlah, 0, ',', '.') }}</b></td>
                    </tr>
                </table>
            </div>
        @endforeach

        <div class="divider"></div>

        <table>
            <tr>
                <td>Subtotal</td>
                <td class="val">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>

            @if(($biaya->tax_percentage ?? 0) > 0)
                @php
                    $pajakNominal = $subtotal * ($biaya->tax_percentage / 100);
                @endphp
                <tr>
                    <td>Pajak ({{ $biaya->tax_percentage }}%)</td>
                    <td class="val">Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
                </tr>
            @endif

            <tr>
                <td class="grand-total">GRAND TOTAL</td>
                <td class="val grand-total">Rp {{ number_format($biaya->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($invoiceUrl) }}"
                style="width:90px;height:90px;">
            <p>Scan untuk melihat dokumen</p>
        </div>

        <div class="footer">
            <p>marketing@hibiscusefsya.com</p>
            <p>-- Terima Kasih --</p>
        </div>

    </div>
</body>

</html>