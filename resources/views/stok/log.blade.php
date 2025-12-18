@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Riwayat Perubahan Stok</h1>
            <a href="{{ route('stok.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Master Stok
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history mr-2"></i>Log Perubahan Stok
                </h6>
            </div>
            <div class="card-body">
                {{-- Filter --}}
                <form action="{{ route('stok.log') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="gudang_id">Gudang</label>
                                <select name="gudang_id" id="gudang_id" class="form-control">
                                    <option value="">Semua Gudang</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}" {{ request('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                            {{ $gudang->nama_gudang }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="produk_id">Produk</label>
                                <select name="produk_id" id="produk_id" class="form-control">
                                    <option value="">Semua Produk</option>
                                    @foreach($produks as $produk)
                                        <option value="{{ $produk->id }}" {{ request('produk_id') == $produk->id ? 'selected' : '' }}>
                                            {{ $produk->nama_produk }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="tanggal_dari">Dari Tanggal</label>
                                <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control"
                                    value="{{ request('tanggal_dari') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="tanggal_sampai">Sampai Tanggal</label>
                                <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control"
                                    value="{{ request('tanggal_sampai') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                    <a href="{{ route('stok.log') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="150">Waktu</th>
                                <th>Produk</th>
                                <th>Gudang</th>
                                <th class="text-center">Sebelum</th>
                                <th class="text-center">Sesudah</th>
                                <th class="text-center">Selisih</th>
                                <th>Diubah Oleh</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <small>{{ $log->created_at->format('d/m/Y') }}</small><br>
                                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $log->produk_nama }}</strong>
                                        @if($log->produk)
                                            <br><small class="text-muted">{{ $log->produk->item_code ?? '-' }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $log->gudang_nama }}</td>
                                    <td class="text-center">{{ number_format($log->stok_sebelum) }}</td>
                                    <td class="text-center font-weight-bold">{{ number_format($log->stok_sesudah) }}</td>
                                    <td class="text-center">
                                        @if($log->selisih > 0)
                                            <span class="badge badge-success">+{{ number_format($log->selisih) }}</span>
                                        @elseif($log->selisih < 0)
                                            <span class="badge badge-danger">{{ number_format($log->selisih) }}</span>
                                        @else
                                            <span class="badge badge-secondary">0</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->user_nama }}</td>
                                    <td>
                                        <small>{{ $log->keterangan ?? '-' }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada riwayat perubahan stok.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Init Select2 untuk filter dropdown
            $('#gudang_id, #produk_id').select2({
                placeholder: 'Pilih...',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endpush