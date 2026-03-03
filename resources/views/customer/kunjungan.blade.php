@extends('customer.layouts.app')

@section('title', 'Riwayat Kunjungan')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Riwayat Kunjungan</h4>
        <p class="page-subtitle">Daftar kunjungan yang tercatat untuk Anda.</p>
    </div>

    {{-- Filter --}}
    <div class="card filter-card">
        <div class="card-body" style="padding: 16px 22px;">
            <form method="GET" action="{{ route('customer.kunjungan') }}" class="filter-form">
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
                    <a href="{{ route('customer.kunjungan') }}" class="btn btn-outline btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Desktop Table --}}
    @if($kunjungans->count() > 0)
        <div class="card desktop-table">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No. Kunjungan</th>
                            <th>Tanggal</th>
                            <th>Tujuan</th>
                            <th>Sales</th>
                            <th>Gudang</th>
                            <th class="text-center">Item</th>
                            <th class="text-center">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kunjungans as $k)
                            <tr>
                                <td><span class="font-bold" style="color: #111827;">{{ $k->nomor }}</span></td>
                                <td>{{ $k->tgl_kunjungan ? $k->tgl_kunjungan->format('d M Y') : '-' }}</td>
                                <td>
                                    @if($k->tujuan == 'Promo Gratis')
                                        <span class="badge badge-success">Promo Gratis</span>
                                    @elseif($k->tujuan == 'Promo Sample')
                                        <span class="badge" style="background:#6f42c1;color:#fff;">Promo Sample</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $k->tujuan }}</span>
                                    @endif
                                </td>
                                <td>{{ $k->user->name ?? '-' }}</td>
                                <td>{{ $k->gudang->nama_gudang ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $k->items->count() }} produk</span>
                                </td>
                                <td class="text-center">
                                    @if($k->status == 'Approved')
                                        <span class="badge badge-info">Approved</span>
                                    @elseif($k->status == 'Pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $k->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('customer.kunjungan.detail', $k->id) }}"
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
            @foreach($kunjungans as $k)
                <a href="{{ route('customer.kunjungan.detail', $k->id) }}" class="card mobile-trx-card">
                    <div class="card-body" style="padding: 16px 18px;">
                        <div class="mobile-trx-top">
                            <div>
                                <div class="mobile-trx-number">{{ $k->nomor }}</div>
                                <div class="mobile-trx-date">{{ $k->tgl_kunjungan ? $k->tgl_kunjungan->format('d M Y') : '-' }}</div>
                            </div>
                            @if($k->status == 'Approved')
                                <span class="badge badge-info">Approved</span>
                            @elseif($k->status == 'Pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-secondary">{{ $k->status }}</span>
                            @endif
                        </div>
                        <div class="mobile-trx-bottom">
                            <span class="mobile-trx-meta">{{ $k->tujuan }} &middot; {{ $k->user->name ?? '-' }} &middot; {{ $k->items->count() }} produk</span>
                            <span class="mobile-trx-total">{{ $k->gudang->nama_gudang ?? '-' }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="display: flex; justify-content: center; margin-top: 24px;">
            {{ $kunjungans->appends(request()->query())->links() }}
        </div>
    @else
        <div class="card">
            <div class="empty-state-lg">
                <div class="empty-title">Belum Ada Kunjungan</div>
                <div class="empty-desc">Data kunjungan Anda akan muncul di sini.</div>
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
            margin-bottom: 6px;
        }

        .page-subtitle {
            font-size: 0.95rem;
            color: #6b7280;
            margin: 0;
            padding-bottom: 12px;
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
            font-size: 0.88rem;
            font-weight: 600;
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
