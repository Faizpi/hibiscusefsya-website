@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-2 mb-sm-0 text-gray-800">Biaya</h1>
        @if(auth()->user()->role !== 'spectator')
            <a href="{{ route('biaya.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Buat Biaya Baru
            </a>
        @endif
    </div>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div> @endif

    {{-- ROW 1: Cards Biaya Masuk/Keluar --}}
    <div class="row">
        {{-- Card Biaya Masuk --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Biaya Masuk (Approved)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($totalBiayaMasuk ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-arrow-down fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Biaya Keluar --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Biaya Keluar (Approved)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($totalBiayaKeluar ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-arrow-up fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Total Bulan Ini --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Bulan Ini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($totalBulanIni, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-alt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Pending Approval --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Approval</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($totalBelumDibayar, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Biaya</h6>
            <div class="d-flex align-items-center">
                {{-- Filter Jenis Biaya --}}
                <form method="GET" class="mr-3">
                    <select name="jenis" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua Jenis</option>
                        <option value="masuk" {{ request('jenis') == 'masuk' ? 'selected' : '' }}>Biaya Masuk</option>
                        <option value="keluar" {{ request('jenis') == 'keluar' ? 'selected' : '' }}>Biaya Keluar</option>
                    </select>
                </form>
                <span class="text-muted small">Total: {{ $biayas->total() }} data</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor</th>
                            <th>Jenis</th>
                            <th>Pembuat</th>
                            <th>Penerima</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($biayas as $item)
                            <tr>
                                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}<br><small
                                        class="text-muted">{{ $item->created_at->format('H:i') }}</small></td>
                                <td>
                                    <a href="{{ route('biaya.show', $item->id) }}">
                                        <strong>{{ $item->custom_number }}</strong>
                                    </a>
                                </td>
                                <td>
                                    @if($item->jenis_biaya == 'masuk')
                                        <span class="badge badge-success">Masuk</span>
                                    @else
                                        <span class="badge badge-danger">Keluar</span>
                                    @endif
                                </td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->penerima ?? '-' }}</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($item->status == 'Approved') <span class="badge badge-success">Approved</span>
                                    @elseif($item->status == 'Pending') <span class="badge badge-warning">Pending</span>
                                    @elseif($item->status == 'Canceled') <span class="badge badge-secondary">Canceled</span>
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
                                            <a class="dropdown-item" href="{{ route('biaya.show', $item->id) }}">
                                                <i class="fas fa-eye fa-fw mr-2 text-info"></i> Lihat Detail
                                            </a>

                                            {{-- APPROVE --}}
                                            @php
                                                $canApprove = false;
                                                if ($role == 'super_admin') {
                                                    $canApprove = true;
                                                } elseif ($role == 'admin' && auth()->user()->canAccessGudang($item->gudang_id)) {
                                                    $canApprove = true;
                                                }
                                            @endphp
                                            @if($canApprove && $item->status == 'Pending')
                                                <form action="{{ route('biaya.approve', $item->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-check fa-fw mr-2 text-success"></i> Approve
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- CANCEL: Hanya super_admin bisa cancel Approved, admin hanya Pending --}}
                                            @if(in_array($role, ['admin', 'super_admin']) && $item->status != 'Canceled')
                                                @if($role == 'super_admin' || $item->status == 'Pending')
                                                    <button type="button" class="dropdown-item" data-toggle="modal"
                                                        data-target="#cancelModal" data-action="{{ route('biaya.cancel', $item->id) }}">
                                                        <i class="fas fa-ban fa-fw mr-2 text-secondary"></i> Batalkan
                                                    </button>
                                                @endif
                                            @endif

                                            {{-- UNCANCEL: Jika Canceled, super_admin bisa uncancel --}}
                                            @if($item->status == 'Canceled' && $role == 'super_admin')
                                                <button type="button" class="dropdown-item" data-toggle="modal"
                                                    data-target="#uncancelModal"
                                                    data-action="{{ route('biaya.uncancel', $item->id) }}">
                                                    <i class="fas fa-undo fa-fw mr-2 text-info"></i> Batalkan Pembatalan
                                                </button>
                                            @endif

                                            {{-- EDIT & DELETE: Super Admin saja --}}
                                            @if($role == 'super_admin')
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('biaya.edit', $item->id) }}">
                                                    <i class="fas fa-pen fa-fw mr-2 text-warning"></i> Edit
                                                </a>
                                                <button type="button" class="dropdown-item text-danger" data-toggle="modal"
                                                    data-target="#deleteModal" data-action="{{ route('biaya.destroy', $item->id) }}"
                                                    data-nomor="{{ $item->custom_number ?? $item->id }}">
                                                    <i class="fas fa-trash fa-fw mr-2"></i> Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data biaya.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination Links --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $biayas->links() }}
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
                    <p>Apakah Anda yakin ingin <strong>menghapus</strong> data biaya <strong id="deleteNomor"></strong>?</p>
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

    {{-- Uncancel Modal --}}
    <div class="modal fade" id="uncancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-undo mr-2"></i>Konfirmasi Batalkan Pembatalan</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>membatalkan pembatalan</strong> transaksi ini?</p>
                    <p class="text-muted mb-0"><small>Status transaksi akan kembali ke <strong>Pending</strong> dan perlu
                            disetujui ulang.</small></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Tidak</button>
                    <form id="uncancelForm" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info">Ya, Batalkan Pembatalan</button>
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
            var nomor = button.data('nomor');
            var modal = $(this);
            modal.find('#deleteForm').attr('action', action);
            modal.find('#deleteNomor').text(nomor);
        });

        $('#cancelModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var action = button.data('action');
            var modal = $(this);
            modal.find('#cancelForm').attr('action', action);
        });

        $('#uncancelModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var action = button.data('action');
            var modal = $(this);
            modal.find('#uncancelForm').attr('action', action);
        });
    </script>
@endpush