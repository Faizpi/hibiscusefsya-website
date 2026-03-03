@extends('customer.layouts.app')

@section('title', 'Detail Kunjungan')

@section('content')
    <div class="detail-top">
        <a href="{{ route('customer.kunjungan') }}" class="btn btn-outline btn-sm" style="margin-bottom: 16px;">
            &#8592; Kembali
        </a>
        <div class="detail-heading">
            <h4 class="detail-title">{{ $kunjungan->nomor }}</h4>
            @if($kunjungan->status == 'Approved')
                <span class="badge badge-info">Approved</span>
            @elseif($kunjungan->status == 'Pending')
                <span class="badge badge-warning">Pending</span>
            @else
                <span class="badge badge-secondary">{{ $kunjungan->status }}</span>
            @endif
        </div>
        <p class="detail-date">{{ $kunjungan->tgl_kunjungan ? $kunjungan->tgl_kunjungan->format('d F Y') : '-' }}</p>
    </div>

    <div class="detail-grid">
        {{-- Info Kunjungan --}}
        <div class="card" style="align-self: start;">
            <div class="card-header">Info Kunjungan</div>
            <div class="card-body" style="padding: 0;">
                <table style="width: 100%;">
                    <tr>
                        <td class="info-label">Nomor</td>
                        <td class="info-value">{{ $kunjungan->nomor }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal</td>
                        <td class="info-value">{{ $kunjungan->tgl_kunjungan ? $kunjungan->tgl_kunjungan->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tujuan</td>
                        <td class="info-value">
                            @if($kunjungan->tujuan == 'Promo Gratis')
                                <span class="badge badge-success">Promo Gratis</span>
                            @elseif($kunjungan->tujuan == 'Promo Sample')
                                <span class="badge" style="background:#6f42c1;color:#fff;">Promo Sample</span>
                            @else
                                <span class="badge badge-secondary">{{ $kunjungan->tujuan }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Sales</td>
                        <td class="info-value">{{ $kunjungan->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Gudang</td>
                        <td class="info-value">{{ $kunjungan->gudang->nama_gudang ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="border-bottom: none;">Memo</td>
                        <td class="info-value" style="border-bottom: none;">{{ $kunjungan->memo ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Detail Produk --}}
        <div class="card">
            <div class="card-header">Detail Produk</div>

            @if($kunjungan->items && $kunjungan->items->count() > 0)
                {{-- Desktop --}}
                <div class="table-wrap detail-desktop">
                    <table>
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Qty</th>
                                <th>Satuan</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kunjungan->items as $item)
                                <tr>
                                    <td>
                                        <div class="font-bold" style="color: #111827;">{{ $item->produk->nama_produk ?? '-' }}</div>
                                        @if($item->produk && $item->produk->item_code)
                                            <div style="font-size: 0.78rem; color: #9ca3af; margin-top: 2px;">{{ $item->produk->item_code }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->qty ?? 0 }}</td>
                                    <td>{{ $item->produk->satuan ?? 'Pcs' }}</td>
                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: #f9fafb;">
                                <td style="font-weight: 700; padding: 14px 18px;">Total Item</td>
                                <td class="text-center" style="font-weight: 700; padding: 14px 18px;">{{ $kunjungan->items->sum('qty') }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Mobile --}}
                <div class="detail-mobile">
                    <div class="card-body" style="padding: 0;">
                        @foreach($kunjungan->items as $item)
                            <div class="mobile-product-item">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <div class="font-bold" style="color: #111827; font-size: 0.95rem;">{{ $item->produk->nama_produk ?? '-' }}</div>
                                        <div style="font-size: 0.82rem; color: #6b7280; margin-top: 3px;">
                                            {{ $item->qty ?? 0 }} {{ $item->produk->satuan ?? 'Pcs' }}
                                        </div>
                                    </div>
                                    @if($item->keterangan)
                                        <div style="font-size: 0.82rem; color: #6b7280;">{{ $item->keterangan }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="mobile-total-section">
                            <div class="mobile-grand-total">
                                <span>Total Item</span>
                                <span>{{ $kunjungan->items->sum('qty') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card-body" style="text-align: center; padding: 40px; color: #9ca3af;">
                    Tidak ada produk dalam kunjungan ini.
                </div>
            @endif
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

        .mobile-grand-total {
            display: flex;
            justify-content: space-between;
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
