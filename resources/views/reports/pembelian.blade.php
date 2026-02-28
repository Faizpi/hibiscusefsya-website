{{-- View untuk export PEMBELIAN --}}
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Tgl Transaksi</th>
            <th>Jam</th>
            <th>Tgl Jatuh Tempo</th>
            <th>Staf Penyetuju</th>
            <th>Email Penyetuju</th>
            <th>Syarat Pembayaran</th>
            <th>Urgensi</th>
            <th>Gudang</th>
            <th>Tahun Anggaran</th>
            <th>Pembuat</th>
            <th>Approver</th>
            <th>Status</th>
            <th>Produk</th>
            <th>Harga Satuan</th>
            <th>Kuantitas</th>
            <th>Diskon Item</th>
            <th>Jumlah Baris</th>
            <th>Memo</th>
            <th>Diskon Akhir</th>
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
                            <td>{{ $item->tgl_jatuh_tempo ? \Carbon\Carbon::parse($item->tgl_jatuh_tempo)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->staf_penyetuju ?? '-' }}</td>
                            <td>{{ $item->email_penyetuju ?? '-' }}</td>
                            <td>{{ $item->syarat_pembayaran ?? '-' }}</td>
                            <td>{{ $item->urgensi ?? '-' }}</td>
                            <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                            <td>{{ $item->tahun_anggaran ?? '-' }}</td>
                            <td>{{ $item->user->name ?? '-' }}</td>
                            <td>{{ $item->approver->name ?? '-' }}</td>
                            <td>{{ $item->status }}</td>
                        @else
                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        @endif
                        <td>{{ ($detail->produk->item_code ?? '') }} - {{ $detail->produk->nama_produk ?? ($detail->deskripsi ?? '-') }}</td>
                        <td>{{ $detail->harga_satuan ?? 0 }}</td>
                        <td>{{ $detail->kuantitas ?? 0 }}</td>
                        <td>{{ $detail->diskon ?? 0 }}</td>
                        <td>{{ $detail->jumlah_baris ?? 0 }}</td>
                        @if($idx === 0)
                            <td>{{ $item->memo ?? '-' }}</td>
                            <td>{{ $item->diskon_akhir ?? 0 }}</td>
                            <td>{{ $item->tax_percentage ?? 0 }}</td>
                            <td>{{ $item->grand_total }}</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->created_at ? $item->created_at->format('H:i') : '-' }}</td>
                    <td>{{ $item->tgl_jatuh_tempo ? \Carbon\Carbon::parse($item->tgl_jatuh_tempo)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->staf_penyetuju ?? '-' }}</td>
                    <td>{{ $item->email_penyetuju ?? '-' }}</td>
                    <td>{{ $item->syarat_pembayaran ?? '-' }}</td>
                    <td>{{ $item->urgensi ?? '-' }}</td>
                    <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                    <td>{{ $item->tahun_anggaran ?? '-' }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ $item->approver->name ?? '-' }}</td>
                    <td>{{ $item->status }}</td>
                    <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                    <td>{{ $item->memo ?? '-' }}</td>
                    <td>{{ $item->diskon_akhir ?? 0 }}</td>
                    <td>{{ $item->tax_percentage ?? 0 }}</td>
                    <td>{{ $item->grand_total }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>