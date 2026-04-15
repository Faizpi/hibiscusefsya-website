<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Export Harian Tagihan Invoice</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
            margin: 20px;
        }

        .header {
            border-bottom: 2px solid #dc2626;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .header h2 {
            margin: 0 0 4px 0;
            color: #dc2626;
            font-size: 18px;
        }

        .meta {
            font-size: 10px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #e5e7eb;
            padding: 6px 7px;
            text-align: left;
            font-size: 10px;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 6px 7px;
            vertical-align: top;
            font-size: 9px;
        }

        tr:nth-child(even) {
            background: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row td {
            background: #fee2e2;
            font-weight: bold;
            color: #7f1d1d;
        }

        .empty {
            text-align: center;
            margin-top: 30px;
            color: #6b7280;
            font-style: italic;
        }

        .lampiran-item {
            margin-bottom: 2px;
            word-break: break-all;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Export Harian Tagihan Invoice Penjualan</h2>
        <div class="meta">
            <strong>Tanggal Transaksi:</strong> {{ $tanggal->format('d F Y') }} |
            <strong>Dibuat oleh:</strong> {{ $generatedBy }} |
            <strong>Dicetak:</strong> {{ $generatedAt->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($invoices->isEmpty())
        <div class="empty">Tidak ada tagihan invoice penjualan (Approved, belum Lunas) pada tanggal ini.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="13%">Nomor Invoice</th>
                    <th width="10%">Tgl Transaksi</th>
                    <th width="10%">Syarat Bayar</th>
                    <th width="10%">Jatuh Tempo</th>
                    <th width="13%">Nama Toko</th>
                    <th width="12%" class="text-right">Jumlah</th>
                    <th width="14%">Koordinat</th>
                    <th width="14%">Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $index => $item)
                    @php
                        $invoiceNomor = $item->nomor ?? $item->custom_number ?? '-';
                        $lampiranPaths = $item->lampiran_paths ?? [];
                        if ($item->lampiran_path && !in_array($item->lampiran_path, $lampiranPaths)) {
                            $lampiranPaths[] = $item->lampiran_path;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $invoiceNomor }}</td>
                        <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                        <td>{{ $item->syarat_pembayaran ?? '-' }}</td>
                        <td>{{ $item->tgl_jatuh_tempo ? $item->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
                        <td>{{ $item->pelanggan ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format($item->jumlah_tagihan, 0, ',', '.') }}</td>
                        <td>{{ $item->koordinat ?? '-' }}</td>
                        <td>
                            @if(count($lampiranPaths) > 0)
                                @foreach($lampiranPaths as $lampiran)
                                    <div class="lampiran-item">{{ basename($lampiran) }}</div>
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="6" class="text-right">Total</td>
                    <td class="text-right">Rp {{ number_format($totalJumlah, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    @endif
</body>

</html>