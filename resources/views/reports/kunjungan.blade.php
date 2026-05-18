{{-- View untuk export KUNJUNGAN --}}
<table>
    <thead>
        <tr>
            <td colspan="20"><strong>Dibuat oleh: {{ $generatedBy ?? '-' }} | Tanggal cetak:
                    {{ $generatedAt ?? now()->format('d/m/Y H:i:s') }}</strong></td>
        </tr>
        <tr>
            <th>No</th>
            <th>No Kunjungan</th>
            <th>Tgl Kunjungan</th>
            <th>Jam</th>
            <th>Tujuan</th>
            <th>Sales Nama</th>
            <th>No Telepon</th>
            <th>No Telepon Sales</th>
            <th>Sales Alamat</th>
            <th>Gudang</th>
            <th>Koordinat</th>
            <th>Pembuat</th>
            <th>Approver</th>
            <th>Status</th>
            <th>Produk</th>
            <th>Kuantitas</th>
            <th>Tipe Stok</th>
            <th>Batch</th>
            <th>Exp</th>
            <th>Memo/Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($transactions as $item)
            @if($item->items && $item->items->count() > 0)
                @foreach($item->items as $itemIdx => $itemDetail)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item->number }}</td>
                        <td>{{ $item->tgl_kunjungan->format('d/m/Y') }}</td>
                        <td>{{ $item->created_at->format('H:i') }}</td>
                        <td>{{ $item->tujuan }}</td>
                        <td>{{ $item->sales_nama ?? '-' }}</td>
                        <td>{{ $item->no_telp_kontak ?? '-' }}</td>
                        <td>{{ $item->sales_no_telepon ?? '-' }}</td>
                        <td>{{ $item->sales_alamat ?? '-' }}</td>
                        <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                        <td>{{ $item->koordinat ?? '-' }}</td>
                        <td>{{ $item->user->name ?? '-' }}</td>
                        <td>{{ $item->approver->name ?? '-' }}</td>
                        <td>{{ $item->status }}</td>
                        <td>{{ $itemDetail->produk->item_code ?? '-' }} - {{ $itemDetail->produk->nama_produk ?? '-' }}</td>
                        <td>{{ $itemDetail->jumlah }}</td>
                        <td>{{ $itemDetail->tipe_stok ?? ($item->tujuan === 'Promo Gratis' ? 'gratis' : ($item->tujuan === 'Promo Sample' ? 'sample' : '-')) }}</td>
                        <td>{{ $itemDetail->batch_number ?? '-' }}</td>
                        <td>{{ $itemDetail->expired_date ? (is_string($itemDetail->expired_date) ? \Carbon\Carbon::parse($itemDetail->expired_date)->format('d/m/Y') : $itemDetail->expired_date->format('d/m/Y')) : '-' }}</td>
                        <td>{{ $itemDetail->keterangan ?? ($itemIdx === 0 ? ($item->memo ?? '-') : '-') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->tgl_kunjungan->format('d/m/Y') }}</td>
                    <td>{{ $item->created_at->format('H:i') }}</td>
                    <td>{{ $item->tujuan }}</td>
                    <td>{{ $item->sales_nama ?? '-' }}</td>
                    <td>{{ $item->no_telp_kontak ?? '-' }}</td>
                    <td>{{ $item->sales_no_telepon ?? '-' }}</td>
                    <td>{{ $item->sales_alamat ?? '-' }}</td>
                    <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                    <td>{{ $item->koordinat ?? '-' }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ $item->approver->name ?? '-' }}</td>
                    <td>{{ $item->status }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $item->memo ?? '-' }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

{{-- RINGKASAN --}}
<table>
    <tr>
        <td colspan="20"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>RINGKASAN</strong></td>
        <td colspan="17"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Kunjungan</strong></td>
        <td colspan="17">{{ $transactions->count() }} kunjungan</td>
    </tr>
    @php
        $statusGroups = $transactions->groupBy('status');
    @endphp
    @foreach($statusGroups as $status => $group)
        <tr>
            <td colspan="3"><strong>{{ $status }}</strong></td>
            <td colspan="17">{{ $group->count() }} kunjungan</td>
        </tr>
    @endforeach
    @php
        $tujuanGroups = $transactions->groupBy('tujuan');
    @endphp
    @foreach($tujuanGroups as $tujuan => $group)
        <tr>
            <td colspan="3"><strong>{{ $tujuan }}</strong></td>
            <td colspan="17">{{ $group->count() }} kunjungan</td>
        </tr>
    @endforeach
</table>