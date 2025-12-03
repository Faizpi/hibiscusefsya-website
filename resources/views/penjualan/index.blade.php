@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-2 mb-sm-0 text-gray-800">Penjualan</h1>
        <a href="{{ route('penjualan.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Penagihan Baru
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div> @endif

    {{-- Kartu Ringkasan --}}
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total (Pending/Approved)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($totalBelumDibayar, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCanceled }} Transaksi</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-ban fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Penjualan</h6>
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
                            <th>Pelanggan</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penjualans as $item)
                            <tr>
                                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('penjualan.show', $item->id) }}">
                                        {{-- Tampilkan Nomor Custom --}}
                                        <strong>{{ $item->custom_number }}</strong>
                                    </a>
                                </td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->approver->name ?? '-' }}</td>
                                <td>{{ $item->pelanggan }}</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($item->status == 'Approved') <span class="badge badge-info">Approved (Belum
                                        Bayar)</span>
                                    @elseif($item->status == 'Lunas') <span class="badge badge-success">Lunas</span>
                                    @elseif($item->status == 'Pending') <span class="badge badge-warning">Pending</span>
                                    @elseif($item->status == 'Canceled') <span class="badge badge-secondary">Canceled</span>
                                    @endif

                                    @if($item->status == 'Approved' && $item->tgl_jatuh_tempo && \Carbon\Carbon::parse($item->tgl_jatuh_tempo)->isPast())
                                        <br><span class="badge badge-danger mt-1">Telat</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php $role = auth()->user()->role; @endphp

                                    @if(in_array($role, ['admin', 'super_admin']))

                                        {{-- APPROVE: Hanya jika Pending --}}
                                        @if($item->status == 'Pending')
                                            {{-- Admin biasa hanya bisa approve jika dia yg ditunjuk --}}
                                            @if($role == 'super_admin' || $item->approver_id == auth()->id())
                                                <form action="{{ route('penjualan.approve', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-circle btn-sm" title="Approve"><i
                                                            class="fas fa-check"></i></button>
                                                </form>
                                            @endif
                                        @endif

                                        {{-- MARK PAID: Hanya jika Approved --}}
                                        @if($item->status == 'Approved')
                                            <form action="{{ route('penjualan.markAsPaid', $item->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-circle btn-sm" title="Tandai Lunas"><i
                                                        class="fas fa-dollar-sign"></i></button>
                                            </form>
                                        @endif

                                        {{-- CANCEL: Jika belum Canceled --}}
                                        @if($item->status != 'Canceled')
                                            <form action="{{ route('penjualan.cancel', $item->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Batalkan transaksi ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-dark btn-circle btn-sm" title="Cancel"><i
                                                        class="fas fa-ban"></i></button>
                                            </form>
                                        @endif

                                    @endif

                                    {{-- DELETE: Super Admin kapan saja, sisanya jika Pending --}}
                                    @if($role == 'super_admin' || $item->status == 'Pending')
                                        <a href="{{ route('penjualan.edit', $item->id) }}"
                                            class="btn btn-warning btn-circle btn-sm"><i class="fas fa-pen"></i></a>
                                        <button type="button" class="btn btn-danger btn-circle btn-sm" data-toggle="modal"
                                            data-target="#deleteModal" data-action="{{ route('penjualan.destroy', $item->id) }}"><i
                                                class="fas fa-trash"></i></button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data.</td>
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