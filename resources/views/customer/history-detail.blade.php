@extends('customer.layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
    <div class="detail-top">
        <a href="{{ route('customer.history') }}" class="btn btn-outline btn-sm" style="margin-bottom: 16px;">
            &#8592; Kembali
        </a>
        <div class="detail-heading">
            <h4 class="detail-title">{{ $penjualan->number }}</h4>
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
        <p class="detail-date">{{ $penjualan->tgl_transaksi ? $penjualan->tgl_transaksi->format('d F Y') : '-' }}</p>
    </div>

    <div class="detail-grid">
        {{-- Info Transaksi --}}
        <div class="card" style="align-self: start;">
            <div class="card-header">Info Transaksi</div>
            <div class="card-body" style="padding: 0;">
                <table style="width: 100%;">
                    <tr>
                        <td class="info-label">Invoice</td>
                        <td class="info-value">{{ $penjualan->number }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal</td>
                        <td class="info-value">
                            {{ $penjualan->tgl_transaksi ? $penjualan->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Gudang</td>
                        <td class="info-value">{{ $penjualan->gudang->nama_gudang ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Sales</td>
                        <td class="info-value">{{ $penjualan->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Pajak</td>
                        <td class="info-value">{{ $penjualan->tax_percentage ?? 0 }}%</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="border-bottom: none;">Memo</td>
                        <td class="info-value" style="border-bottom: none;">{{ $penjualan->memo ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Detail Produk --}}
        <div class="card">
            <div class="card-header">Detail Produk</div>

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
                                    <div class="font-bold" style="color: #111827;">
                                        {{ $item->produk->nama_produk ?? $item->deskripsi ?? '-' }}</div>
                                    @if($item->produk && $item->produk->item_code)
                                        <div style="font-size: 0.78rem; color: #9ca3af; margin-top: 2px;">
                                            {{ $item->produk->item_code }}</div>
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
                            <td colspan="5" class="text-right" style="color: #6b7280; padding: 12px 18px;">Subtotal</td>
                            <td class="text-right" style="padding: 12px 18px;">Rp
                                {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if(($penjualan->tax_percentage ?? 0) > 0)
                            <tr>
                                <td colspan="5" class="text-right" style="color: #6b7280; padding: 12px 18px;">Pajak
                                    ({{ $penjualan->tax_percentage }}%)</td>
                                <td class="text-right" style="padding: 12px 18px;">Rp
                                    {{ number_format(($penjualan->grand_total ?? 0) - $subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr style="background: #f9fafb;">
                            <td colspan="5" class="text-right" style="font-weight: 700; padding: 14px 18px;">Grand Total
                            </td>
                            <td class="text-right"
                                style="font-weight: 700; padding: 14px 18px; font-size: 1.05rem; color: #1e40af;">
                                Rp {{ number_format($penjualan->grand_total ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Mobile --}}
            <div class="detail-mobile">
                <div class="card-body" style="padding: 0;">
                    @php $subtotal2 = 0; @endphp
                    @forelse($penjualan->items as $item)
                        @php
                            $lineTotal2 = $item->jumlah_baris ?? (($item->harga_satuan ?? 0) * ($item->kuantitas ?? 0));
                            $subtotal2 += $lineTotal2;
                        @endphp
                        <div class="mobile-product-item">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <div class="font-bold" style="color: #111827; font-size: 0.95rem;">
                                        {{ $item->produk->nama_produk ?? '-' }}</div>
                                    <div style="font-size: 0.82rem; color: #6b7280; margin-top: 3px;">
                                        {{ $item->kuantitas ?? 0 }} x Rp
                                        {{ number_format($item->harga_satuan ?? 0, 0, ',', '.') }}
                                        @if(($item->diskon ?? 0) > 0) <span style="color: #dc2626;">(-
                                        {{ $item->diskon }}%)</span> @endif
                                    </div>
                                </div>
                                <div class="font-bold" style="font-size: 0.95rem; color: #111827;">Rp
                                    {{ number_format($lineTotal2, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 28px; color: #9ca3af; font-size: 0.9rem;">Tidak ada item.</div>
                    @endforelse

                    <div class="mobile-total-section">
                        <div class="mobile-total-row">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($subtotal2, 0, ',', '.') }}</span>
                        </div>
                        @if(($penjualan->tax_percentage ?? 0) > 0)
                            <div class="mobile-total-row">
                                <span>Pajak ({{ $penjualan->tax_percentage }}%)</span>
                                <span>Rp {{ number_format(($penjualan->grand_total ?? 0) - $subtotal2, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="mobile-grand-total">
                            <span>Grand Total</span>
                            <span style="color: #1e40af;">Rp
                                {{ number_format($penjualan->grand_total ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .detail-top {
            margin-bottom: 24px;
            padding-top: 8px;
        }

        .detail-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .detail-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .detail-date {
            font-size: 0.95rem;
            color: #6b7280;
            margin: 6px 0 0;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 20px;
        }

        .info-label {
            padding: 12px 22px;
            color: #6b7280;
            font-size: 0.88rem;
            width: 40%;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-value {
            padding: 12px 22px;
            font-size: 0.88rem;
            font-weight: 600;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-mobile {
            display: none;
        }

        .mobile-product-item {
            padding: 16px 18px;
            border-bottom: 1px solid #f3f4f6;
        }

        .mobile-total-section {
            padding: 14px 18px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .mobile-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 0.88rem;
            color: #6b7280;
        }

        .mobile-grand-total {
            display: flex;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 1rem;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .detail-desktop {
                display: none !important;
            }

            .detail-mobile {
                display: block !important;
            }
        }
    </style>
@endpush