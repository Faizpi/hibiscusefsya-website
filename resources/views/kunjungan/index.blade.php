@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-2 mb-sm-0 text-gray-800">Kunjungan</h1>
        @if(auth()->user()->role !== 'spectator')
            <a href="{{ route('kunjungan.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Buat Kunjungan Baru
            </a>
        @endif
    </div>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div> @endif

    {{-- ROW 1: Cards Summary --}}
    <div class="row">
        {{-- Card Pemeriksaan Stock --}}
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pemeriksaan Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPemeriksaanStock }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clipboard-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Penagihan --}}
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Penagihan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPenagihan }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Promo --}}
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Promo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPromo }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-tags fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Canceled --}}
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Dibatalkan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCanceled }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kunjungan</h6>
            <div class="d-flex align-items-center">
                {{-- Filter Tujuan --}}
                <form method="GET" class="mr-3">
                    <select name="tujuan" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua Tujuan</option>
                        <option value="Pemeriksaan Stock" {{ request('tujuan') == 'Pemeriksaan Stock' ? 'selected' : '' }}>
                            Pemeriksaan Stock</option>
                        <option value="Penagihan" {{ request('tujuan') == 'Penagihan' ? 'selected' : '' }}>Penagihan</option>
                        <option value="Promo" {{ request('tujuan') == 'Promo' ? 'selected' : '' }}>Promo</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No Kunjungan</th>
                            <th>Sales/Kontak</th>
                            <th>Tujuan</th>
                            <th>Gudang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $role = auth()->user()->role; @endphp
                        @forelse($kunjungans as $item)
                            <tr>
                                <td>{{ $item->tgl_kunjungan->format('d M Y') }}<br><small
                                        class="text-muted">{{ $item->created_at->format('H:i') }}</small></td>
                                <td>
                                    <a href="{{ route('kunjungan.show', $item->id) }}">
                                        <strong>{{ $item->custom_number }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $item->sales_nama }}</strong>
                                    @if($item->sales_email)
                                        <br><small class="text-muted">{{ $item->sales_email }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->tujuan == 'Pemeriksaan Stock')
                                        <span class="badge badge-info">{{ $item->tujuan }}</span>
                                    @elseif($item->tujuan == 'Penagihan')
                                        <span class="badge badge-warning">{{ $item->tujuan }}</span>
                                    @elseif($item->tujuan == 'Promo')
                                        <span class="badge badge-primary">{{ $item->tujuan }}</span>
                                    @else
                                        <span class="badge badge-success">{{ $item->tujuan }}</span>
                                    @endif
                                </td>
                                <td>{{ optional($item->gudang)->nama_gudang ?? '-' }}</td>
                                <td>
                                    @if($item->status == 'Approved')
                                        <span class="badge badge-success">{{ $item->status }}</span>
                                    @elseif($item->status == 'Pending')
                                        <span class="badge badge-warning">{{ $item->status }}</span>
                                    @elseif($item->status == 'Canceled')
                                        <span class="badge badge-secondary">Canceled</span>
                                    @else
                                        <span class="badge badge-danger">{{ $item->status }}</span>
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
                                            <a class="dropdown-item" href="{{ route('kunjungan.show', $item->id) }}">
                                                <i class="fas fa-eye fa-fw mr-2 text-info"></i> Lihat Detail
                                            </a>

                                            @if(in_array($role, ['admin', 'super_admin']))
                                                {{-- APPROVE: Hanya jika Pending --}}
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
                                                        <form action="{{ route('kunjungan.approve', $item->id) }}" method="POST"
                                                            class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-check fa-fw mr-2 text-success"></i> Approve
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif

                                                {{-- CANCEL: Jika belum Canceled, hanya super_admin bisa cancel Approved --}}
                                                @if($item->status != 'Canceled')
                                                    @if($role == 'super_admin' || $item->status == 'Pending')
                                                        <button type="button" class="dropdown-item" data-toggle="modal"
                                                            data-target="#cancelModal"
                                                            data-action="{{ route('kunjungan.cancel', $item->id) }}">
                                                            <i class="fas fa-ban fa-fw mr-2 text-secondary"></i> Batalkan
                                                        </button>
                                                    @endif
                                                @endif

                                                {{-- UNCANCEL: Jika Canceled, super_admin bisa uncancel --}}
                                                @if($item->status == 'Canceled' && $role == 'super_admin')
                                                    <button type="button" class="dropdown-item" data-toggle="modal"
                                                        data-target="#uncancelModal"
                                                        data-action="{{ route('kunjungan.uncancel', $item->id) }}">
                                                        <i class="fas fa-undo fa-fw mr-2 text-info"></i> Batalkan Pembatalan
                                                    </button>
                                                @endif
                                            @endif

                                            {{-- EDIT & DELETE: Super Admin saja --}}
                                            @if($role == 'super_admin')
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('kunjungan.edit', $item->id) }}">
                                                    <i class="fas fa-pen fa-fw mr-2 text-warning"></i> Edit
                                                </a>
                                                <button type="button" class="dropdown-item text-danger" data-toggle="modal"
                                                    data-target="#deleteModal"
                                                    data-action="{{ route('kunjungan.destroy', $item->id) }}"
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
                                <td colspan="7" class="text-center text-muted">Belum ada data kunjungan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $kunjungans->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Delete --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>menghapus</strong> data kunjungan <strong id="deleteNomor"></strong>?
                    </p>
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

    {{-- Modal Cancel --}}
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Pembatalan</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>membatalkan</strong> kunjungan ini?</p>
                    <p class="text-muted mb-0"><small>Kunjungan yang dibatalkan tidak dapat diproses kembali.</small></p>
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

    {{-- Modal Uncancel --}}
    <div class="modal fade" id="uncancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-undo mr-2"></i>Konfirmasi Batalkan Pembatalan</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>membatalkan pembatalan</strong> kunjungan ini?</p>
                    <p class="text-muted mb-0"><small>Status kunjungan akan kembali ke <strong>Pending</strong> dan perlu
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