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
        {{-- Card 1: Total Pending/Approved --}}
        <div class="col-xl-3 col-md-6 mb-4">
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

        {{-- Card 2: Jatuh Tempo Lewat --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Jatuh Tempo Lewat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($totalTelatDibayar, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Pelunasan 30 Hari --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Lunas (30 Hari Terakhir)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($pelunasan30Hari, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Canceled --}}
        <div class="col-xl-3 col-md-6 mb-4">
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
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Penjualan</h6>
            <span class="text-muted small">Total: {{ $penjualans->total() }} data</span>
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

                                    <div class="dropdown action-dropdown">
                                        <button class="btn btn-sm dropdown-toggle" type="button" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            {{-- VIEW --}}
                                            <a class="dropdown-item" href="{{ route('penjualan.show', $item->id) }}">
                                                <i class="fas fa-eye fa-fw mr-2 text-info"></i> Lihat Detail
                                            </a>

                                            @if(in_array($role, ['admin', 'super_admin']))
                                                {{-- APPROVE: Hanya jika Pending --}}
                                                @if($item->status == 'Pending')
                                                    @if($role == 'super_admin' || $item->approver_id == auth()->id())
                                                        <form action="{{ route('penjualan.approve', $item->id) }}" method="POST"
                                                            class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-check fa-fw mr-2 text-success"></i> Approve
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif

                                                {{-- MARK PAID: Hanya jika Approved --}}
                                                @if($item->status == 'Approved')
                                                    <form action="{{ route('penjualan.markAsPaid', $item->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-dollar-sign fa-fw mr-2 text-primary"></i> Tandai Lunas
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- CANCEL: Jika belum Canceled --}}
                                                @if($item->status != 'Canceled')
                                                    <button type="button" class="dropdown-item" data-toggle="modal"
                                                        data-target="#cancelModal"
                                                        data-action="{{ route('penjualan.cancel', $item->id) }}">
                                                        <i class="fas fa-ban fa-fw mr-2 text-secondary"></i> Batalkan
                                                    </button>
                                                @endif
                                            @endif

                                            {{-- EDIT & DELETE: Super Admin kapan saja, sisanya jika Pending --}}
                                            @if($role == 'super_admin' || $item->status == 'Pending')
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('penjualan.edit', $item->id) }}">
                                                    <i class="fas fa-pen fa-fw mr-2 text-warning"></i> Edit
                                                </a>
                                                <button type="button" class="dropdown-item text-danger" data-toggle="modal"
                                                    data-target="#deleteModal"
                                                    data-action="{{ route('penjualan.destroy', $item->id) }}">
                                                    <i class="fas fa-trash fa-fw mr-2"></i> Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </div>
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
            {{-- Pagination Links --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $penjualans->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>menghapus</strong> data ini?</p>
                    <p class="text-muted mb-0"><small>Data yang dihapus tidak dapat dikembalikan.</small></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST">@csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Pembatalan</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>membatalkan</strong> transaksi ini?</p>
                    <p class="text-muted mb-0"><small>Transaksi yang dibatalkan tidak dapat diproses kembali.</small></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Tidak</button>
                    <form id="cancelForm" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning">Ya, Batalkan</button>
                    </form>
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

        $('#cancelModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var action = button.data('action');
            var modal = $(this);
            modal.find('#cancelForm').attr('action', action);
        });
    </script>
@endpush