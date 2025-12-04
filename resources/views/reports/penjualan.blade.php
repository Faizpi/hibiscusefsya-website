{{-- View untuk export PENJUALAN --}}
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Tgl Transaksi</th>
            <th>Jam</th>
            <th>Tgl Jatuh Tempo</th>
            <th>Pelanggan</th>
            <th>Email</th>
            <th>Alamat Penagihan</th>
            <th>Syarat Pembayaran</th>
            <th>Gudang</th>
            <th>Pembuat</th>
            <th>Approver</th>
            <th>Status</th>
            <th>No Referensi</th>
            <th>Memo</th>
            <th>Diskon Akhir</th>
            <th>Pajak (%)</th>
            <th>Grand Total</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($transactions as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $item->number }}</td>
                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}</td>
                <td>{{ $item->created_at->format('H:i') }}</td>
                <td>{{ $item->tgl_jatuh_tempo ? \Carbon\Carbon::parse($item->tgl_jatuh_tempo)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->pelanggan ?? '-' }}</td>
                <td>{{ $item->email ?? '-' }}</td>
                <td>{{ $item->alamat_penagihan ?? '-' }}</td>
                <td>{{ $item->syarat_pembayaran ?? '-' }}</td>
                <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                <td>{{ $item->user->name ?? '-' }}</td>
                <td>{{ $item->approver->name ?? '-' }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->no_referensi ?? '-' }}</td>
                <td>{{ $item->memo ?? '-' }}</td>
                <td>{{ $item->diskon_akhir ?? 0 }}</td>
                <td>{{ $item->tax_percentage ?? 0 }}</td>
                <td>{{ $item->grand_total }}</td>
            </tr>
        @endforeach
    </tbody>
</table>