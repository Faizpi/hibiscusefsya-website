<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Penerimaan Barang</title>

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

        .reject {
            color: #dc3545;
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
            $invoiceUrl = url('invoice/penerimaan-barang/' . $penerimaan->uuid);
        @endphp

        <div class="header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" class="logo">
            <div class="title">PENERIMAAN BARANG</div>
        </div>

        <table>
            <tr>
                <td class="label">Nomor</td>
                <td class="colon">:</td>
                <td class="value">{{ $penerimaan->custom_number }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="colon">:</td>
                <td class="value">{{ $penerimaan->tgl_penerimaan->format('d/m/Y') }} |
                    {{ $penerimaan->created_at->format('H:i') }}
                </td>
            </tr>
            <tr>
                <td class="label">No. Surat Jalan</td>
                <td class="colon">:</td>
                <td class="value">{{ $penerimaan->no_surat_jalan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Dibuat oleh</td>
                <td class="colon">:</td>
                <td class="value">{{ $penerimaan->user->name }}</td>
            </tr>
            <tr>
                <td class="label">Disetujui</td>
                <td class="colon">:</td>
                <td class="value">{{ $penerimaan->status == 'Pending' ? '-' : ($penerimaan->approver->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">Gudang</td>
                <td class="colon">:</td>
                <td class="value">{{ $penerimaan->gudang->nama_gudang ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">{{ $penerimaan->status }}</td>
            </tr>
        </table>

        @if($penerimaan->pembelian)
        <div class="divider"></div>
        <table>
            <tr>
                <td colspan="3"><b>Referensi PO:</b></td>
            </tr>
            <tr>
                <td class="label">No. PO</td>
                <td class="colon">:</td>
                <td class="value">{{ $penerimaan->pembelian->custom_number }}</td>
            </tr>
        </table>
        @endif

        <div class="divider"></div>

        @php
            $totalDiterima = 0;
            $totalReject = 0;
        @endphp

        @foreach($penerimaan->items as $item)
            @php
                $totalDiterima += $item->qty_diterima;
                $totalReject += $item->qty_reject ?? 0;
            @endphp
            <div>
                <div class="item-name">{{ $item->produk->nama_produk ?? '-' }}</div>
                <div style="font-size: 7pt; color: #555; margin: 1px 0;">
                    [{{ ucfirst($item->tipe_stok ?? 'penjualan') }}]
                    @if($item->batch_number) | Batch: {{ $item->batch_number }} @endif
                    @if($item->expired_date) | Exp: {{ $item->expired_date->format('d/m/Y') }} @endif
                </div>
                <table>
                    <tr>
                        <td>Diterima</td>
                        <td class="val">{{ $item->qty_diterima }} {{ $item->produk->satuan ?? 'Pcs' }}</td>
                    </tr>
                    @if(($item->qty_reject ?? 0) > 0)
                    <tr>
                        <td class="reject">Reject</td>
                        <td class="val reject">{{ $item->qty_reject }} {{ $item->produk->satuan ?? 'Pcs' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        @endforeach

        <div class="divider"></div>

        <table>
            <tr>
                <td><b>Total Diterima</b></td>
                <td class="val"><b>{{ $totalDiterima }} item</b></td>
            </tr>
            @if($totalReject > 0)
            <tr>
                <td class="reject"><b>Total Reject</b></td>
                <td class="val reject"><b>{{ $totalReject }} item</b></td>
            </tr>
            @endif
        </table>

        @if($penerimaan->keterangan)
        <div class="divider"></div>
        <p style="font-size: 8pt;"><b>Keterangan:</b> {{ $penerimaan->keterangan }}</p>
        @endif

        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($invoiceUrl) }}"
                style="width:90px;height:90px;">
            <p>Scan untuk melihat detail penerimaan</p>
        </div>

        <div class="footer">
            <p>marketing@hibiscusefsya.com</p>
            <p>-- Terima Kasih --</p>
        </div>

    </div>
</body>

</html>
