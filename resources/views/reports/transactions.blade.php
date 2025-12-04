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
            <th>Status</th>
            <th>Subtotal</th>
            <th>Pajak (%)</th>
            <th>Grand Total</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($transactions as $item)
            @php
                $subtotal = $item->tax_percentage > 0
                    ? $item->grand_total / (1 + ($item->tax_percentage / 100))
                    : $item->grand_total;
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $item->type }}</td>
                <td>{{ $item->number }}</td>
                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}</td>
                <td>{{ $item->created_at->format('H:i') }}</td>
                <td>{{ $item->user->name ?? '-' }}</td>
                <td>{{ $item->approver->name ?? '-' }}</td>
                <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ round($subtotal) }}</td>
                <td>{{ $item->tax_percentage ?? 0 }}</td>
                <td>{{ $item->grand_total }}</td>
            </tr>
        @endforeach
    </tbody>
</table>