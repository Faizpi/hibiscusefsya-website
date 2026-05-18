{{-- Ini adalah file HTML sederhana yang akan dibaca Laravel Excel --}}
{{-- View untuk export SEMUA transaksi (gabungan) --}}
<table>
    <thead>
        <tr>
            <td colspan="40"><strong>Dibuat oleh: {{ $generatedBy ?? '-' }} | Tanggal cetak:
                    {{ $generatedAt ?? now()->format('d/m/Y H:i:s') }}</strong></td>
        </tr>
        <tr>
            <th>No</th>
            <th>Tipe</th>
            <th>No Transaksi</th>
            <th>Tgl Transaksi</th>
            <th>Jam</th>
            <th>Tgl Jatuh Tempo</th>
            <th>Pembuat</th>
            <th>No Telp Sales</th>
            <th>Approver</th>
            <th>Gudang</th>
            <th>Status</th>
            <th>Pelanggan/Penerima</th>
            <th>No Telepon</th>
            <th>Alamat</th>
            <th>Syarat/Metode Pembayaran</th>
            <th>Tipe Harga</th>
            <th>Urgensi</th>
            <th>Tahun Anggaran</th>
            <th>Staf Penyetuju</th>
            <th>Email Penyetuju</th>
            <th>Jenis Biaya</th>
            <th>Bayar Dari</th>
            <th>Tujuan</th>
            <th>Tag</th>
            <th>Koordinat</th>
            <th>Produk/Kategori</th>
            <th>Harga Satuan</th>
            <th>Kuantitas/Jumlah</th>
            <th>Diskon Item (%)</th>
            <th>Diskon Nominal</th>
            <th>Batch</th>
            <th>Exp</th>
            <th>Tipe Stok</th>
            <th>Deskripsi/Keterangan Item</th>
            <th>Jumlah Baris</th>
            <th>No Referensi/Invoice</th>
            <th>Memo/Keterangan</th>
            <th>Diskon Akhir</th>
            <th>Pajak (%)</th>
            <th>Grand Total/Jumlah Bayar</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($transactions as $item)
            @php
                $type = $item->type ?? '-';
                $tanggal = $item->tgl_transaksi ?? $item->tgl_kunjungan ?? $item->tgl_pembayaran ?? null;
                $items = collect();
                if (isset($item->type) && $item->type !== 'Pembayaran' && $item->relationLoaded('items')) {
                    $items = $item->items;
                }
                $paymentLabel = $item->syarat_pembayaran ?? $item->metode_pembayaran ?? $item->cara_pembayaran ?? '-';
                $amount = $item->jumlah_bayar ?? $item->grand_total ?? '-';
            @endphp
            @if($items->count() > 0)
                @foreach($items as $idx => $detail)
                    <tr>
                        @if($idx === 0)
                            <td>{{ $no++ }}</td>
                            <td>{{ $type }}</td>
                            <td>{{ $item->number }}</td>
                            <td>{{ $tanggal ? $tanggal->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->created_at ? $item->created_at->format('H:i') : '-' }}</td>
                            <td>{{ $item->tgl_jatuh_tempo ? \Carbon\Carbon::parse($item->tgl_jatuh_tempo)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->user->name ?? '-' }}</td>
                            <td>{{ $item->user->no_telp ?? '-' }}</td>
                            <td>{{ $item->approver->name ?? '-' }}</td>
                            <td>{{ optional($item->gudang)->nama_gudang ?? '-' }}</td>
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->display_contact_name ?? $item->pelanggan ?? $item->penerima ?? '-' }}</td>
                            <td>{{ $item->no_telp_kontak ?? $item->no_telepon ?? '-' }}</td>
                            <td>{{ $item->alamat_penagihan ?? $item->sales_alamat ?? '-' }}</td>
                            <td>{{ $paymentLabel }}</td>
                            <td>{{ $item->tipe_harga ?? '-' }}</td>
                            <td>{{ $item->urgensi ?? '-' }}</td>
                            <td>{{ $item->tahun_anggaran ?? '-' }}</td>
                            <td>{{ $item->staf_penyetuju ?? '-' }}</td>
                            <td>{{ $item->email_penyetuju ?? '-' }}</td>
                            <td>{{ $item->jenis_biaya ? ucfirst($item->jenis_biaya) : '-' }}</td>
                            <td>{{ $item->bayar_dari ?? '-' }}</td>
                            <td>{{ $item->tujuan ?? '-' }}</td>
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
                        <td>{{ $detail->produk->nama_produk ?? $detail->produk->item_code ?? $detail->kategori ?? $detail->deskripsi ?? '-' }}</td>
                        <td>{{ $type === 'Kunjungan' ? '-' : ($detail->harga_satuan ?? ($type === 'Biaya' ? ($detail->jumlah ?? '-') : '-')) }}</td>
                        <td>{{ $detail->kuantitas ?? $detail->jumlah ?? '-' }}</td>
                        <td>{{ $detail->diskon ?? '-' }}</td>
                        <td>{{ $detail->diskon_nominal ?? '-' }}</td>
                        <td>{{ $detail->batch_number ?? '-' }}</td>
                        <td>{{ $detail->expired_date ? (is_string($detail->expired_date) ? \Carbon\Carbon::parse($detail->expired_date)->format('d/m/Y') : $detail->expired_date->format('d/m/Y')) : '-' }}</td>
                        <td>{{ $detail->tipe_stok ?? ($item->tujuan === 'Promo Gratis' ? 'gratis' : ($item->tujuan === 'Promo Sample' ? 'sample' : '-')) }}</td>
                        <td>{{ $detail->deskripsi ?? $detail->keterangan ?? '-' }}</td>
                        <td>{{ $detail->jumlah_baris ?? '-' }}</td>
                        @if($idx === 0)
                            <td>{{ $item->no_referensi ?? optional($item->penjualan)->nomor ?? '-' }}</td>
                            <td>{{ $item->memo ?? $item->keterangan ?? '-' }}</td>
                            <td>{{ $item->diskon_akhir ?? '-' }}</td>
                            <td>{{ $item->tax_percentage ?? '-' }}</td>
                            <td>{{ $amount }}</td>
                        @else
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
                    <td>{{ $type }}</td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $tanggal ? $tanggal->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->created_at ? $item->created_at->format('H:i') : '-' }}</td>
                    <td>{{ $item->tgl_jatuh_tempo ? \Carbon\Carbon::parse($item->tgl_jatuh_tempo)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ $item->user->no_telp ?? '-' }}</td>
                    <td>{{ $item->approver->name ?? '-' }}</td>
                    <td>{{ optional($item->gudang)->nama_gudang ?? '-' }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->display_contact_name ?? $item->pelanggan ?? $item->penerima ?? '-' }}</td>
                    <td>{{ $item->no_telp_kontak ?? $item->no_telepon ?? '-' }}</td>
                    <td>{{ $item->alamat_penagihan ?? $item->sales_alamat ?? '-' }}</td>
                    <td>{{ $paymentLabel }}</td>
                    <td>{{ $item->tipe_harga ?? '-' }}</td>
                    <td>{{ $item->urgensi ?? '-' }}</td>
                    <td>{{ $item->tahun_anggaran ?? '-' }}</td>
                    <td>{{ $item->staf_penyetuju ?? '-' }}</td>
                    <td>{{ $item->email_penyetuju ?? '-' }}</td>
                    <td>{{ $item->jenis_biaya ? ucfirst($item->jenis_biaya) : '-' }}</td>
                    <td>{{ $item->bayar_dari ?? '-' }}</td>
                    <td>{{ $item->tujuan ?? '-' }}</td>
                    <td>{{ $item->tag ?? '-' }}</td>
                    <td>{{ $item->koordinat ?? '-' }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $item->no_referensi ?? optional($item->penjualan)->nomor ?? '-' }}</td>
                    <td>{{ $item->memo ?? $item->keterangan ?? '-' }}</td>
                    <td>{{ $item->diskon_akhir ?? '-' }}</td>
                    <td>{{ $item->tax_percentage ?? '-' }}</td>
                    <td>{{ $amount }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

{{-- RINGKASAN --}}
<table>
    <tr>
        <td colspan="40"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>RINGKASAN</strong></td>
        <td colspan="37"></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Transaksi</strong></td>
        <td colspan="37">{{ $transactions->count() }} transaksi</td>
    </tr>
    <tr>
        <td colspan="3"><strong>Total Grand Total</strong></td>
        <td colspan="37">{{ format_rupiah($transactions->sum('grand_total')) }}</td>
    </tr>
    @php
        $typeGroups = $transactions->groupBy('type');
    @endphp
    @foreach($typeGroups as $type => $group)
        <tr>
            <td colspan="3"><strong>{{ $type }}</strong></td>
            <td colspan="37">{{ $group->count() }} transaksi — {{ format_rupiah($group->sum('grand_total')) }}
            </td>
        </tr>
    @endforeach
    @php
        $statusGroups = $transactions->groupBy('status');
    @endphp
    @foreach($statusGroups as $status => $group)
        <tr>
            <td colspan="3"><strong>{{ $status }}</strong></td>
            <td colspan="37">{{ $group->count() }} transaksi — {{ format_rupiah($group->sum('grand_total')) }}
            </td>
        </tr>
    @endforeach
</table>
