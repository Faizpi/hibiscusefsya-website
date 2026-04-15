{{-- View untuk export PEMBAYARAN --}}
<table>
    <thead>
        <tr>
            <td colspan="10"><strong>Dibuat oleh: {{ $generatedBy ?? '-' }} | Tanggal cetak:
                    {{ $generatedAt ?? now()->format('d/m/Y H:i:s') }}</strong></td>
        </tr>
        <tr>
            <th>No</th>
            <th>No Pembayaran</th>
            <th>Tgl Pembayaran</th>
            <th>Gudang</th>
            <th>Pelanggan</th>
            <th>No Telepon</th>
            <th>Jumlah Bayar</th>
            <th>Status</th>
            <th>Penjualan (Invoice)</th>
            <th>Lampiran</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($transactions as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $item->number }}</td>
                <td>{{ $item->tgl_pembayaran ? $item->tgl_pembayaran->format('d/m/Y') : '-' }}</td>
                <td>{{ optional($item->gudang)->nama_gudang ?? '-' }}</td>
                <td>{{ $item->display_contact_name ?? '-' }}</td>
                <td>{{ $item->no_telp_kontak ?? '-' }}</td>
                <td>{{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ optional($item->penjualan)->nomor ?? '-' }}</td>
                <td>
                    @php
                        $paths = $item->lampiran_paths ?? [];
                        if ($item->lampiran_path && !in_array($item->lampiran_path, $paths))
                            $paths[] = $item->lampiran_path;
                        $imagePaths = collect($paths)->filter(function ($p) {
                            return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $p);
                        });
                    @endphp
                    @if($imagePaths->count() > 0)
                        @foreach($imagePaths as $imgPath)
                            @php $fullPath = public_path('storage/' . $imgPath); @endphp
                            @if(file_exists($fullPath))
                                <img src="{{ $fullPath }}" style="max-width:60px; max-height:45px; border:1px solid #ccc; margin:1px;">
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

{{-- RINGKASAN --}}
<table>
    <tr>
        <td colspan="10"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>RINGKASAN</strong></td>
        <td colspan="7"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Pembayaran</strong></td>
        <td colspan="7">{{ $transactions->count() }} pembayaran</td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Jumlah Bayar</strong></td>
        <td colspan="7">{{ number_format($transactions->sum('jumlah_bayar'), 0, ',', '.') }}</td>
    </tr>
    @php $statusGroups = $transactions->groupBy('status'); @endphp
    @foreach($statusGroups as $status => $group)
        <tr>
            <td colspan="3"><strong>{{ $status }}</strong></td>
            <td colspan="7">{{ $group->count() }} pembayaran  Rp {{ number_format($group->sum('jumlah_bayar'), 0, ',', '.') }}</td>
        </tr>
    @endforeach
</table>
