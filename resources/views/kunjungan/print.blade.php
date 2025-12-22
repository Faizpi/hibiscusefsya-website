<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Kunjungan</title>

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

        .tujuan-badge {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #000;
            font-weight: bold;
            margin-top: 4px;
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
            $dateCode = $kunjungan->created_at->format('Ymd');
            $noUrutPadded = str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $customNumber = "VST-{$dateCode}-{$kunjungan->user_id}-{$noUrutPadded}";
        @endphp

        {{-- HEADER --}}
        <div class="header">
            <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo" class="logo">
            <div class="title">BUKTI KUNJUNGAN</div>
            <div>{{ $customNumber }}</div>
        </div>

        <div class="divider"></div>

        {{-- INFO UTAMA --}}
        <table>
            <tr>
                <td class="label">Tanggal</td>
                <td class="colon">:</td>
                <td class="value">{{ $kunjungan->tgl_kunjungan->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="colon">:</td>
                <td class="value">{{ $kunjungan->created_at->format('H:i') }} WIB</td>
            </tr>
            <tr>
                <td class="label">Tujuan</td>
                <td class="colon">:</td>
                <td class="value">{{ $kunjungan->tujuan }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        {{-- DETAIL SALES/KONTAK --}}
        <table>
            <tr>
                <td class="label">Sales</td>
                <td class="colon">:</td>
                <td class="value">{{ $kunjungan->sales_nama }}</td>
            </tr>
            @if($kunjungan->sales_email)
                <tr>
                    <td class="label">Email</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $kunjungan->sales_email }}</td>
                </tr>
            @endif
            @if($kunjungan->sales_alamat)
                <tr>
                    <td class="label">Alamat</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $kunjungan->sales_alamat }}</td>
                </tr>
            @endif
        </table>

        <div class="divider"></div>

        {{-- INFO GUDANG & PEMBUAT --}}
        <table>
            <tr>
                <td class="label">Gudang</td>
                <td class="colon">:</td>
                <td class="value">{{ optional($kunjungan->gudang)->nama_gudang ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pembuat</td>
                <td class="colon">:</td>
                <td class="value">{{ $kunjungan->user->name }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">{{ $kunjungan->status }}</td>
            </tr>
            @if($kunjungan->status != 'Pending' && $kunjungan->approver)
                <tr>
                    <td class="label">Approver</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $kunjungan->approver->name }}</td>
                </tr>
            @endif
        </table>

        @if($kunjungan->koordinat)
            <div class="divider"></div>
            <table>
                <tr>
                    <td class="label">Koordinat</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $kunjungan->koordinat }}</td>
                </tr>
            </table>
        @endif

        @if($kunjungan->memo)
            <div class="divider"></div>
            <table>
                <tr>
                    <td colspan="3"><strong>Memo:</strong></td>
                </tr>
                <tr>
                    <td colspan="3">{{ $kunjungan->memo }}</td>
                </tr>
            </table>
        @endif

        <div class="divider"></div>

        {{-- QR CODE --}}
        <div class="qr-section">
            {!! QrCode::size(100)->generate(route('public.invoice.kunjungan', $kunjungan->uuid)) !!}
            <p>Scan untuk detail kunjungan</p>
        </div>

        <div class="divider"></div>

        {{-- FOOTER --}}
        <div class="footer">
            <p>Terima Kasih</p>
            <p style="font-size: 8pt; color: #666;">{{ config('app.name') }}</p>
        </div>
    </div>

</body>

</html>