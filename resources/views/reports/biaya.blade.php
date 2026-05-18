{{-- View untuk export BIAYA --}}
<table>
    <thead>
        <tr>
            <td colspan="22"><strong>Dibuat oleh: {{ $generatedBy ?? '-' }} | Tanggal cetak:
                    {{ $generatedAt ?? now()->format('d/m/Y H:i:s') }}</strong></td>
        </tr>
        <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Tgl Transaksi</th>
            <th>Jam</th>
            <th>Jenis</th>
            <th>Bayar Dari</th>
            <th>Penerima</th>
            <th>No Telepon</th>
            <th>Alamat Penagihan</th>
            <th>Cara Pembayaran</th>
            <th>Pembuat</th>
            <th>Gudang</th>
            <th>Approver</th>
            <th>Status</th>
            <th>Tag</th>
            <th>Koordinat</th>
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
                            <td>{{ $item->no_telp_kontak ?? '-' }}</td>
                            <td>{{ $item->alamat_penagihan ?? '-' }}</td>
                            <td>{{ $item->cara_pembayaran ?? '-' }}</td>
                            <td>{{ $item->user->name ?? '-' }}</td>
                            <td>{{ optional($item->gudang)->nama_gudang ?? '-' }}</td>
                            <td>{{ $item->approver->name ?? '-' }}</td>
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->tag ?? '-' }}</td>
                            <td>{{ $item->koordinat ?? '-' }}</td>
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
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        @endif
                        <td>{{ $detail->kategori ?? '-' }}</td>
                        <td>{{ $detail->deskripsi ?? '-' }}</td>
                        <td>{{ format_rupiah($detail->jumlah ?? 0) }}</td>
                        @if($idx === 0)
                            <td>{{ $item->memo ?? '-' }}</td>
                            <td>{{ $item->tax_percentage ?? 0 }}</td>
                            <td>{{ format_rupiah($item->grand_total ?? 0) }}</td>
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
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->tgl_transaksi ? $item->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->created_at ? $item->created_at->format('H:i') : '-' }}</td>
                    <td>{{ $item->jenis_biaya ? ucfirst($item->jenis_biaya) : '-' }}</td>
                    <td>{{ $item->bayar_dari ?? '-' }}</td>
                    <td>{{ $item->penerima ?? '-' }}</td>
                    <td>{{ $item->no_telp_kontak ?? '-' }}</td>
                    <td>{{ $item->alamat_penagihan ?? '-' }}</td>
                    <td>{{ $item->cara_pembayaran ?? '-' }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ optional($item->gudang)->nama_gudang ?? '-' }}</td>
                    <td>{{ $item->approver->name ?? '-' }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->tag ?? '-' }}</td>
                    <td>{{ $item->koordinat ?? '-' }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $item->memo ?? '-' }}</td>
                    <td>{{ $item->tax_percentage ?? 0 }}</td>
                    <td>{{ format_rupiah($item->grand_total ?? 0) }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

{{-- RINGKASAN --}}
<table>
    <tr>
        <td colspan="22"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>RINGKASAN</strong></td>
        <td colspan="19"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Transaksi</strong></td>
        <td colspan="19">{{ $transactions->count() }} transaksi</td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Grand Total</strong></td>
        <td colspan="19">{{ format_rupiah($transactions->sum('grand_total')) }}</td>
    </tr>
    @php
        $statusGroups = $transactions->groupBy('status');
    @endphp
    @foreach($statusGroups as $status => $group)
        <tr>
            <td colspan="3"><strong>{{ $status }}</strong></td>
            <td colspan="19">{{ $group->count() }} transaksi — {{ format_rupiah($group->sum('grand_total')) }}
            </td>
        </tr>
    @endforeach
    @php
        $jenisGroups = $transactions->groupBy('jenis_biaya');
    @endphp
    @foreach($jenisGroups as $jenis => $group)
        <tr>
            <td colspan="3"><strong>{{ ucfirst($jenis) }}</strong></td>
            <td colspan="19">{{ $group->count() }} transaksi — {{ format_rupiah($group->sum('grand_total')) }}
            </td>
        </tr>
    @endforeach
</table>