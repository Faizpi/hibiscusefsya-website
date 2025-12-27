<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bukti Kunjungan - {{ $kunjungan->nomor ?? 'VST' }}</title>
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
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 15px;
        }

        .logo {
            font-size: 18pt;
            font-weight: bold;
            color: #3b82f6;
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

        .tujuan-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 5px;
            font-size: 10pt;
            font-weight: bold;
            margin-top: 8px;
        }

        .tujuan-pemeriksaan {
            background: #dbeafe;
            color: #1e40af;
        }

        .tujuan-penagihan {
            background: #fef3c7;
            color: #92400e;
        }

        .tujuan-penawaran {
            background: #d1fae5;
            color: #065f46;
        }

        .tujuan-promo {
            background: #fce7f3;
            color: #9d174d;
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
            background: #3b82f6;
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

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .status-approved {
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

        .kontak-section {
            margin-top: 15px;
            padding: 10px;
            background: #eff6ff;
            border-radius: 5px;
            border-left: 3px solid #3b82f6;
        }

        .kontak-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 8px;
            color: #1e40af;
        }

        .kontak-content {
            font-size: 9pt;
            color: #333;
        }

        .kontak-content .row {
            margin-bottom: 3px;
        }
    </style>
</head>

<body>
    @php
        $dateCode = $kunjungan->created_at->format('Ymd');
        $noUrut = str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = $kunjungan->nomor ?? "VST-{$dateCode}-{$kunjungan->user_id}-{$noUrut}";
        
        $tujuanClass = [
            'Pemeriksaan Stock' => 'pemeriksaan',
            'Penagihan' => 'penagihan',
            'Penawaran' => 'penawaran',
            'Promo' => 'promo'
        ];
    @endphp

    <!-- HEADER -->
    <div class="header">
        <div class="logo">HIBISCUS EFSYA</div>
        <div class="tagline">marketing@hibiscusefsya.com</div>
        <div class="invoice-title">BUKTI KUNJUNGAN</div>
        <div class="tujuan-badge tujuan-{{ $tujuanClass[$kunjungan->tujuan] ?? 'pemeriksaan' }}">
            {{ strtoupper($kunjungan->tujuan) }}
        </div>
    </div>

    <!-- INFO SECTION -->
    <div class="info-section">
        <div class="info-left">
            <table class="info-table">
                <tr>
                    <td class="label">No. Kunjungan</td>
                    <td class="value">: {{ $nomorInvoice }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Kunjungan</td>
                    <td class="value">: {{ $kunjungan->tgl_kunjungan->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Tujuan</td>
                    <td class="value">: {{ $kunjungan->tujuan }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value">:
                        <span class="status-badge status-{{ strtolower($kunjungan->status) }}">
                            {{ $kunjungan->status }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="info-right">
            <table class="info-table">
                <tr>
                    <td class="label">Sales</td>
                    <td class="value">: {{ $kunjungan->sales_nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Email Sales</td>
                    <td class="value">: {{ $kunjungan->sales_email ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Pembuat</td>
                    <td class="value">: {{ $kunjungan->user->name ?? '-' }}</td>
                </tr>
                @if($kunjungan->gudang)
                <tr>
                    <td class="label">Gudang</td>
                    <td class="value">: {{ $kunjungan->gudang->nama_gudang ?? '-' }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <!-- KONTAK SECTION -->
    @if($kunjungan->kontak)
    <div class="kontak-section">
        <div class="kontak-title">Informasi Kontak yang Dikunjungi</div>
        <div class="kontak-content">
            <div class="row"><strong>Nama:</strong> {{ $kunjungan->kontak->nama ?? '-' }}</div>
            @if($kunjungan->kontak->telepon)
            <div class="row"><strong>Telepon:</strong> {{ $kunjungan->kontak->telepon }}</div>
            @endif
            @if($kunjungan->kontak->alamat)
            <div class="row"><strong>Alamat:</strong> {{ $kunjungan->kontak->alamat }}</div>
            @endif
        </div>
    </div>
    @endif

    <!-- ITEMS TABLE (if any) -->
    @if($kunjungan->items && $kunjungan->items->count() > 0)
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 40%">Produk</th>
                <th style="width: 15%">Jumlah</th>
                <th style="width: 40%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kunjungan->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->produk->nama ?? '-' }}</td>
                    <td>{{ $item->jumlah ?? '-' }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($kunjungan->koordinat)
        <div class="memo-section">
            <div class="memo-title">Koordinat Lokasi:</div>
            <div class="memo-content">{{ $kunjungan->koordinat }}</div>
        </div>
    @endif

    @if($kunjungan->memo)
        <div class="memo-section">
            <div class="memo-title">Catatan:</div>
            <div class="memo-content">{{ $kunjungan->memo }}</div>
        </div>
    @endif

    @if($kunjungan->approver)
        <div style="margin-top: 20px; font-size: 9pt;">
            <strong>Disetujui oleh:</strong> {{ $kunjungan->approver->name }}
        </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem.</p>
        <p>marketing@hibiscusefsya.com</p>
    </div>
</body>

</html>
