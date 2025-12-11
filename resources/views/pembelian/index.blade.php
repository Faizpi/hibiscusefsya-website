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
        {{-- Card 1: Pending Approval --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Approval</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($fakturPending, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Aktif --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total (Pending/Approved)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($fakturBelumDibayar, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Jatuh Tempo Lewat --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Jatuh Tempo Lewat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($fakturTelatBayar, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $fakturCanceled }} Transaksi</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-ban fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Permintaan Pembelian</h6>
            <span class="text-muted small">Total: {{ $pembelians->total() }} data</span>
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
                                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}<br><small
                                        class="text-muted">{{ $item->created_at->format('H:i') }}</small></td>
                                <td>
                                    <a href="{{ route('pembelian.show', $item->id) }}">
                                        <strong>{{ $item->custom_number }}</strong>
                                    </a>
                                </td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->status == 'Pending' ? '-' : ($item->approver->name ?? '-') }}</td>
                                <td>{{ $item->gudang->nama_gudang ?? '-' }}</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-center" style="white-space: nowrap;">
                                    @if($item->status == 'Approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($item->status == 'Pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($item->status == 'Canceled')
                                        <span class="badge badge-secondary">Canceled</span>
                                    @elseif($item->status == 'Lunas')
                                        <span class="badge badge-primary">Lunas</span>
                                    @else
                                        <span class="badge badge-info">{{ $item->status }}</span>
                                    @endif

                                    {{-- Indikator Telat --}}
                                    @if($item->status == 'Approved' && $item->tgl_jatuh_tempo && \Carbon\Carbon::parse($item->tgl_jatuh_tempo)->isPast())
                                        <span class="badge badge-danger">Telat</span>
                                    @endif
                                </td>
                                <td class="text-center" style="white-space: nowrap;">
                                    @php $role = auth()->user()->role; @endphp

                                    <div class="dropdown action-dropdown">
                                        <button class="btn btn-sm dropdown-toggle" type="button" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            {{-- VIEW --}}
                                            <a class="dropdown-item" href="{{ route('pembelian.show', $item->id) }}">
                                                <i class="fas fa-eye fa-fw mr-2 text-info"></i> Lihat Detail
                                            </a>

                                            {{-- APPROVE: Hanya jika Pending & Role Sesuai --}}
                                            @if($item->status == 'Pending')
                                                @php
                                                    $canApprove = false;
                                                    if ($role == 'super_admin') {
                                                        $canApprove = true;
                                                    } elseif ($role == 'admin') {
                                                        // Admin bisa approve jika punya akses ke gudang ini
                                                        $canApprove = auth()->user()->canAccessGudang($item->gudang_id);
                                                    }
                                                @endphp

                                                @if($canApprove)
                                                    <form action="{{ route('pembelian.approve', $item->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-check fa-fw mr-2 text-success"></i> Approve
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            {{-- CANCEL: Admin/Super Admin, jika belum Canceled --}}
                                            @if(in_array($role, ['admin', 'super_admin']) && $item->status != 'Canceled')
                                                <button type="button" class="dropdown-item" data-toggle="modal"
                                                    data-target="#cancelModal"
                                                    data-action="{{ route('pembelian.cancel', $item->id) }}">
                                                    <i class="fas fa-ban fa-fw mr-2 text-secondary"></i> Batalkan
                                                </button>
                                            @endif

                                            {{-- EDIT & DELETE --}}
                                            @php
                                                $canEdit = false;
                                                if (in_array($role, ['admin', 'super_admin']))
                                                    $canEdit = true;
                                                elseif ($item->user_id == auth()->id() && $item->status == 'Pending')
                                                    $canEdit = true;
                                            @endphp

                                            @if($canEdit)
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('pembelian.edit', $item->id) }}">
                                                    <i class="fas fa-pen fa-fw mr-2 text-warning"></i> Edit
                                                </a>
                                                <button type="button" class="dropdown-item text-danger" data-toggle="modal"
                                                    data-target="#deleteModal"
                                                    data-action="{{ route('pembelian.destroy', $item->id) }}">
                                                    <i class="fas fa-trash fa-fw mr-2"></i> Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </div>
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
            {{-- Pagination Links --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $pembelians->links() }}
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