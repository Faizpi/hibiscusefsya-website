<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bukti Pembayaran - {{ $pembayaran->custom_number }}</title>
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
            max-width: 480px;
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

        .amount-section {
            background: var(--primary);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
        }

        .amount-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .amount-value {
            font-size: 28px;
            font-weight: 700;
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
        }
    </style>
</head>

<body>
    @php
        $invoiceUrl = url('invoice/pembayaran/' . $pembayaran->uuid);

        $statusClass = 'pending';
        $statusText = $pembayaran->status;
        if ($pembayaran->status == 'Approved') {
            $statusClass = 'approved';
        } elseif ($pembayaran->status == 'Canceled') {
            $statusClass = 'canceled';
        }
    @endphp

    <div class="invoice-container">
        <div class="invoice-header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" alt="Hibiscus Efsya" onerror="this.style.display='none'">
            <h1>Bukti Pembayaran</h1>
            <div class="invoice-number">{{ $pembayaran->custom_number }}</div>
        </div>

        <div class="invoice-body">
            <div class="amount-section">
                <div class="amount-label">Jumlah Pembayaran</div>
                <div class="amount-value">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</div>
            </div>

            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-receipt"></i> Informasi Pembayaran</div>
                <div class="info-row">
                    <span class="label">Tanggal</span>
                    <span class="value">{{ $pembayaran->tgl_pembayaran->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Waktu</span>
                    <span class="value">{{ $pembayaran->created_at->format('H:i') }} WIB</span>
                </div>
                <div class="info-row">
                    <span class="label">Metode</span>
                    <span class="value">{{ $pembayaran->metode_pembayaran }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $statusClass }}">{{ $statusText }}</span>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-file-invoice"></i> Referensi Invoice</div>
                @if($pembayaran->penjualan)
                <div class="info-row">
                    <span class="label">No. Invoice</span>
                    <span class="value">{{ $pembayaran->penjualan->custom_number }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Pelanggan</span>
                    <span class="value">{{ $pembayaran->penjualan->pelanggan ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Total Invoice</span>
                    <span class="value">Rp {{ number_format($pembayaran->penjualan->grand_total, 0, ',', '.') }}</span>
                </div>
                @else
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">Invoice tidak tersedia</span>
                </div>
                @endif
            </div>

            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-user"></i> Dibuat Oleh</div>
                <div class="info-row">
                    <span class="label">Nama</span>
                    <span class="value">{{ $pembayaran->user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Gudang</span>
                    <span class="value">{{ $pembayaran->gudang->nama_gudang ?? '-' }}</span>
                </div>
                @if($pembayaran->approver)
                <div class="info-row">
                    <span class="label">Disetujui oleh</span>
                    <span class="value">{{ $pembayaran->approver->name }}</span>
                </div>
                @endif
            </div>

            @if($pembayaran->keterangan)
            <div class="info-card">
                <div class="info-card-title"><i class="fas fa-sticky-note"></i> Keterangan</div>
                <p style="font-size: 13px; color: var(--text-secondary);">{{ $pembayaran->keterangan }}</p>
            </div>
            @endif

            <div class="qr-section">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($invoiceUrl) }}"
                    alt="QR Code">
                <p>Scan untuk melihat bukti pembayaran ini</p>
            </div>

            <a href="{{ route('public.invoice.pembayaran.download', $pembayaran->uuid) }}" class="btn-download">
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
