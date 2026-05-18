{{-- View untuk export PEMBAYARAN --}}
<table>
    <thead>
        <tr>
            <td colspan="12"><strong>Dibuat oleh: {{ $generatedBy ?? '-' }} | Tanggal cetak:
                    {{ $generatedAt ?? now()->format('d/m/Y H:i:s') }}</strong></td>
        </tr>
        <tr>
            <th>No</th>
            <th>No Pembayaran</th>
            <th>Tgl Pembayaran</th>
            <th>Gudang</th>
            <th>Pelanggan</th>
            <th>No Telepon</th>
            <th>Metode Pembayaran</th>
            <th>Jumlah Bayar</th>
            <th>Status</th>
            <th>Penjualan (Invoice)</th>
            <th>Pembuat</th>
            <th>Keterangan</th>
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
                <td>{{ $item->metode_pembayaran ?? '-' }}</td>
                <td>{{ format_rupiah($item->jumlah_bayar ?? 0) }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ optional($item->penjualan)->nomor ?? '-' }}</td>
                <td>{{ optional($item->user)->name ?? '-' }}</td>
                <td>{{ $item->keterangan ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- RINGKASAN --}}
<table>
    <tr>
        <td colspan="12"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>RINGKASAN</strong></td>
        <td colspan="9"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Pembayaran</strong></td>
        <td colspan="9">{{ $transactions->count() }} pembayaran</td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Jumlah Bayar</strong></td>
        <td colspan="9">{{ format_rupiah($transactions->sum('jumlah_bayar')) }}</td>
    </tr>
    @php $statusGroups = $transactions->groupBy('status'); @endphp
    @foreach($statusGroups as $status => $group)
        <tr>
            <td colspan="3"><strong>{{ $status }}</strong></td>
            <td colspan="9">{{ $group->count() }} pembayaran — {{ format_rupiah($group->sum('jumlah_bayar')) }}
            </td>
        </tr>
    @endforeach
</table>