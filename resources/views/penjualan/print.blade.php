<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Penjualan</title>

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
            $dateCode = $penjualan->created_at->format('Ymd');
            $noUrut = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $nomorInvoice = "INV-{$penjualan->user_id}-{$dateCode}-{$noUrut}";
            $invoiceUrl = url('invoice/penjualan/' . $penjualan->uuid);
        @endphp

        <div class="header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" class="logo">
            <div class="title">INVOICE PENJUALAN</div>
        </div>

        <table>
            <tr>
                <td class="label">Nomor</td>
                <td class="colon">:</td>
                <td class="value">{{ $nomorInvoice }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="colon">:</td>
                <td class="value">{{ $penjualan->tgl_transaksi->format('d/m/Y') }} |
                    {{ $penjualan->created_at->format('H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Jatuh Tempo</td>
                <td class="colon">:</td>
                <td class="value">{{ $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d/m/Y') : '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">Pembayaran</td>
                <td class="colon">:</td>
                <td class="value">{{ $penjualan->syarat_pembayaran ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pelanggan</td>
                <td class="colon">:</td>
                <td class="value">{{ $penjualan->pelanggan }}</td>
            </tr>
            <tr>
                <td class="label">Sales</td>
                <td class="colon">:</td>
                <td class="value">{{ $penjualan->user->name }}</td>
            </tr>
            <tr>
                <td class="label">Disetujui</td>
                <td class="colon">:</td>
                <td class="value">{{ $penjualan->status == 'Pending' ? '-' : ($penjualan->approver->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">Gudang</td>
                <td class="colon">:</td>
                <td class="value">{{ $penjualan->gudang->nama_gudang ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">{{ $penjualan->status_display }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        @foreach($penjualan->items as $item)
            <div>
                <div class="item-name">{{ $item->produk->nama_produk }}</div>
                <table>
                    <tr>
                        <td>Qty</td>
                        <td class="val">{{ $item->kuantitas }} {{ $item->unit ?? 'Pcs' }}</td>
                    </tr>
                    <tr>
                        <td>Harga</td>
                        <td class="val">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    </tr>
                    @if($item->diskon > 0)
                        <tr>
                            <td>Disc</td>
                            <td class="val">{{ $item->diskon }}%</td>
                        </tr>
                    @endif
                    <tr>
                        <td><b>Jumlah</b></td>
                        <td class="val"><b>Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</b></td>
                    </tr>
                </table>
            </div>
        @endforeach

        <div class="divider"></div>

        <table>
            <tr>
                <td>Subtotal</td>
                <td class="val">Rp {{ number_format($penjualan->items->sum('jumlah_baris'), 0, ',', '.') }}</td>
            </tr>

            @if($penjualan->diskon_akhir > 0)
                <tr>
                    <td>Diskon</td>
                    <td class="val">- Rp {{ number_format($penjualan->diskon_akhir, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if($penjualan->tax_percentage > 0)
                @php
                    $subtotal = $penjualan->items->sum('jumlah_baris');
                    $kenaPajak = max(0, $subtotal - $penjualan->diskon_akhir);
                    $pajakNominal = $kenaPajak * ($penjualan->tax_percentage / 100);
                @endphp
                <tr>
                    <td>Pajak ({{ $penjualan->tax_percentage }}%)</td>
                    <td class="val">Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
                </tr>
            @endif

            <tr>
                <td class="grand-total">GRAND TOTAL</td>
                <td class="val grand-total">Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($invoiceUrl) }}"
                style="width:90px;height:90px;">
            <p>Scan untuk melihat invoice</p>
        </div>

        <div class="footer">
            <p>marketing@hibiscusefsya.com</p>
            <p>-- Terima Kasih --</p>
        </div>

    </div>
</body>

</html>