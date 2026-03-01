<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan {{ ucfirst($exportType) }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #333; }
        h2 { color: #1a56db; margin-bottom: 5px; }
        .meta { font-size: 8px; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #1a56db; color: #fff; padding: 5px 4px; text-align: left; font-size: 8px; }
        td { border: 1px solid #ddd; padding: 4px; font-size: 8px; vertical-align: top; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 3px; color: #fff; font-size: 7px; }
        .badge-success { background: #0e9f6e; }
        .badge-warning { background: #c27803; color: #fff; }
        .badge-secondary { background: #6b7280; }
        .lampiran-thumb { max-width: 60px; max-height: 45px; border: 1px solid #ccc; margin: 1px; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 7px; color: #999; border-top: 1px solid #ddd; padding-top: 3px; }
    </style>
</head>
<body>
    <h2>Laporan {{ $exportType === 'all' ? 'Semua Transaksi' : ucfirst($exportType) }}</h2>
    <div class="meta">
        Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        | Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>

    @if($exportType === 'kunjungan')
        {{-- KUNJUNGAN --}}
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="9%">No Kunjungan</th>
                    <th width="7%">Tanggal</th>
                    <th width="7%">Tujuan</th>
                    <th width="8%">Sales</th>
                    <th width="8%">Gudang</th>
                    <th width="6%">Status</th>
                    <th width="12%">Produk</th>
                    <th width="4%">Qty</th>
                    <th width="12%">Memo</th>
                    <th width="15%">Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($transactions as $item)
                    @php
                        $paths = $item->lampiran_paths ?? [];
                        if ($item->lampiran_path && !in_array($item->lampiran_path, $paths)) {
                            $paths[] = $item->lampiran_path;
                        }
                        $imagePaths = collect($paths)->filter(function($p) {
                            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $p);
                        });
                    @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item->number }}</td>
                        <td>{{ $item->tgl_kunjungan ? $item->tgl_kunjungan->format('d/m/Y') : '-' }}</td>
                        <td>{{ $item->tujuan ?? '-' }}</td>
                        <td>{{ $item->sales_nama ?? ($item->user->name ?? '-') }}</td>
                        <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
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
                            @if($item->items && $item->items->count() > 0)
                                @foreach($item->items as $detail)
                                    {{ $detail->produk->nama_produk ?? '-' }} ({{ $detail->jumlah }})<br>
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->items && $item->items->count() > 0)
                                {{ $item->items->sum('jumlah') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $item->memo ?? '-' }}</td>
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

    @elseif($exportType === 'penjualan')
        {{-- PENJUALAN --}}
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="9%">No Transaksi</th>
                    <th width="7%">Tanggal</th>
                    <th width="9%">Pelanggan</th>
                    <th width="7%">Gudang</th>
                    <th width="5%">Status</th>
                    <th width="13%">Produk</th>
                    <th width="7%">Harga</th>
                    <th width="4%">Qty</th>
                    <th width="4%">Diskon</th>
                    <th width="7%">Subtotal</th>
                    <th width="4%">Pajak</th>
                    <th width="8%">Grand Total</th>
                    <th width="10%">Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($transactions as $item)
                    @php
                        $paths = $item->lampiran_paths ?? [];
                        if ($item->lampiran_path && !in_array($item->lampiran_path, $paths)) $paths[] = $item->lampiran_path;
                        $imagePaths = collect($paths)->filter(function($p) {
                            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $p);
                        });
                    @endphp
                    @if($item->items && $item->items->count() > 0)
                        @foreach($item->items as $idx => $detail)
                            <tr>
                                @if($idx === 0)
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $item->number }}</td>
                                    <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->pelanggan ?? '-' }}</td>
                                    <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                                    <td>{{ $item->status }}</td>
                                @else
                                    <td></td><td></td><td></td><td></td><td></td><td></td>
                                @endif
                                <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                <td class="text-right">{{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $detail->kuantitas ?? 0 }}</td>
                                <td class="text-right">{{ $detail->diskon ?? 0 }}%</td>
                                @if($idx === 0)
                                    <td class="text-right">{{ number_format(($item->grand_total ?? 0) / (1 + (($item->tax_percentage ?? 0) / 100)), 0, ',', '.') }}</td>
                                    <td>{{ $item->tax_percentage ?? 0 }}%</td>
                                    <td class="text-right"><strong>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</strong></td>
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
                                @else
                                    <td></td><td></td><td></td><td></td><td></td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $item->number }}</td>
                            <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->pelanggan ?? '-' }}</td>
                            <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                            <td>{{ $item->status }}</td>
                            <td>-</td><td>-</td><td>-</td><td>-</td>
                            <td class="text-right">{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $item->tax_percentage ?? 0 }}%</td>
                            <td class="text-right"><strong>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</strong></td>
                            <td>-</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

    @elseif($exportType === 'pembelian')
        {{-- PEMBELIAN --}}
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="10%">No Transaksi</th>
                    <th width="7%">Tanggal</th>
                    <th width="8%">Gudang</th>
                    <th width="6%">Status</th>
                    <th width="15%">Produk</th>
                    <th width="7%">Harga</th>
                    <th width="5%">Qty</th>
                    <th width="8%">Jumlah Baris</th>
                    <th width="5%">Pajak</th>
                    <th width="8%">Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($transactions as $item)
                    @if($item->items && $item->items->count() > 0)
                        @foreach($item->items as $idx => $detail)
                            <tr>
                                @if($idx === 0)
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $item->number }}</td>
                                    <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                                    <td>{{ $item->status }}</td>
                                @else
                                    <td></td><td></td><td></td><td></td><td></td>
                                @endif
                                <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                <td class="text-right">{{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $detail->kuantitas ?? 0 }}</td>
                                <td class="text-right">{{ number_format($detail->jumlah_baris ?? 0, 0, ',', '.') }}</td>
                                @if($idx === 0)
                                    <td>{{ $item->tax_percentage ?? 0 }}%</td>
                                    <td class="text-right"><strong>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</strong></td>
                                @else
                                    <td></td><td></td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $item->number }}</td>
                            <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                            <td>{{ $item->status }}</td>
                            <td>-</td><td>-</td><td>-</td><td>-</td>
                            <td>{{ $item->tax_percentage ?? 0 }}%</td>
                            <td class="text-right"><strong>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</strong></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

    @elseif($exportType === 'biaya')
        {{-- BIAYA --}}
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Transaksi</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Penerima</th>
                    <th>Gudang</th>
                    <th>Status</th>
                    <th>Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah</th>
                    <th>Pajak</th>
                    <th>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($transactions as $item)
                    @if($item->items && $item->items->count() > 0)
                        @foreach($item->items as $idx => $detail)
                            <tr>
                                @if($idx === 0)
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $item->number }}</td>
                                    <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                                    <td>{{ ucfirst($item->jenis_biaya ?? '-') }}</td>
                                    <td>{{ $item->penerima ?? '-' }}</td>
                                    <td>{{ optional($item->gudang)->nama_gudang ?? '-' }}</td>
                                    <td>{{ $item->status }}</td>
                                @else
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                @endif
                                <td>{{ $detail->kategori ?? '-' }}</td>
                                <td>{{ $detail->deskripsi ?? '-' }}</td>
                                <td class="text-right">{{ number_format($detail->jumlah ?? 0, 0, ',', '.') }}</td>
                                @if($idx === 0)
                                    <td>{{ $item->tax_percentage ?? 0 }}%</td>
                                    <td class="text-right"><strong>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</strong></td>
                                @else
                                    <td></td><td></td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $item->number }}</td>
                            <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                            <td>{{ ucfirst($item->jenis_biaya ?? '-') }}</td>
                            <td>{{ $item->penerima ?? '-' }}</td>
                            <td>{{ optional($item->gudang)->nama_gudang ?? '-' }}</td>
                            <td>{{ $item->status }}</td>
                            <td>-</td><td>-</td><td>-</td>
                            <td>{{ $item->tax_percentage ?? 0 }}%</td>
                            <td class="text-right"><strong>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</strong></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

    @else
        {{-- ALL TRANSACTIONS --}}
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tipe</th>
                    <th>No Transaksi</th>
                    <th>Tanggal</th>
                    <th>Pembuat</th>
                    <th>Gudang</th>
                    <th>Status</th>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Grand Total</th>
                    <th>Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($transactions as $item)
                    @php
                        $tanggal = $item->tgl_transaksi ?? $item->tgl_kunjungan ?? null;
                        $items = collect();
                        if ($item->relationLoaded('items')) $items = $item->items;
                        $paths = [];
                        if (isset($item->lampiran_paths) && is_array($item->lampiran_paths)) $paths = $item->lampiran_paths;
                        if ($item->lampiran_path && !in_array($item->lampiran_path, $paths)) $paths[] = $item->lampiran_path;
                        $imagePaths = collect($paths)->filter(function($p) {
                            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $p);
                        });
                    @endphp
                    @if($items->count() > 0)
                        @foreach($items as $idx => $detail)
                            <tr>
                                @if($idx === 0)
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $item->type }}</td>
                                    <td>{{ $item->number }}</td>
                                    <td>{{ $tanggal ? $tanggal->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->user->name ?? '-' }}</td>
                                    <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                                    <td>{{ $item->status }}</td>
                                @else
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                @endif
                                <td>{{ $detail->produk->nama_produk ?? ($detail->deskripsi ?? ($detail->kategori ?? '-')) }}</td>
                                <td class="text-right">{{ number_format($detail->harga_satuan ?? ($detail->jumlah ?? 0), 0, ',', '.') }}</td>
                                <td class="text-center">{{ $detail->kuantitas ?? ($detail->jumlah ?? '-') }}</td>
                                @if($idx === 0)
                                    <td class="text-right"><strong>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</strong></td>
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
                                @else
                                    <td></td><td></td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $item->type }}</td>
                            <td>{{ $item->number }}</td>
                            <td>{{ $tanggal ? $tanggal->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->user->name ?? '-' }}</td>
                            <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                            <td>{{ $item->status }}</td>
                            <td>-</td><td>-</td><td>-</td>
                            <td class="text-right"><strong>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</strong></td>
                            <td>-</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Hibiscus Efsya - Laporan dicetak otomatis pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
