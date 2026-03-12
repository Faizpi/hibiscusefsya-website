<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Harian - {{ $date }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            color: #333;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a56db;
            padding-bottom: 10px;
        }

        .header h2 {
            color: #1a56db;
            margin: 0 0 5px 0;
            font-size: 16px;
        }

        .header .meta {
            font-size: 8px;
            color: #666;
        }

        .section-title {
            background: #1a56db;
            color: #fff;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            margin: 15px 0 5px 0;
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            background: #e8edf5;
            color: #1a56db;
            padding: 3px 4px;
            text-align: left;
            font-size: 7px;
            border: 1px solid #ccc;
        }

        td {
            border: 1px solid #ddd;
            padding: 3px 4px;
            font-size: 7px;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            color: #fff;
            font-size: 6px;
            display: inline-block;
        }

        .badge-success {
            background: #0e9f6e;
        }

        .badge-warning {
            background: #c27803;
        }

        .badge-secondary {
            background: #6b7280;
        }

        .badge-primary {
            background: #1a56db;
        }

        .badge-info {
            background: #0694a2;
        }

        .lampiran-thumb {
            max-width: 110px;
            max-height: 85px;
            border: 1px solid #ccc;
            margin: 2px;
        }

        .summary-box {
            margin-top: 15px;
            border: 1px solid #1a56db;
            border-radius: 5px;
            padding: 10px;
        }

        .summary-box h4 {
            color: #1a56db;
            margin: 0 0 8px 0;
            font-size: 10px;
        }

        .summary-table td {
            border: none;
            padding: 2px 5px;
            font-size: 9px;
        }

        .summary-table .label {
            font-weight: bold;
            width: 200px;
        }

        .empty-msg {
            text-align: center;
            color: #999;
            padding: 8px;
            font-style: italic;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            font-size: 7px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 3px;
            text-align: center;
        }

        .koordinat-link {
            color: #1a56db;
            font-size: 7px;
            word-break: break-all;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Harian</h2>
        <div class="meta">
            <strong>Sales:</strong> {{ $salesName }} |
            <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($date)->format('d F Y') }} |
            <strong>Dicetak:</strong> {{ $generatedAt }}
        </div>
    </div>

    {{-- ========== PENJUALAN ========== --}}
    <div class="section-title">
        <i></i> Penjualan ({{ $penjualans->count() }} data)
    </div>
    @if($penjualans->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="12%">Nomor</th>
                    <th width="10%">Tanggal / Jam</th>
                    <th width="12%">Pelanggan</th>
                    <th width="10%" class="text-right">Total</th>
                    <th width="5%">Status</th>
                    <th width="15%">Koordinat</th>
                    <th width="20%">Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($penjualans as $item)
                    @php
                        $paths = $item->lampiran_paths ?? [];
                        if ($item->lampiran_path && !in_array($item->lampiran_path, $paths)) {
                            $paths[] = $item->lampiran_path;
                        }
                        $imagePaths = collect($paths)->filter(function ($p) {
                            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $p);
                        });
                    @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item->custom_number }}</td>
                        <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}
                            {{ $item->created_at ? $item->created_at->format('H:i') : '' }}</td>
                        <td>{{ $item->pelanggan ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        <td>
                            @if($item->status == 'Approved')
                                <span class="badge badge-success">{{ $item->status }}</span>
                            @elseif($item->status == 'Pending')
                                <span class="badge badge-warning">{{ $item->status }}</span>
                            @elseif($item->status == 'Lunas')
                                <span class="badge badge-primary">{{ $item->status }}</span>
                            @else
                                <span class="badge badge-secondary">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->koordinat)
                                @php
                                    $coords = $item->koordinat;
                                    $mapsUrl = 'https://www.google.com/maps?q=' . $coords;
                                @endphp
                                <span class="koordinat-link">{{ $coords }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($imagePaths->count() > 0)
                                @foreach($imagePaths as $imgPath)
                                    @php $fullPath = public_path('storage/' . $imgPath); @endphp
                                    @if(file_exists($fullPath))
                                        <img src="{{ $fullPath }}" class="lampiran-thumb">
                                    @endif
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-msg">Tidak ada data penjualan hari ini.</div>
    @endif

    {{-- ========== PEMBELIAN ========== --}}
    <div class="section-title">
        Pembelian ({{ $pembelians->count() }} data)
    </div>
    @if($pembelians->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="12%">Nomor</th>
                    <th width="10%">Tanggal / Jam</th>
                    <th width="12%">Gudang</th>
                    <th width="10%" class="text-right">Total</th>
                    <th width="5%">Status</th>
                    <th width="15%">Koordinat</th>
                    <th width="20%">Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($pembelians as $item)
                    @php
                        $paths = $item->lampiran_paths ?? [];
                        if ($item->lampiran_path && !in_array($item->lampiran_path, $paths)) {
                            $paths[] = $item->lampiran_path;
                        }
                        $imagePaths = collect($paths)->filter(function ($p) {
                            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $p);
                        });
                    @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item->custom_number }}</td>
                        <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}
                            {{ $item->created_at ? $item->created_at->format('H:i') : '' }}</td>
                        <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        <td>
                            @if($item->status == 'Approved')
                                <span class="badge badge-success">{{ $item->status }}</span>
                            @elseif($item->status == 'Pending')
                                <span class="badge badge-warning">{{ $item->status }}</span>
                            @else
                                <span class="badge badge-secondary">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->koordinat)
                                <span class="koordinat-link">{{ $item->koordinat }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($imagePaths->count() > 0)
                                @foreach($imagePaths as $imgPath)
                                    @php $fullPath = public_path('storage/' . $imgPath); @endphp
                                    @if(file_exists($fullPath))
                                        <img src="{{ $fullPath }}" class="lampiran-thumb">
                                    @endif
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-msg">Tidak ada data pembelian hari ini.</div>
    @endif

    {{-- ========== BIAYA ========== --}}
    <div class="section-title">
        Biaya ({{ $biayas->count() }} data)
    </div>
    @if($biayas->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="12%">Nomor</th>
                    <th width="10%">Tanggal / Jam</th>
                    <th width="6%">Jenis</th>
                    <th width="10%">Penerima</th>
                    <th width="10%" class="text-right">Total</th>
                    <th width="5%">Status</th>
                    <th width="14%">Koordinat</th>
                    <th width="18%">Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($biayas as $item)
                    @php
                        $paths = $item->lampiran_paths ?? [];
                        if ($item->lampiran_path && !in_array($item->lampiran_path, $paths)) {
                            $paths[] = $item->lampiran_path;
                        }
                        $imagePaths = collect($paths)->filter(function ($p) {
                            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $p);
                        });
                    @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item->custom_number }}</td>
                        <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}
                            {{ $item->created_at ? $item->created_at->format('H:i') : '' }}</td>
                        <td>
                            @if($item->jenis_biaya == 'masuk')
                                <span class="badge badge-info">Masuk</span>
                            @else
                                <span class="badge badge-warning">Keluar</span>
                            @endif
                        </td>
                        <td>{{ $item->penerima ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        <td>
                            @if($item->status == 'Approved')
                                <span class="badge badge-success">{{ $item->status }}</span>
                            @elseif($item->status == 'Pending')
                                <span class="badge badge-warning">{{ $item->status }}</span>
                            @else
                                <span class="badge badge-secondary">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->koordinat)
                                <span class="koordinat-link">{{ $item->koordinat }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($imagePaths->count() > 0)
                                @foreach($imagePaths as $imgPath)
                                    @php $fullPath = public_path('storage/' . $imgPath); @endphp
                                    @if(file_exists($fullPath))
                                        <img src="{{ $fullPath }}" class="lampiran-thumb">
                                    @endif
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-msg">Tidak ada data biaya hari ini.</div>
    @endif

    {{-- ========== KUNJUNGAN ========== --}}
    <div class="section-title">
        Kunjungan ({{ $kunjungans->count() }} data)
    </div>
    @if($kunjungans->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="12%">Nomor</th>
                    <th width="10%">Tanggal / Jam</th>
                    <th width="8%">Tujuan</th>
                    <th width="10%">Kontak</th>
                    <th width="5%">Status</th>
                    <th width="15%">Koordinat</th>
                    <th width="20%">Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($kunjungans as $item)
                    @php
                        $paths = $item->lampiran_paths ?? [];
                        if ($item->lampiran_path && !in_array($item->lampiran_path, $paths)) {
                            $paths[] = $item->lampiran_path;
                        }
                        $imagePaths = collect($paths)->filter(function ($p) {
                            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $p);
                        });
                    @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item->custom_number }}</td>
                        <td>{{ $item->tgl_kunjungan ? $item->tgl_kunjungan->format('d/m/Y') : '-' }}
                            {{ $item->created_at ? $item->created_at->format('H:i') : '' }}</td>
                        <td>{{ $item->tujuan ?? '-' }}</td>
                        <td>{{ $item->kontak->nama ?? '-' }}</td>
                        <td>
                            @if($item->status == 'Approved')
                                <span class="badge badge-success">{{ $item->status }}</span>
                            @elseif($item->status == 'Pending')
                                <span class="badge badge-warning">{{ $item->status }}</span>
                            @else
                                <span class="badge badge-secondary">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->koordinat)
                                <span class="koordinat-link">{{ $item->koordinat }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($imagePaths->count() > 0)
                                @foreach($imagePaths as $imgPath)
                                    @php $fullPath = public_path('storage/' . $imgPath); @endphp
                                    @if(file_exists($fullPath))
                                        <img src="{{ $fullPath }}" class="lampiran-thumb">
                                    @endif
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-msg">Tidak ada data kunjungan hari ini.</div>
    @endif

    {{-- ========== RINGKASAN ========== --}}
    <div class="summary-box">
        <h4>Ringkasan Hari Ini</h4>
        <table class="summary-table">
            <tr>
                <td class="label">Total Penjualan</td>
                <td>: {{ $penjualans->count() }} transaksi — Rp
                    {{ number_format($penjualans->sum('grand_total'), 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="label">Total Pembelian</td>
                <td>: {{ $pembelians->count() }} transaksi — Rp
                    {{ number_format($pembelians->sum('grand_total'), 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="label">Total Biaya</td>
                <td>: {{ $biayas->count() }} transaksi — Rp
                    {{ number_format($biayas->sum('grand_total'), 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="label">Total Kunjungan</td>
                <td>: {{ $kunjungans->count() }} kunjungan</td>
            </tr>
            <tr>
                <td class="label" style="border-top: 1px solid #ccc; padding-top: 5px;"><strong>Grand Total
                        Aktivitas</strong></td>
                <td style="border-top: 1px solid #ccc; padding-top: 5px;">
                    <strong>:
                        {{ $penjualans->count() + $pembelians->count() + $biayas->count() + $kunjungans->count() }}
                        aktivitas</strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Laporan Harian — {{ $salesName }} — {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }} — Dicetak
        {{ $generatedAt }}
    </div>
</body>

</html>