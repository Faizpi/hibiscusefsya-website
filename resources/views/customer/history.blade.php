@extends('customer.layouts.app')

@section('title', 'Riwayat Pembelian')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Riwayat Pembelian</h4>
        <p class="page-subtitle">Daftar transaksi pembelian Anda.</p>
    </div>

    {{-- Filter --}}
    <div class="card filter-card">
        <div class="card-body" style="padding: 16px 22px;">
            <form method="GET" action="{{ route('customer.history') }}" class="filter-form">
                <div class="filter-field">
                    <label class="filter-label">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ request('dari') }}" class="filter-input">
                </div>
                <div class="filter-field">
                    <label class="filter-label">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ request('sampai') }}" class="filter-input">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('customer.history') }}" class="btn btn-outline btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Desktop Table --}}
    @if($penjualans->count() > 0)
        <div class="card desktop-table">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Tanggal</th>
                            <th>Gudang</th>
                            <th class="text-center">Item</th>
                            <th class="text-right">Grand Total</th>
                            <th class="text-center">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penjualans as $trx)
                            <tr>
                                <td><span class="font-bold" style="color: #111827;">{{ $trx->number }}</span></td>
                                <td>{{ $trx->tgl_transaksi ? $trx->tgl_transaksi->format('d M Y') : '-' }}</td>
                                <td>{{ $trx->gudang->nama_gudang ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $trx->items->count() }} produk</span>
                                </td>
                                <td class="text-right font-bold">Rp {{ number_format($trx->grand_total ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($trx->status == 'Lunas')
                                        <span class="badge badge-success">Lunas</span>
                                    @elseif($trx->status == 'Approved')
                                        <span class="badge badge-info">Approved</span>
                                    @elseif($trx->status == 'Pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $trx->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('customer.history.detail', $trx->id) }}"
                                        class="btn btn-outline btn-icon btn-sm" title="Detail">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Cards --}}
        <div class="mobile-cards">
            @foreach($penjualans as $trx)
                <a href="{{ route('customer.history.detail', $trx->id) }}" class="card mobile-trx-card">
                    <div class="card-body" style="padding: 16px 18px;">
                        <div class="mobile-trx-top">
                            <div>
                                <div class="mobile-trx-number">{{ $trx->number }}</div>
                                <div class="mobile-trx-date">{{ $trx->tgl_transaksi ? $trx->tgl_transaksi->format('d M Y') : '-' }}
                                </div>
                            </div>
                            @if($trx->status == 'Lunas')
                                <span class="badge badge-success">Lunas</span>
                            @elseif($trx->status == 'Approved')
                                <span class="badge badge-info">Approved</span>
                            @elseif($trx->status == 'Pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-secondary">{{ $trx->status }}</span>
                            @endif
                        </div>
                        <div class="mobile-trx-bottom">
                            <span class="mobile-trx-meta">{{ $trx->gudang->nama_gudang ?? '-' }} &middot; {{ $trx->items->count() }}
                                produk</span>
                            <span class="mobile-trx-total">Rp {{ number_format($trx->grand_total ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="display: flex; justify-content: center; margin-top: 24px;">
            {{ $penjualans->appends(request()->query())->links() }}
        </div>
    @else
        <div class="card">
            <div class="empty-state-lg">
                <div class="empty-title">Belum Ada Transaksi</div>
                <div class="empty-desc">Data pembelian Anda akan muncul di sini.</div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .page-header {
            margin-bottom: 40px;
            padding-top: 8px;
        }

        .page-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 0.95rem;
            color: #6b7280;
            margin: 0;
            padding-bottom: 4px;
            line-height: 1.5;
        }

        .filter-card {
            margin-bottom: 20px;
        }

        .filter-form {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-field {
            flex: 1;
            min-width: 140px;
        }

        .filter-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
            color: #374151;
            outline: none;
            background: rgba(255, 255, 255, 0.6);
            transition: border-color 0.2s;
        }

        .filter-input:focus {
            border-color: #2563eb;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
        }

        .mobile-cards {
            display: none;
        }

        .mobile-trx-card {
            display: block;
            text-decoration: none;
            color: inherit;
            margin-bottom: 12px;
        }

        .mobile-trx-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .mobile-trx-number {
            font-size: 0.95rem;
            font-weight: 600;
            color: #111827;
        }

        .mobile-trx-date {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 3px;
        }

        .mobile-trx-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-trx-meta {
            font-size: 0.82rem;
            color: #6b7280;
        }

        .mobile-trx-total {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
        }

        .empty-state-lg {
            text-align: center;
            padding: 52px 20px;
        }

        .empty-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .empty-desc {
            font-size: 0.9rem;
            color: #9ca3af;
        }

        @media (max-width: 768px) {
            .desktop-table {
                display: none;
            }

            .mobile-cards {
                display: block;
            }

            .filter-form {
                flex-direction: column;
            }

            .filter-field {
                min-width: 100%;
            }
        }
    </style>
@endpush