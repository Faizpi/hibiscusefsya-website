@extends('customer.layouts.app')

@section('title', 'Riwayat Pembelian')

@section('content')
    <div style="margin-bottom: 24px;">
        <h4 style="font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 4px;">
            Riwayat Pembelian
        </h4>
        <p style="font-size: 0.85rem; color: #6b7280; margin: 0;">Daftar transaksi pembelian Anda.</p>
    </div>

    {{-- Filter --}}
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 14px 20px;">
            <form method="GET" action="{{ route('customer.history') }}" style="display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 140px;">
                    <label style="display: block; font-size: 0.72rem; font-weight: 600; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ request('dari') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1.5px solid #d1d5db; border-radius: 8px; font-size: 0.82rem; font-family: 'Inter', sans-serif; color: #374151; outline: none;">
                </div>
                <div style="flex: 1; min-width: 140px;">
                    <label style="display: block; font-size: 0.72rem; font-weight: 600; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ request('sampai') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1.5px solid #d1d5db; border-radius: 8px; font-size: 0.82rem; font-family: 'Inter', sans-serif; color: #374151; outline: none;">
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
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
                                    <a href="{{ route('customer.history.detail', $trx->id) }}" class="btn btn-outline btn-icon btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
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
                <a href="{{ route('customer.history.detail', $trx->id) }}" class="card" style="display: block; text-decoration: none; color: inherit; margin-bottom: 12px;">
                    <div class="card-body" style="padding: 14px 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <div>
                                <div style="font-size: 0.85rem; font-weight: 600; color: #111827;">{{ $trx->number }}</div>
                                <div style="font-size: 0.72rem; color: #6b7280; margin-top: 2px;">
                                    {{ $trx->tgl_transaksi ? $trx->tgl_transaksi->format('d M Y') : '-' }}
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
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; color: #6b7280;">{{ $trx->gudang->nama_gudang ?? '-' }} &middot; {{ $trx->items->count() }} produk</span>
                            <span style="font-size: 0.9rem; font-weight: 700; color: #111827;">Rp {{ number_format($trx->grand_total ?? 0, 0, ',', '.') }}</span>
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
            <div style="text-align: center; padding: 48px 20px; color: #9ca3af;">
                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i>
                <div style="font-size: 0.95rem; font-weight: 600; color: #6b7280; margin-bottom: 4px;">Belum Ada Transaksi</div>
                <div style="font-size: 0.82rem;">Data pembelian Anda akan muncul di sini.</div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
<style>
    .mobile-cards { display: none; }
    @media (max-width: 768px) {
        .desktop-table { display: none; }
        .mobile-cards { display: block; }
    }
</style>
@endpush
