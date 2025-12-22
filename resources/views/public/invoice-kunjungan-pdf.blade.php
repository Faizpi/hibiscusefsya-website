<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kunjungan PDF</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: #1f2937;
            margin: 0 0 5px 0;
        }

        .header .number {
            font-size: 14px;
            color: #6b7280;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }

        .info-table .label {
            width: 35%;
            color: #6b7280;
        }

        .info-table .value {
            width: 65%;
            font-weight: 500;
            color: #1f2937;
        }

        .tujuan-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
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

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
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

        .memo-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #9ca3af;
            font-size: 11px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }

        .two-column {
            width: 100%;
        }

        .two-column td {
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }
    </style>
</head>

<body>
    @php
        $dateCode = $kunjungan->created_at->format('Ymd');
        $noUrut = str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $customNumber = "VST-{$dateCode}-{$kunjungan->user_id}-{$noUrut}";
    @endphp

    {{-- HEADER --}}
    <div class="header">
        <h1>BUKTI KUNJUNGAN</h1>
        <div class="number">{{ $customNumber }}</div>
    </div>

    {{-- TUJUAN --}}
    <div style="text-align: center; margin-bottom: 20px;">
        @if($kunjungan->tujuan == 'Pemeriksaan Stock')
            <span class="tujuan-badge tujuan-stock">{{ $kunjungan->tujuan }}</span>
        @elseif($kunjungan->tujuan == 'Penagihan')
            <span class="tujuan-badge tujuan-penagihan">{{ $kunjungan->tujuan }}</span>
        @else
            <span class="tujuan-badge tujuan-penawaran">{{ $kunjungan->tujuan }}</span>
        @endif
    </div>

    {{-- INFO KUNJUNGAN --}}
    <table class="two-column">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Informasi Kunjungan</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">Tanggal</td>
                            <td class="value">{{ $kunjungan->tgl_kunjungan->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Waktu Buat</td>
                            <td class="value">{{ $kunjungan->created_at->format('H:i') }} WIB</td>
                        </tr>
                        <tr>
                            <td class="label">Status</td>
                            <td class="value">
                                @if($kunjungan->status == 'Approved')
                                    <span class="status-badge status-approved">{{ $kunjungan->status }}</span>
                                @elseif($kunjungan->status == 'Pending')
                                    <span class="status-badge status-pending">{{ $kunjungan->status }}</span>
                                @else
                                    <span class="status-badge status-canceled">{{ $kunjungan->status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Gudang</td>
                            <td class="value">{{ optional($kunjungan->gudang)->nama_gudang ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="section">
                    <div class="section-title">Sales / Kontak</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">Nama</td>
                            <td class="value">{{ $kunjungan->sales_nama }}</td>
                        </tr>
                        <tr>
                            <td class="label">Email</td>
                            <td class="value">{{ $kunjungan->sales_email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Alamat</td>
                            <td class="value">{{ $kunjungan->sales_alamat ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- PETUGAS --}}
    <div class="section">
        <div class="section-title">Detail Petugas</div>
        <table class="info-table">
            <tr>
                <td class="label" style="width: 20%;">Pembuat</td>
                <td class="value">{{ $kunjungan->user->name }}</td>
            </tr>
            @if($kunjungan->status != 'Pending' && $kunjungan->approver)
                <tr>
                    <td class="label">Approver</td>
                    <td class="value">{{ $kunjungan->approver->name }}</td>
                </tr>
            @endif
            @if($kunjungan->koordinat)
                <tr>
                    <td class="label">Koordinat</td>
                    <td class="value">{{ $kunjungan->koordinat }}</td>
                </tr>
            @endif
        </table>
    </div>

    {{-- MEMO --}}
    @if($kunjungan->memo)
        <div class="section">
            <div class="section-title">Memo / Catatan</div>
            <div class="memo-box">
                {{ $kunjungan->memo }}
            </div>
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p><strong>Hibiscus Efsya</strong></p>
        <p>Dokumen ini sah tanpa tanda tangan</p>
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }} WIB</p>
    </div>
</body>

</html>