@extends('customer.layouts.app')

@section('title', 'Riwayat Pembelian')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-receipt text-primary mr-1"></i> Riwayat Pembelian
            </h4>
            <p class="text-muted mb-0">Daftar transaksi pembelian Anda.</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('customer.history') }}" class="row align-items-end">
                <div class="col-md-4 col-6 mb-2">
                    <label class="small font-weight-bold mb-1">Dari Tanggal</label>
                    <input type="date" name="dari" class="form-control form-control-sm" value="{{ request('dari') }}">
                </div>
                <div class="col-md-4 col-6 mb-2">
                    <label class="small font-weight-bold mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="form-control form-control-sm" value="{{ request('sampai') }}">
                </div>
                <div class="col-md-4 mb-2">
                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('customer.history') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Transactions List --}}
    @if($penjualans->count() > 0)
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>No. Invoice</th>
                                <th>Tanggal</th>
                                <th>Gudang</th>
                                <th>Item</th>
                                <th class="text-right">Grand Total</th>
                                <th class="text-center">Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penjualans as $trx)
                                <tr>
                                    <td>
                                        <strong>{{ $trx->number }}</strong>
                                    </td>
                                    <td>{{ $trx->tgl_transaksi ? $trx->tgl_transaksi->format('d M Y') : '-' }}</td>
                                    <td>{{ $trx->gudang->nama_gudang ?? '-' }}</td>
                                    <td>
                                        @if($trx->items->count() > 0)
                                            <span class="badge badge-light">{{ $trx->items->count() }} produk</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        Rp {{ number_format($trx->grand_total ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($trx->status == 'Lunas')
                                            <span class="badge badge-success px-2 py-1">Lunas</span>
                                        @elseif($trx->status == 'Approved')
                                            <span class="badge badge-info px-2 py-1">Approved</span>
                                        @elseif($trx->status == 'Pending')
                                            <span class="badge badge-warning px-2 py-1">Pending</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1">{{ $trx->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('customer.history.detail', $trx->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $penjualans->appends(request()->query())->links() }}
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                <h5 class="text-muted">Belum Ada Transaksi</h5>
                <p class="text-muted mb-0">Data pembelian Anda akan muncul di sini.</p>
            </div>
        </div>
    @endif
@endsection
