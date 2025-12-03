{{-- View untuk export BIAYA --}}
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Tgl Transaksi</th>
            <th>Bayar Dari</th>
            <th>Penerima</th>
            <th>Alamat Penagihan</th>
            <th>Cara Pembayaran</th>
            <th>Pembuat</th>
            <th>Approver</th>
            <th>Status</th>
            <th>Memo</th>
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
                <td>{{ $item->bayar_dari ?? '-' }}</td>
                <td>{{ $item->penerima ?? '-' }}</td>
                <td>{{ $item->alamat_penagihan ?? '-' }}</td>
                <td>{{ $item->cara_pembayaran ?? '-' }}</td>
                <td>{{ $item->user->name ?? '-' }}</td>
                <td>{{ $item->approver->name ?? '-' }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->memo ?? '-' }}</td>
                <td>{{ $item->tax_percentage ?? 0 }}</td>
                <td>{{ $item->grand_total }}</td>
            </tr>
        @endforeach
    </tbody>
</table>