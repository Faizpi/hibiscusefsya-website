{{-- View untuk export PENJUALAN --}}
<table>
    <thead>
        <tr>
            <td colspan="32"><strong>Dibuat oleh: {{ $generatedBy ?? '-' }} | Tanggal cetak:
                    {{ $generatedAt ?? now()->format('d/m/Y H:i:s') }}</strong></td>
        </tr>
        <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Tgl Transaksi</th>
            <th>Jam</th>
            <th>Tgl Jatuh Tempo</th>
            <th>Pelanggan</th>
            <th>No Telepon</th>
            <th>Nomor Telepon</th>
            <th>Alamat Penagihan</th>
            <th>Syarat Pembayaran</th>
            <th>Tipe Harga</th>
            <th>Gudang</th>
            <th>Pembuat</th>
            <th>No Telp Sales</th>
            <th>Approver</th>
            <th>Status</th>
            <th>Produk</th>
            <th>Harga Satuan</th>
            <th>Kuantitas</th>
            <th>Diskon Item (%)</th>
            <th>Diskon Nominal</th>
            <th>Batch</th>
            <th>Exp</th>
            <th>Deskripsi Item</th>
            <th>Jumlah Baris</th>
            <th>No Referensi</th>
            <th>Tag</th>
            <th>Koordinat</th>
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
                            <td>{{ $item->pelanggan ?? '-' }}</td>
                            <td>{{ $item->no_telp_kontak ?? '-' }}</td>
                            <td>{{ $item->no_telepon ?? '-' }}</td>
                            <td>{{ $item->alamat_penagihan ?? '-' }}</td>
                            <td>{{ $item->syarat_pembayaran ?? '-' }}</td>
                            <td>{{ $item->tipe_harga ?? '-' }}</td>
                            <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                            <td>{{ $item->user->name ?? '-' }}</td>
                            <td>{{ $item->user->no_telp ?? '-' }}</td>
                            <td>{{ $item->approver->name ?? '-' }}</td>
                            <td>{{ $item->status }}</td>
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
                        <td>{{ ($detail->produk->item_code ?? '') }} -
                            {{ $detail->produk->nama_produk ?? ($detail->deskripsi ?? '-') }}
                        </td>
                        <td>{{ $detail->harga_satuan ?? 0 }}</td>
                        <td>{{ $detail->kuantitas ?? 0 }}</td>
                        <td>{{ $detail->diskon ?? 0 }}</td>
                        <td>{{ $detail->diskon_nominal ?? 0 }}</td>
                        <td>{{ $detail->batch_number ?? '-' }}</td>
                        <td>{{ $detail->expired_date ? $detail->expired_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $detail->deskripsi ?? '-' }}</td>
                        <td>{{ $detail->jumlah_baris ?? 0 }}</td>
                        @if($idx === 0)
                            <td>{{ $item->no_referensi ?? '-' }}</td>
                            <td>{{ $item->tag ?? '-' }}</td>
                            <td>{{ $item->koordinat ?? '-' }}</td>
                            <td>{{ $item->memo ?? '-' }}</td>
                            <td>{{ $item->diskon_akhir ?? 0 }}</td>
                            <td>{{ $item->tax_percentage ?? 0 }}</td>
                            <td>{{ $item->grand_total }}</td>
                        @else
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
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
                    <td>{{ $item->tgl_jatuh_tempo ? \Carbon\Carbon::parse($item->tgl_jatuh_tempo)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->pelanggan ?? '-' }}</td>
                    <td>{{ $item->no_telp_kontak ?? '-' }}</td>
                    <td>{{ $item->no_telepon ?? '-' }}</td>
                    <td>{{ $item->alamat_penagihan ?? '-' }}</td>
                    <td>{{ $item->syarat_pembayaran ?? '-' }}</td>
                    <td>{{ $item->tipe_harga ?? '-' }}</td>
                    <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ $item->user->no_telp ?? '-' }}</td>
                    <td>{{ $item->approver->name ?? '-' }}</td>
                    <td>{{ $item->status }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $item->no_referensi ?? '-' }}</td>
                    <td>{{ $item->tag ?? '-' }}</td>
                    <td>{{ $item->koordinat ?? '-' }}</td>
                    <td>{{ $item->memo ?? '-' }}</td>
                    <td>{{ $item->diskon_akhir ?? 0 }}</td>
                    <td>{{ $item->tax_percentage ?? 0 }}</td>
                    <td>{{ $item->grand_total }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

{{-- RINGKASAN --}}
<table>
    <tr>
        <td colspan="32"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>RINGKASAN</strong></td>
        <td colspan="29"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Transaksi</strong></td>
        <td colspan="29">{{ $transactions->count() }} transaksi</td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Grand Total</strong></td>
        <td colspan="29">{{ format_rupiah($transactions->sum('grand_total')) }}</td>
    </tr>
    @php
        $statusGroups = $transactions->groupBy('status');
    @endphp
    @foreach($statusGroups as $status => $group)
        <tr>
            <td colspan="3"><strong>{{ $status }}</strong></td>
            <td colspan="29">{{ $group->count() }} transaksi — {{ format_rupiah($group->sum('grand_total')) }}
            </td>
        </tr>
    @endforeach
</table>