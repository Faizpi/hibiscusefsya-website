{{-- View untuk export BIAYA --}}
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Tgl Transaksi</th>
            <th>Jam</th>
            <th>Jenis</th>
            <th>Bayar Dari</th>
            <th>Penerima</th>
            <th>Alamat Penagihan</th>
            <th>Cara Pembayaran</th>
            <th>Pembuat</th>
            <th>Approver</th>
            <th>Status</th>
            <th>Kategori Item</th>
            <th>Deskripsi Item</th>
            <th>Jumlah Item</th>
            <th>Memo</th>
            <th>Pajak (%)</th>
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
                            <td>{{ $item->created_at ? $item->created_at->format('H:i') : '-' }}</td>
                            <td>{{ $item->jenis_biaya ? ucfirst($item->jenis_biaya) : '-' }}</td>
                            <td>{{ $item->bayar_dari ?? '-' }}</td>
                            <td>{{ $item->penerima ?? '-' }}</td>
                            <td>{{ $item->alamat_penagihan ?? '-' }}</td>
                            <td>{{ $item->cara_pembayaran ?? '-' }}</td>
                            <td>{{ $item->user->name ?? '-' }}</td>
                            <td>{{ $item->approver->name ?? '-' }}</td>
                            <td>{{ $item->status }}</td>
                        @else
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        @endif
                        <td>{{ $detail->kategori ?? '-' }}</td>
                        <td>{{ $detail->deskripsi ?? '-' }}</td>
                        <td>{{ number_format($detail->jumlah ?? 0, 0, ',', '.') }}</td>
                        @if($idx === 0)
                            <td>{{ $item->memo ?? '-' }}</td>
                            <td>{{ $item->tax_percentage ?? 0 }}</td>
                            <td>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</td>
                        @else
                            <td></td><td></td><td></td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->created_at ? $item->created_at->format('H:i') : '-' }}</td>
                    <td>{{ $item->jenis_biaya ? ucfirst($item->jenis_biaya) : '-' }}</td>
                    <td>{{ $item->bayar_dari ?? '-' }}</td>
                    <td>{{ $item->penerima ?? '-' }}</td>
                    <td>{{ $item->alamat_penagihan ?? '-' }}</td>
                    <td>{{ $item->cara_pembayaran ?? '-' }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ $item->approver->name ?? '-' }}</td>
                    <td>{{ $item->status }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $item->memo ?? '-' }}</td>
                    <td>{{ $item->tax_percentage ?? 0 }}</td>
                    <td>{{ number_format($item->grand_total ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>