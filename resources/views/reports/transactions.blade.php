{{-- Ini adalah file HTML sederhana yang akan dibaca Laravel Excel --}}
{{-- View untuk export SEMUA transaksi (gabungan) --}}
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tipe</th>
            <th>No Transaksi</th>
            <th>Tgl Transaksi</th>
            <th>Jam</th>
            <th>Pembuat</th>
            <th>Approver</th>
            <th>Gudang</th>
            <th>Jenis Biaya</th>
            <th>Status</th>
            <th>Produk</th>
            <th>Harga Satuan</th>
            <th>Kuantitas</th>
            <th>Subtotal</th>
            <th>Pajak (%)</th>
            <th>Grand Total</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($transactions as $item)
            @php
                $tanggal = $item->tgl_transaksi ?? $item->tgl_kunjungan ?? null;
                $subtotal = ($item->tax_percentage ?? 0) > 0
                    ? ($item->grand_total ?? 0) / (1 + ($item->tax_percentage / 100))
                    : ($item->grand_total ?? 0);
                $items = collect();
                if (isset($item->type)) {
                    if ($item->type === 'Penjualan' && $item->relationLoaded('items')) {
                        $items = $item->items;
                    } elseif ($item->type === 'Pembelian' && $item->relationLoaded('items')) {
                        $items = $item->items;
                    } elseif ($item->type === 'Biaya' && $item->relationLoaded('items')) {
                        $items = $item->items;
                    } elseif ($item->type === 'Kunjungan' && $item->relationLoaded('items')) {
                        $items = $item->items;
                    }
                }
            @endphp
            @if($items->count() > 0)
                @foreach($items as $idx => $detail)
                    <tr>
                        @if($idx === 0)
                            <td>{{ $no++ }}</td>
                            <td>{{ $item->type }}</td>
                            <td>{{ $item->number }}</td>
                            <td>{{ $tanggal ? $tanggal->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->created_at ? $item->created_at->format('H:i') : '-' }}</td>
                            <td>{{ $item->user->name ?? '-' }}</td>
                            <td>{{ $item->approver->name ?? '-' }}</td>
                            <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                            <td>
                                @if($item->type === 'Biaya')
                                    {{ $item->jenis_biaya ? ucfirst($item->jenis_biaya) : '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->status }}</td>
                        @else
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        @endif
                        <td>{{ $detail->produk->nama_produk ?? ($detail->deskripsi ?? ($detail->kategori ?? '-')) }}</td>
                        <td>
                            @if(isset($item->type) && $item->type === 'Kunjungan')
                                -
                            @else
                                {{ $detail->harga_satuan ?? ($detail->jumlah ?? '-') }}
                            @endif
                        </td>
                        <td>{{ $detail->kuantitas ?? ($detail->jumlah ?? '-') }}</td>
                        @if($idx === 0)
                            <td>{{ round($subtotal) }}</td>
                            <td>{{ $item->tax_percentage ?? 0 }}</td>
                            <td>{{ $item->grand_total ?? '-' }}</td>
                        @else
                            <td></td>
                            <td></td>
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->type }}</td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $tanggal ? $tanggal->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->created_at ? $item->created_at->format('H:i') : '-' }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ $item->approver->name ?? '-' }}</td>
                    <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                    <td>
                        @if(isset($item->type) && $item->type === 'Biaya')
                            {{ $item->jenis_biaya ? ucfirst($item->jenis_biaya) : '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->status }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ round($subtotal) }}</td>
                    <td>{{ $item->tax_percentage ?? 0 }}</td>
                    <td>{{ $item->grand_total ?? '-' }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>