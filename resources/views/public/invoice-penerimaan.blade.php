<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Penerimaan Barang - {{ $penerimaan->custom_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --bg-light: #f9fafb;
            --bg-white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            min-height: 100vh;
            padding: 20px;
        }

        .invoice-container {
            max-width: 520px;
            margin: 0 auto;
            background: var(--bg-white);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .invoice-header {
            background: var(--bg-white);
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .invoice-header img {
            max-width: 140px;
            margin-bottom: 12px;
        }

        .invoice-header h1 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .invoice-number {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .invoice-body {
            padding: 24px;
        }

        .info-card {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .info-card-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-card-title i {
            color: var(--primary);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-row .label {
            color: var(--text-secondary);
        }

        .info-row .value {
            font-weight: 600;
            color: var(--text-primary);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-canceled {
            background: #fee2e2;
            color: #991b1b;
        }

        .items-section {
            margin-bottom: 20px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            background: var(--bg-light);
            padding: 10px 8px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            text-align: left;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table td {
            padding: 12px 8px;
            font-size: 13px;
            border-bottom: 1px solid var(--border-color);
        }

        .items-table .item-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .items-table .item-sku {
            font-size: 11px;
            color: var(--text-muted);
        }

        .qty-diterima {
            color: var(--success);
            font-weight: 600;
        }

        .qty-reject {
            color: var(--danger);
            font-weight: 600;
        }

        .totals-card {
            background: var(--primary);
            color: white;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .totals-card .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }

        .totals-card .total-row.main {
            font-size: 16px;
            font-weight: 700;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            padding-top: 12px;
            margin-top: 4px;
        }

        .qr-section {
            text-align: center;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .qr-section img {
            width: 100px;
            height: 100px;
            margin-bottom: 8px;
            border-radius: 8px;
        }

        .qr-section p {
            font-size: 11px;
            color: var(--text-muted);
        }

        .btn-download {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 14px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-download:hover {
            background: var(--primary-dark);
            color: white;
            text-decoration: none;
        }

        .invoice-footer {
            text-align: center;
            padding: 20px 24px;
            border-top: 1px solid var(--border-color);
            font-size: 12px;
            color: var(--text-muted);
        }

        .invoice-footer strong {
            color: var(--text-primary);
            display: block;
            margin-bottom: 4px;
        }

        @media (max-width: 400px) {
            body {
                padding: 12px;
            }

            .invoice-body {
                padding: 16px;
            }

            .items-table th,
            .items-table td {
                padding: 8px 4px;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    @php
        $invoiceUrl = url('invoice/penerimaan-barang/' . $penerimaan->uuid);

        $statusClass = 'pending';
        $statusText = $penerimaan->status;
        if ($penerimaan->status == 'Approved') {
            $statusClass = 'approved';
        } elseif ($penerimaan->status == 'Canceled') {
            $statusClass = 'canceled';
        }

        $totalDiterima = $penerimaan->items->sum('qty_diterima');
        $totalReject = $penerimaan->items->sum('qty_reject');
    @endphp

    <div class="invoice-container">
        <div class="invoice-header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" alt="Hibiscus Efsya" onerror="this.style.display='none'">
            <h1>Penerimaan Barang</h1>
            <div class="invoice-number">{{ $penerimaan->custom_number }}</div>
        </div>

        <div class="invoice-body">
            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-truck-loading"></i> Informasi Penerimaan</div>
                <div class="info-row">
                    <span class="label">Tanggal</span>
                    <span class="value">{{ $penerimaan->tgl_penerimaan->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Waktu</span>
                    <span class="value">{{ $penerimaan->created_at->format('H:i') }} WIB</span>
                </div>
                <div class="info-row">
                    <span class="label">Gudang</span>
                    <span class="value">{{ $penerimaan->gudang->nama_gudang ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $statusClass }}">{{ $statusText }}</span>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-file-invoice"></i> Referensi Pembelian</div>
                @if($penerimaan->pembelian)
                <div class="info-row">
                    <span class="label">Invoice</span>
                    <span class="value">{{ $penerimaan->pembelian->custom_number }}</span>
                </div>
                @else
                <div class="info-row">
                    <span class="label">Invoice</span>
                    <span class="value">-</span>
                </div>
                @endif
            </div>
            </div>

            <div class="info-card items-section">
                <div class="info-card-title"><i class="fas fa-box"></i> Detail Barang</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th style="text-align: center">Diterima</th>
                            <th style="text-align: center">Reject</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penerimaan->items as $item)
                        <tr>
                            <td>
                                <div class="item-name">{{ $item->produk->nama_produk ?? '-' }}</div>
                                <div class="item-sku">{{ $item->produk->kode_produk ?? '-' }}</div>
                                <div style="margin-top: 4px; font-size: 11px;">
                                    @if($item->tipe_stok == 'gratis')
                                        <span style="background: #28a745; color: #fff; padding: 1px 6px; border-radius: 3px; font-size: 10px;">Gratis</span>
                                    @elseif($item->tipe_stok == 'sample')
                                        <span style="background: #ffc107; color: #000; padding: 1px 6px; border-radius: 3px; font-size: 10px;">Sample</span>
                                    @else
                                        <span style="background: #4e73df; color: #fff; padding: 1px 6px; border-radius: 3px; font-size: 10px;">Penjualan</span>
                                    @endif
                                    @if($item->batch_number)
                                        <span style="color: var(--text-muted); margin-left: 4px;">Batch: {{ $item->batch_number }}</span>
                                    @endif
                                    @if($item->expired_date)
                                        <span style="color: var(--text-muted); margin-left: 4px;">Exp: {{ $item->expired_date->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align: center">
                                <span class="qty-diterima">{{ $item->qty_diterima }}</span>
                            </td>
                            <td style="text-align: center">
                                @if($item->qty_reject > 0)
                                <span class="qty-reject">{{ $item->qty_reject }}</span>
                                @else
                                <span style="color: var(--text-muted);">0</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="totals-card">
                <div class="total-row">
                    <span>Total Diterima</span>
                    <span>{{ $totalDiterima }} pcs</span>
                </div>
                @if($totalReject > 0)
                <div class="total-row">
                    <span>Total Reject</span>
                    <span>{{ $totalReject }} pcs</span>
                </div>
                @endif
                <div class="total-row main">
                    <span>Total Masuk Gudang</span>
                    <span>{{ $totalDiterima }} pcs</span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-user"></i> Dibuat Oleh</div>
                <div class="info-row">
                    <span class="label">Nama</span>
                    <span class="value">{{ $penerimaan->user->name }}</span>
                </div>
                @if($penerimaan->approver)
                <div class="info-row">
                    <span class="label">Disetujui oleh</span>
                    <span class="value">{{ $penerimaan->approver->name }}</span>
                </div>
                @endif
            </div>

            @if($penerimaan->keterangan)
            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-sticky-note"></i> Keterangan</div>
                <p style="font-size: 13px; color: var(--text-secondary);">{{ $penerimaan->keterangan }}</p>
            </div>
            @endif

            <div class="qr-section">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($invoiceUrl) }}"
                    alt="QR Code">
                <p>Scan untuk melihat dokumen ini</p>
            </div>

            <a href="{{ route('public.invoice.penerimaan.download', $penerimaan->uuid) }}" class="btn-download">
                <i class="fas fa-download"></i> Download PDF
            </a>
        </div>

        <div class="invoice-footer">
            <strong>HIBISCUS EFSYA</strong>
            marketing@hibiscusefsya.com
        </div>
    </div>
</body>

</html>
