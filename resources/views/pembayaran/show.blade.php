@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4 page-header-mobile">
            <h1 class="h3 mb-0 text-gray-800">Detail Pembayaran</h1>
            <div class="show-action-buttons">
                <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                    <i class="fas fa-arrow-left fa-sm"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-money-bill-wave"></i> Informasi Pembayaran
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Nomor</strong></td>
                                        <td width="5%">:</td>
                                        <td><span class="badge badge-dark font-weight-bold" style="font-size: 1rem;">{{ $pembayaran->custom_number }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal</strong></td>
                                        <td>:</td>
                                        <td>{{ $pembayaran->tgl_pembayaran->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Metode</strong></td>
                                        <td>:</td>
                                        <td>{{ $pembayaran->metode_pembayaran }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status</strong></td>
                                        <td>:</td>
                                        <td>
                                            @if($pembayaran->status == 'Approved')
                                                <span class="badge badge-success">Approved</span>
                                            @elseif($pembayaran->status == 'Pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $pembayaran->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Dibuat oleh</strong></td>
                                        <td width="5%">:</td>
                                        <td>{{ $pembayaran->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Approver</strong></td>
                                        <td>:</td>
                                        <td>{{ $pembayaran->approver ? $pembayaran->approver->name : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gudang</strong></td>
                                        <td>:</td>
                                        <td>{{ $pembayaran->gudang->nama_gudang ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dibuat</strong></td>
                                        <td>:</td>
                                        <td>{{ $pembayaran->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <h6 class="font-weight-bold">Referensi Invoice Penjualan</h6>
                        @if($pembayaran->penjualan)
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <td width="30%"><strong>Nomor Invoice</strong></td>
                                    <td>
                                        <a href="{{ route('penjualan.show', $pembayaran->penjualan_id) }}">
                                            {{ $pembayaran->penjualan->nomor ?? $pembayaran->penjualan->custom_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Pelanggan</strong></td>
                                    <td>{{ $pembayaran->penjualan->pelanggan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Invoice</strong></td>
                                    <td>Rp {{ number_format($pembayaran->penjualan->grand_total, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sisa Hutang</strong></td>
                                    <td class="text-danger font-weight-bold">Rp {{ number_format($sisaHutang, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        @else
                            <p class="text-muted">Invoice tidak ditemukan.</p>
                        @endif

                        <hr>

                        <div class="row">
                            <div class="col-12">
                                <h4 class="text-right">
                                    Jumlah Bayar: 
                                    <span class="text-success font-weight-bold">
                                        Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}
                                    </span>
                                </h4>
                            </div>
                        </div>

                        @if($pembayaran->keterangan)
                            <hr>
                            <h6 class="font-weight-bold">Keterangan</h6>
                            <p>{{ $pembayaran->keterangan }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Action Card --}}
                @php $role = auth()->user()->role; @endphp
                @if($role !== 'spectator')
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cogs"></i> Aksi</h6>
                        </div>
                        <div class="card-body">
                            @if(in_array($role, ['admin', 'super_admin']) && $pembayaran->status == 'Pending')
                                <form action="{{ route('pembayaran.approve', $pembayaran->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                            @endif

                            @if(in_array($role, ['admin', 'super_admin']) && $pembayaran->status != 'Canceled')
                                @if($role == 'super_admin' || $pembayaran->status == 'Pending')
                                    <form action="{{ route('pembayaran.cancel', $pembayaran->id) }}" method="POST" class="mb-2"
                                        onsubmit="return confirm('Yakin ingin membatalkan pembayaran ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-block">
                                            <i class="fas fa-ban"></i> Batalkan
                                        </button>
                                    </form>
                                @endif
                            @endif

                            @if($pembayaran->status == 'Canceled' && $role == 'super_admin')
                                <form action="{{ route('pembayaran.uncancel', $pembayaran->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-block">
                                        <i class="fas fa-undo"></i> Batalkan Pembatalan
                                    </button>
                                </form>
                            @endif

                            @if($role == 'super_admin')
                                <hr>
                                <form action="{{ route('pembayaran.destroy', $pembayaran->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus pembayaran ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Lampiran Card --}}
                @if($pembayaran->lampiran_paths && count($pembayaran->lampiran_paths) > 0)
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-paperclip"></i> Lampiran</h6>
                        </div>
                        <div class="card-body">
                            @foreach($pembayaran->lampiran_paths as $index => $path)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                        <i class="fas fa-file"></i> Lampiran {{ $index + 1 }}
                                    </a>
                                    @if($role == 'super_admin')
                                        <form action="{{ route('pembayaran.deleteLampiran', [$pembayaran->id, $index]) }}" 
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Hapus lampiran ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
