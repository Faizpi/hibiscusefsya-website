@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-2 mb-sm-0 text-gray-800">Pembelian</h1>
        <a href="{{ route('pembelian.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Permintaan Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total (Pending/Approved)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($fakturBelumDibayar, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Jatuh Tempo Lewat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($fakturTelatBayar, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Dibatalkan (Canceled)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $fakturCanceled }} Transaksi</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-ban fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Permintaan Pembelian</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor</th>
                            <th>Pembuat</th>
                            <th>Approver</th>
                            <th>Gudang</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembelians as $item)
                            <tr>
                                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('pembelian.show', $item->id) }}">
                                        <strong>{{ $item->custom_number }}</strong>
                                    </a>
                                </td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->approver->name ?? '-' }}</td>
                                <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($item->status == 'Approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($item->status == 'Pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($item->status == 'Canceled')
                                        <span class="badge badge-secondary">Canceled</span>
                                    @else
                                        <span class="badge badge-info">{{ $item->status }}</span>
                                    @endif

                                    {{-- Indikator Telat --}}
                                    @if($item->status == 'Approved' && $item->tgl_jatuh_tempo && \Carbon\Carbon::parse($item->tgl_jatuh_tempo)->isPast())
                                        <br><span class="badge badge-danger mt-1">Telat</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php $role = auth()->user()->role; @endphp

                                    {{-- TOMBOL APPROVE (Hanya jika Pending & Role Sesuai) --}}
                                    @if($item->status == 'Pending')
                                        @if($role == 'super_admin' || ($role == 'admin' && $item->approver_id == auth()->id()))
                                            <form action="{{ route('pembelian.approve', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-circle btn-sm" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    {{-- TOMBOL CANCEL (Admin/Super Admin, jika belum Canceled) --}}
                                    @if(in_array($role, ['admin', 'super_admin']) && $item->status != 'Canceled')
                                        <form action="{{ route('pembelian.cancel', $item->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Batalkan transaksi ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-dark btn-circle btn-sm" title="Cancel Transaksi">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- TOMBOL EDIT & DELETE --}}
                                    {{-- Super Admin/Admin: Bebas. User: Hanya jika Pending. --}}
                                    @php
                                        $canEdit = false;
                                        if (in_array($role, ['admin', 'super_admin']))
                                            $canEdit = true;
                                        elseif ($item->user_id == auth()->id() && $item->status == 'Pending')
                                            $canEdit = true;
                                    @endphp

                                    @if($canEdit)
                                        <a href="{{ route('pembelian.edit', $item->id) }}" class="btn btn-warning btn-circle btn-sm"
                                            title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-circle btn-sm" data-toggle="modal"
                                            data-target="#deleteModal" data-action="{{ route('pembelian.destroy', $item->id) }}"
                                            title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <span class="text-muted small">Locked</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data permintaan pembelian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yakin Hapus?</h5><button class="close" type="button"
                        data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">Data yang dihapus tidak bisa dikembalikan.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST">@csrf @method('DELETE') <button type="submit"
                            class="btn btn-danger">Hapus</button></form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var action = button.data('action');
            var modal = $(this);
            modal.find('#deleteForm').attr('action', action);
        });
    </script>
@endpush