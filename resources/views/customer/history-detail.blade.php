@extends('customer.layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
    <div style="margin-bottom: 24px;">
        <a href="{{ route('customer.history') }}" class="btn btn-outline btn-sm" style="margin-bottom: 16px;">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <h4 style="font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0;">
                {{ $penjualan->number }}
            </h4>
            @if($penjualan->status == 'Lunas')
                <span class="badge badge-success">Lunas</span>
            @elseif($penjualan->status == 'Approved')
                <span class="badge badge-info">Approved</span>
            @elseif($penjualan->status == 'Pending')
                <span class="badge badge-warning">Pending</span>
            @else
                <span class="badge badge-secondary">{{ $penjualan->status }}</span>
            @endif
        </div>
        <p style="font-size: 0.85rem; color: #6b7280; margin: 4px 0 0;">
            {{ $penjualan->tgl_transaksi ? $penjualan->tgl_transaksi->format('d F Y') : '-' }}
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 340px 1fr; gap: 20px;">
        {{-- Info Transaksi --}}
        <div class="card" style="align-self: start;">
            <div class="card-header"><i class="fas fa-info-circle"></i> Info Transaksi</div>
            <div class="card-body" style="padding: 0;">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 10px 20px; color: #6b7280; font-size: 0.8rem; width: 40%; border-bottom: 1px solid #f3f4f6;">Invoice</td>
                        <td style="padding: 10px 20px; font-size: 0.8rem; font-weight: 600; border-bottom: 1px solid #f3f4f6;">{{ $penjualan->number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 20px; color: #6b7280; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6;">Tanggal</td>
                        <td style="padding: 10px 20px; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6;">{{ $penjualan->tgl_transaksi ? $penjualan->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 20px; color: #6b7280; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6;">Gudang</td>
                        <td style="padding: 10px 20px; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6;">{{ $penjualan->gudang->nama_gudang ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 20px; color: #6b7280; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6;">Sales</td>
                        <td style="padding: 10px 20px; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6;">{{ $penjualan->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 20px; color: #6b7280; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6;">Pajak</td>
                        <td style="padding: 10px 20px; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6;">{{ $penjualan->tax_percentage ?? 0 }}%</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 20px; color: #6b7280; font-size: 0.8rem;">Memo</td>
                        <td style="padding: 10px 20px; font-size: 0.8rem;">{{ $penjualan->memo ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Detail Produk --}}
        <div class="card">
            <div class="card-header"><i class="fas fa-boxes"></i> Detail Produk</div>

            {{-- Desktop --}}
            <div class="table-wrap detail-desktop">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center">Qty</th>
                            <th>Satuan</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Diskon</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
                        @forelse($penjualan->items as $item)
                            @php
                                $lineTotal = $item->jumlah_baris ?? (($item->harga_satuan ?? 0) * ($item->kuantitas ?? 0));
                                $subtotal += $lineTotal;
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-bold" style="color: #111827;">{{ $item->produk->nama_produk ?? $item->deskripsi ?? '-' }}</div>
                                    @if($item->produk && $item->produk->item_code)
                                        <div style="font-size: 0.72rem; color: #9ca3af; margin-top: 1px;">{{ $item->produk->item_code }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->kuantitas ?? 0 }}</td>
                                <td>{{ $item->unit ?? ($item->produk->satuan ?? 'Pcs') }}</td>
                                <td class="text-right">Rp {{ number_format($item->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right">
                                    @if(($item->diskon ?? 0) > 0)
                                        <span style="color: #dc2626;">{{ $item->diskon }}%</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right font-bold">Rp {{ number_format($lineTotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 24px; color: #9ca3af;">Tidak ada item.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right" style="color: #6b7280; padding: 10px 16px;">Subtotal</td>
                            <td class="text-right" style="padding: 10px 16px;">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if(($penjualan->tax_percentage ?? 0) > 0)
                            <tr>
                                <td colspan="5" class="text-right" style="color: #6b7280; padding: 10px 16px;">Pajak ({{ $penjualan->tax_percentage }}%)</td>
                                <td class="text-right" style="padding: 10px 16px;">Rp {{ number_format(($penjualan->grand_total ?? 0) - $subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr style="background: #f9fafb;">
                            <td colspan="5" class="text-right" style="font-weight: 700; padding: 12px 16px;">Grand Total</td>
                            <td class="text-right" style="font-weight: 700; padding: 12px 16px; font-size: 1rem; color: #1e40af;">
                                Rp {{ number_format($penjualan->grand_total ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Mobile --}}
            <div class="detail-mobile" style="display: none;">
                <div class="card-body" style="padding: 0;">
                    @php $subtotal2 = 0; @endphp
                    @forelse($penjualan->items as $item)
                        @php
                            $lineTotal2 = $item->jumlah_baris ?? (($item->harga_satuan ?? 0) * ($item->kuantitas ?? 0));
                            $subtotal2 += $lineTotal2;
                        @endphp
                        <div style="padding: 14px 16px; border-bottom: 1px solid #f3f4f6;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <div class="font-bold" style="color: #111827; font-size: 0.85rem;">{{ $item->produk->nama_produk ?? '-' }}</div>
                                    <div style="font-size: 0.72rem; color: #6b7280; margin-top: 2px;">
                                        {{ $item->kuantitas ?? 0 }} x Rp {{ number_format($item->harga_satuan ?? 0, 0, ',', '.') }}
                                        @if(($item->diskon ?? 0) > 0) <span style="color: #dc2626;">(- {{ $item->diskon }}%)</span> @endif
                                    </div>
                                </div>
                                <div class="font-bold" style="font-size: 0.85rem; color: #111827;">Rp {{ number_format($lineTotal2, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 24px; color: #9ca3af; font-size: 0.82rem;">Tidak ada item.</div>
                    @endforelse

                    <div style="padding: 12px 16px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-size: 0.8rem; color: #6b7280;">Subtotal</span>
                            <span style="font-size: 0.8rem;">Rp {{ number_format($subtotal2, 0, ',', '.') }}</span>
                        </div>
                        @if(($penjualan->tax_percentage ?? 0) > 0)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span style="font-size: 0.8rem; color: #6b7280;">Pajak ({{ $penjualan->tax_percentage }}%)</span>
                                <span style="font-size: 0.8rem;">Rp {{ number_format(($penjualan->grand_total ?? 0) - $subtotal2, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid #e5e7eb;">
                            <span style="font-size: 0.9rem; font-weight: 700;">Grand Total</span>
                            <span style="font-size: 0.9rem; font-weight: 700; color: #1e40af;">Rp {{ number_format($penjualan->grand_total ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 340px"] {
            grid-template-columns: 1fr !important;
        }
        .detail-desktop { display: none !important; }
        .detail-mobile { display: block !important; }
    }
</style>
@endpush
