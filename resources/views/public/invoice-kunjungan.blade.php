<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bukti Kunjungan - {{ $kunjungan->sales_nama }}</title>
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
            --info: #06b6d4;
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
            text-align: right;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-canceled {
            background: #fee2e2;
            color: #991b1b;
        }

        .tujuan-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .tujuan-stock {
            background: #e0f2fe;
            color: #0369a1;
        }

        .tujuan-penagihan {
            background: #fef3c7;
            color: #92400e;
        }

        .tujuan-penawaran {
            background: #dcfce7;
            color: #166534;
        }

        .memo-section {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .memo-section h4 {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .memo-section p {
            font-size: 13px;
            color: var(--text-primary);
            line-height: 1.5;
        }

        .invoice-footer {
            background: var(--bg-light);
            padding: 20px 24px;
            text-align: center;
            border-top: 1px solid var(--border-color);
        }

        .footer-brand {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .footer-text {
            font-size: 11px;
            color: var(--text-muted);
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            margin-top: 16px;
            transition: all 0.2s ease;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .koordinat-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .koordinat-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    @php
        $dateCode = $kunjungan->created_at->format('Ymd');
        $noUrut = str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $customNumber = "VST-{$dateCode}-{$kunjungan->user_id}-{$noUrut}";
    @endphp

    <div class="invoice-container">
        {{-- HEADER --}}
        <div class="invoice-header">
            <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo">
            <h1>BUKTI KUNJUNGAN</h1>
            <div class="invoice-number">{{ $customNumber }}</div>
        </div>

        {{-- BODY --}}
        <div class="invoice-body">
            {{-- TUJUAN KUNJUNGAN --}}
            <div style="text-align: center; margin-bottom: 16px;">
                @if($kunjungan->tujuan == 'Pemeriksaan Stock')
                    <span class="tujuan-badge tujuan-stock">
                        <i class="fas fa-clipboard-check mr-1"></i> {{ $kunjungan->tujuan }}
                    </span>
                @elseif($kunjungan->tujuan == 'Penagihan')
                    <span class="tujuan-badge tujuan-penagihan">
                        <i class="fas fa-hand-holding-usd mr-1"></i> {{ $kunjungan->tujuan }}
                    </span>
                @else
                    <span class="tujuan-badge tujuan-penawaran">
                        <i class="fas fa-handshake mr-1"></i> {{ $kunjungan->tujuan }}
                    </span>
                @endif
            </div>

            {{-- INFO KUNJUNGAN --}}
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fas fa-info-circle"></i> Informasi Kunjungan
                </div>
                <div class="info-row">
                    <span class="label">Tanggal</span>
                    <span class="value">{{ $kunjungan->tgl_kunjungan->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Waktu Buat</span>
                    <span class="value">{{ $kunjungan->created_at->format('H:i') }} WIB</span>
                </div>
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        @if($kunjungan->status == 'Approved')
                            <span class="status-badge status-approved">{{ $kunjungan->status }}</span>
                        @elseif($kunjungan->status == 'Pending')
                            <span class="status-badge status-pending">{{ $kunjungan->status }}</span>
                        @else
                            <span class="status-badge status-canceled">{{ $kunjungan->status }}</span>
                        @endif
                    </span>
                </div>
            </div>

            {{-- INFO SALES/KONTAK --}}
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fas fa-user"></i> Sales / Kontak
                </div>
                <div class="info-row">
                    <span class="label">Nama</span>
                    <span class="value">{{ $kunjungan->sales_nama }}</span>
                </div>
                @if($kunjungan->sales_email)
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value">{{ $kunjungan->sales_email }}</span>
                    </div>
                @endif
                @if($kunjungan->sales_alamat)
                    <div class="info-row">
                        <span class="label">Alamat</span>
                        <span class="value">{{ $kunjungan->sales_alamat }}</span>
                    </div>
                @endif
            </div>

            {{-- INFO GUDANG & PETUGAS --}}
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fas fa-warehouse"></i> Detail Petugas
                </div>
                <div class="info-row">
                    <span class="label">Gudang</span>
                    <span class="value">{{ optional($kunjungan->gudang)->nama_gudang ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Pembuat</span>
                    <span class="value">{{ $kunjungan->user->name }}</span>
                </div>
                @if($kunjungan->status != 'Pending' && $kunjungan->approver)
                    <div class="info-row">
                        <span class="label">Approver</span>
                        <span class="value">{{ $kunjungan->approver->name }}</span>
                    </div>
                @endif
                @if($kunjungan->koordinat)
                    <div class="info-row">
                        <span class="label">Koordinat</span>
                        <span class="value">
                            <a href="https://www.google.com/maps?q={{ $kunjungan->koordinat }}" target="_blank"
                                class="koordinat-link">
                                {{ $kunjungan->koordinat }} <i class="fas fa-external-link-alt fa-xs"></i>
                            </a>
                        </span>
                    </div>
                @endif
            </div>

            {{-- PRODUK ITEMS --}}
            @if($kunjungan->items && $kunjungan->items->count() > 0)
                <div class="info-card">
                    <div class="info-card-title">
                        <i class="fas fa-boxes"></i> Produk Terkait
                    </div>
                    @foreach($kunjungan->items as $index => $item)
                        <div class="info-row">
                            <span class="label">{{ $index + 1 }}. {{ optional($item->produk)->item_code ?? '-' }}</span>
                            <span class="value">{{ optional($item->produk)->nama_produk ?? '-' }}
                                ({{ $item->jumlah ?? 1 }})</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- MEMO --}}
            @if($kunjungan->memo)
                <div class="memo-section">
                    <h4><i class="fas fa-sticky-note"></i> Memo / Catatan</h4>
                    <p>{{ $kunjungan->memo }}</p>
                </div>
            @endif

            {{-- DOWNLOAD BUTTON --}}
            <a href="{{ route('public.invoice.kunjungan.download', $kunjungan->uuid) }}" class="download-btn">
                <i class="fas fa-download"></i> Download PDF
            </a>
        </div>

        {{-- FOOTER --}}
        <div class="invoice-footer">
            <div class="footer-brand">Hibiscus Efsya</div>
            <div class="footer-text">Dokumen ini sah tanpa tanda tangan</div>
        </div>
    </div>
</body>

</html>