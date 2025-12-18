<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice Penjualan - {{ $nomorInvoice }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 15px;
        }

        .logo {
            font-size: 18pt;
            font-weight: bold;
            color: #28a745;
            letter-spacing: 2px;
        }

        .tagline {
            font-size: 8pt;
            color: #666;
        }

        .invoice-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }

        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-left,
        .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .info-table {
            width: 100%;
            font-size: 9pt;
        }

        .info-table td {
            padding: 3px 0;
        }

        .info-table .label {
            width: 40%;
            color: #666;
        }

        .info-table .value {
            font-weight: 500;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .items-table th {
            background: #28a745;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9pt;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table td {
            padding: 8px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .total-section {
            width: 100%;
            margin-top: 15px;
        }

        .total-table {
            float: right;
            width: 45%;
            font-size: 10pt;
        }

        .total-table td {
            padding: 5px 0;
        }

        .total-table td:last-child {
            text-align: right;
        }

        .total-table .grand-total {
            font-weight: bold;
            font-size: 12pt;
            border-top: 2px solid #28a745;
            padding-top: 8px;
            color: #28a745;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .status-approved,
        .status-lunas {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-canceled {
            background: #f8d7da;
            color: #721c24;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .memo-section {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .memo-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }

        .memo-content {
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>

<body>
    @php
        $dateCode = $penjualan->created_at->format('Ymd');
        $noUrut = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "INV-{$penjualan->user_id}-{$dateCode}-{$noUrut}";
    @endphp

    <!-- HEADER -->
    <div class="header">
        <div class="logo">HIBISCUS EFSYA</div>
        <div class="tagline">marketing@hibiscusefsya.com</div>
        <div class="invoice-title">INVOICE PENJUALAN</div>
    </div>

    <!-- INFO SECTION -->
    <div class="info-section">
        <div class="info-left">
            <table class="info-table">
                <tr>
                    <td class="label">No. Invoice</td>
                    <td class="value">: {{ $nomorInvoice }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Transaksi</td>
                    <td class="value">: {{ $penjualan->tgl_transaksi->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Jatuh Tempo</td>
                    <td class="value">:
                        {{ $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Syarat Pembayaran</td>
                    <td class="value">: {{ $penjualan->syarat_pembayaran ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Metode Pembayaran</td>
                    <td class="value">: {{ $penjualan->metode_pembayaran ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="info-right">
            <table class="info-table">
                <tr>
                    <td class="label">Pelanggan</td>
                    <td class="value">: {{ $penjualan->pelanggan ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat</td>
                    <td class="value">: {{ $penjualan->alamat_pengiriman ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Sales</td>
                    <td class="value">: {{ $penjualan->user->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Gudang</td>
                    <td class="value">: {{ $penjualan->gudang->nama_gudang ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value">:
                        <span class="status-badge status-{{ strtolower($penjualan->status) }}">
                            {{ $penjualan->status }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ITEMS TABLE -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 35%">Produk</th>
                <th style="width: 10%">Qty</th>
                <th style="width: 15%">Harga</th>
                <th style="width: 15%">Diskon</th>
                <th style="width: 20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->produk->nama_produk ?? '-' }} <br><small
                            style="color:#999">({{ $item->produk->item_code ?? '-' }})</small></td>
                    <td>{{ $item->kuantitas }} Pcs</td>
                    <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->diskon_per_item ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TOTAL SECTION -->
    <div class="total-section clearfix">
        <table class="total-table">
            @php
                $subtotal = $penjualan->items->sum('jumlah_baris');
                $kenaPajak = max(0, $subtotal - ($penjualan->diskon_akhir ?? 0));
                $pajakNominal = $penjualan->tax_percentage > 0 ? $kenaPajak * ($penjualan->tax_percentage / 100) : 0;
            @endphp
            <tr>
                <td>Subtotal</td>
                <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($penjualan->diskon_akhir > 0)
                <tr>
                    <td>Diskon Akhir</td>
                    <td>- Rp {{ number_format($penjualan->diskon_akhir, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($penjualan->tax_percentage > 0)
                <tr>
                    <td>Pajak ({{ $penjualan->tax_percentage }}%)</td>
                    <td>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td class="grand-total">GRAND TOTAL</td>
                <td class="grand-total">Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @if($penjualan->memo)
        <div class="memo-section">
            <div class="memo-title">Catatan:</div>
            <div class="memo-content">{{ $penjualan->memo }}</div>
        </div>
    @endif

    @if($penjualan->approver)
        <div style="margin-top: 20px; font-size: 9pt;">
            <strong>Disetujui oleh:</strong> {{ $penjualan->approver->name }}
        </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem.</p>
        <p>marketing@hibiscusefsya.com</p>
    </div>
</body>

</html>