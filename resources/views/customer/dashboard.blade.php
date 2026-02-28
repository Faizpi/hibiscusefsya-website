@extends('customer.layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- Welcome --}}
    <div class="mb-4">
        <h4 class="font-weight-bold text-dark">
            Selamat Datang, {{ $kontak->nama }}! <i class="fas fa-hand-sparkles text-warning"></i>
        </h4>
        <p class="text-muted mb-0">Berikut ringkasan data akun dan transaksi Anda.</p>
    </div>

    {{-- Info Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon mr-3" style="background: #e3f2fd;">
                        <i class="fas fa-user fa-lg" style="color: #1565c0;"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Kode Kontak</small>
                        <strong>{{ $kontak->kode_kontak ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon mr-3" style="background: #fce4ec;">
                        <i class="fas fa-percent fa-lg" style="color: #e91e63;"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Diskon Anda</small>
                        <strong class="text-primary">{{ $kontak->diskon_persen ?? 0 }}%</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon mr-3" style="background: #e8f5e9;">
                        <i class="fas fa-receipt fa-lg" style="color: #2e7d32;"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Transaksi</small>
                        <strong>{{ $totalTransaksi }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon mr-3" style="background: #fff3e0;">
                        <i class="fas fa-money-bill-wave fa-lg" style="color: #e65100;"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Nilai</small>
                        <strong>Rp {{ number_format($totalNilai, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Profil --}}
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-id-card mr-1"></i> Data Profil
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td width="35%" class="text-muted py-2">Nama</td>
                            <td class="py-2"><strong>{{ $kontak->nama }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">No. Telepon</td>
                            <td class="py-2">{{ $kontak->no_telp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Email</td>
                            <td class="py-2">{{ $kontak->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Alamat</td>
                            <td class="py-2">{{ $kontak->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Diskon Bawaan</td>
                            <td class="py-2">
                                <span class="badge badge-primary px-3 py-2" style="font-size: 0.9rem;">
                                    {{ $kontak->diskon_persen ?? 0 }}%
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock mr-1"></i> Transaksi Terakhir
                    </h6>
                    <a href="{{ route('customer.history') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @php
                        $recentTransaksi = \App\Penjualan::where('pelanggan', $kontak->nama)
                            ->whereIn('status', ['Approved', 'Lunas', 'Pending'])
                            ->orderBy('tgl_transaksi', 'desc')
                            ->take(5)
                            ->get();
                    @endphp
                    @if($recentTransaksi->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentTransaksi as $trx)
                                <a href="{{ route('customer.history.detail', $trx->id) }}" 
                                   class="list-group-item list-group-item-action py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="d-block">{{ $trx->number }}</strong>
                                            <small class="text-muted">
                                                {{ $trx->tgl_transaksi ? $trx->tgl_transaksi->format('d M Y') : '-' }}
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <span class="d-block font-weight-bold">
                                                Rp {{ number_format($trx->grand_total ?? 0, 0, ',', '.') }}
                                            </span>
                                            @if($trx->status == 'Lunas')
                                                <span class="badge badge-success">Lunas</span>
                                            @elseif($trx->status == 'Approved')
                                                <span class="badge badge-info">Approved</span>
                                            @elseif($trx->status == 'Pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Belum ada transaksi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
