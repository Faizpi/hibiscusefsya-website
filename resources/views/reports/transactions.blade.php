{{-- Ini adalah file HTML sederhana yang akan dibaca Laravel Excel --}}
<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Tipe</th>
            <th>Nomor</th>
            <th>Pembuat</th>
            <th>Gudang</th>
            <th>Status</th>
            <th>Subtotal</th>
            <th>Pajak (%)</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $item)
        @php
            // Hitung subtotal (GrandTotal - Pajak)
            $subtotal = $item->grand_total / (1 + ($item->tax_percentage / 100));
        @endphp
        <tr>
            <td>{{ $item->tgl_transaksi->format('Y-m-d') }}</td>
            <td>{{ $item->type }}</td>
            <td>{{ $item->number }}</td>
            <td>{{ $item->user->name }}</td>
            <td>{{ $item->gudang ? $item->gudang->nama_gudang : '-' }}</td>
            <td>{{ $item->status }}</td>
            <td>{{ $subtotal }}</td>
            <td>{{ $item->tax_percentage }}</td>
            <td>{{ $item->grand_total }}</td>
        </tr>
        @endforeach
    </tbody>
</table>