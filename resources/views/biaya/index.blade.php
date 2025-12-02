@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Biaya</h1>
    <a href="{{ route('biaya.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Buat Biaya Baru
    </a>
</div>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Bulan Ini</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</div></div>
                <div class="col-auto"><i class="fas fa-calendar-alt fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Approval</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalBelumDibayar, 0, ',', '.') }}</div></div>
                <div class="col-auto"><i class="fas fa-comments-dollar fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total 30 Hari</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($total30Hari, 0, ',', '.') }}</div></div>
                <div class="col-auto"><i class="fas fa-money-bill-wave fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Daftar Biaya</h6></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor</th>
                        <th>Pembuat</th>
                        <th>Approver</th>
                        <th>Penerima</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($biayas as $item)
                    <tr>
                        <td>{{ $item->tgl_transaksi->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('biaya.show', $item->id) }}">
                                <strong>{{ $item->custom_number }}</strong>
                            </a>
                        </td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->approver->name ?? '-' }}</td>
                        <td>{{ $item->penerima ?? '-' }}</td>
                        <td class="text-right font-weight-bold">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($item->status == 'Approved') <span class="badge badge-success">Approved</span>
                            @elseif($item->status == 'Pending') <span class="badge badge-warning">Pending</span>
                            @elseif($item->status == 'Canceled') <span class="badge badge-secondary">Canceled</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $role = auth()->user()->role; @endphp

                            {{-- APPROVE --}}
                            @if( ($role == 'super_admin' || ( $role == 'admin' && $item->approver_id == auth()->id() )) && $item->status == 'Pending')
                                <form action="{{ route('biaya.approve', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-circle btn-sm" title="Approve"><i class="fas fa-check"></i></button>
                                </form>
                            @endif

                            {{-- CANCEL --}}
                            @if(in_array($role, ['admin', 'super_admin']) && $item->status != 'Canceled')
                                <form action="{{ route('biaya.cancel', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Batalkan transaksi ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-dark btn-circle btn-sm" title="Cancel"><i class="fas fa-ban"></i></button>
                                </form>
                            @endif

                            {{-- DELETE --}}
                            @if($role == 'super_admin' || $item->status == 'Pending')
                                <button type="button" class="btn btn-danger btn-circle btn-sm" 
                                        data-toggle="modal" data-target="#deleteModal" data-action="{{ route('biaya.destroy', $item->id) }}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif

                            @if(!in_array($role, ['admin', 'super_admin']) && $item->status != 'Pending')
                                <span class="text-muted small">Terkunci</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center">Belum ada data biaya.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Yakin Hapus?</h5><button class="close" type="button" data-dismiss="modal"><span>×</span></button></div>
            <div class="modal-body">Data yang dihapus tidak bisa dikembalikan.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST">@csrf @method('DELETE') <button type="submit" class="btn btn-danger">Hapus</button></form>
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