@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4 page-header-mobile">
            <h1 class="h3 mb-0 text-gray-800">Detail Penerimaan Barang</h1>
            <div class="show-action-buttons">
                <a href="{{ route('penerimaan-barang.index') }}" class="btn btn-secondary btn-sm shadow-sm">
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
                            <i class="fas fa-truck-loading"></i> Informasi Penerimaan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Nomor</strong></td>
                                        <td width="5%">:</td>
                                        <td><span class="badge badge-dark font-weight-bold" style="font-size: 1rem;">{{ $penerimaan->custom_number }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal</strong></td>
                                        <td>:</td>
                                        <td>{{ $penerimaan->tgl_penerimaan->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>No. Surat Jalan</strong></td>
                                        <td>:</td>
                                        <td>{{ $penerimaan->no_surat_jalan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status</strong></td>
                                        <td>:</td>
                                        <td>
                                            @if($penerimaan->status == 'Approved')
                                                <span class="badge badge-success">Approved</span>
                                            @elseif($penerimaan->status == 'Pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $penerimaan->status }}</span>
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
                                        <td>{{ $penerimaan->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Approver</strong></td>
                                        <td>:</td>
                                        <td>{{ $penerimaan->approver ? $penerimaan->approver->name : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gudang</strong></td>
                                        <td>:</td>
                                        <td>{{ $penerimaan->gudang->nama_gudang ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dibuat</strong></td>
                                        <td>:</td>
                                        <td>{{ $penerimaan->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <h6 class="font-weight-bold">Referensi Invoice Pembelian</h6>
                        @if($penerimaan->pembelian)
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <td width="30%"><strong>Nomor Invoice</strong></td>
                                    <td>
                                        <a href="{{ route('pembelian.show', $penerimaan->pembelian_id) }}">
                                            {{ $penerimaan->pembelian->nomor ?? $penerimaan->pembelian->custom_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Supplier</strong></td>
                                    <td>{{ $penerimaan->pembelian->nama_supplier ?? '-' }}</td>
                                </tr>
                            </table>
                        @else
                            <p class="text-muted">Invoice tidak ditemukan.</p>
                        @endif

                        <hr>

                        <h6 class="font-weight-bold">Detail Barang Diterima</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Produk</th>
                                        <th class="text-center">Qty Diterima</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($penerimaan->items as $item)
                                        <tr>
                                            <td>{{ $item->produk->item_kode ?? '-' }}</td>
                                            <td>{{ $item->produk->item_nama ?? '-' }}</td>
                                            <td class="text-center font-weight-bold">{{ $item->qty_diterima }}</td>
                                            <td>{{ $item->keterangan ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada item.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th colspan="2" class="text-right">Total Item:</th>
                                        <th class="text-center">{{ $penerimaan->items->sum('qty_diterima') }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($penerimaan->keterangan)
                            <hr>
                            <h6 class="font-weight-bold">Keterangan</h6>
                            <p>{{ $penerimaan->keterangan }}</p>
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
                            @if(in_array($role, ['admin', 'super_admin']) && $penerimaan->status == 'Pending')
                                <form action="{{ route('penerimaan-barang.approve', $penerimaan->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-check"></i> Approve & Tambah Stok
                                    </button>
                                </form>
                            @endif

                            @if(in_array($role, ['admin', 'super_admin']) && $penerimaan->status != 'Canceled')
                                @if($role == 'super_admin' || $penerimaan->status == 'Pending')
                                    <form action="{{ route('penerimaan-barang.cancel', $penerimaan->id) }}" method="POST" class="mb-2"
                                        onsubmit="return confirm('Yakin ingin membatalkan penerimaan ini? Jika sudah approved, stok akan dikurangi kembali.')">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-block">
                                            <i class="fas fa-ban"></i> Batalkan
                                        </button>
                                    </form>
                                @endif
                            @endif

                            @if($penerimaan->status == 'Canceled' && $role == 'super_admin')
                                <form action="{{ route('penerimaan-barang.uncancel', $penerimaan->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-block">
                                        <i class="fas fa-undo"></i> Batalkan Pembatalan
                                    </button>
                                </form>
                            @endif

                            @if($role == 'super_admin')
                                <hr>
                                <form action="{{ route('penerimaan-barang.destroy', $penerimaan->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus penerimaan ini? Jika sudah approved, stok akan dikurangi kembali.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Status Info --}}
                @if($penerimaan->status == 'Approved')
                    <div class="card border-left-success shadow mb-4">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Status Stok</div>
                            <div class="text-success">
                                <i class="fas fa-check-circle"></i> Stok telah ditambahkan ke gudang
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Lampiran Card --}}
                @if($penerimaan->lampiran_paths && count($penerimaan->lampiran_paths) > 0)
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-paperclip"></i> Lampiran</h6>
                        </div>
                        <div class="card-body">
                            @foreach($penerimaan->lampiran_paths as $index => $path)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                        <i class="fas fa-file"></i> Lampiran {{ $index + 1 }}
                                    </a>
                                    @if($role == 'super_admin')
                                        <form action="{{ route('penerimaan-barang.deleteLampiran', [$penerimaan->id, $index]) }}" 
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
