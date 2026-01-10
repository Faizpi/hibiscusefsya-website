@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-2 mb-sm-0 text-gray-800">Penerimaan Barang (Barang Masuk)</h1>
        @if(auth()->user()->role !== 'spectator')
            <a href="{{ route('penerimaan-barang.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Buat Penerimaan Baru
            </a>
        @endif
    </div>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div> @endif

    {{-- ROW 1: Summary Cards --}}
    <div class="row">
        {{-- Card Total Bulan Ini --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Penerimaan Bulan Ini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBulanIni ?? 0 }} Transaksi</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-alt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Total 30 Hari --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">30 Hari Terakhir</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total30Hari ?? 0 }} Transaksi</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Total Approved --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Approved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalApproved ?? 0 }} Transaksi</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPending ?? 0 }} Transaksi</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Penerimaan Barang</h6>
            <span class="text-muted small">Total: {{ $penerimaans->total() }} data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor</th>
                            <th>Invoice Pembelian</th>
                            <th>No. Surat Jalan</th>
                            <th>Pembuat</th>
                            <th class="text-center">Jumlah Item</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penerimaans as $item)
                            <tr>
                                <td>{{ $item->tgl_penerimaan->format('d/m/Y') }}<br><small class="text-muted">{{ $item->created_at->format('H:i') }}</small></td>
                                <td>
                                    <a href="{{ route('penerimaan-barang.show', $item->id) }}">
                                        <strong>{{ $item->custom_number }}</strong>
                                    </a>
                                </td>
                                <td>
                                    @if($item->pembelian)
                                        <a href="{{ route('pembelian.show', $item->pembelian_id) }}">
                                            {{ $item->pembelian->nomor ?? $item->pembelian->custom_number }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $item->no_surat_jalan ?? '-' }}</td>
                                <td>{{ $item->user->name }}</td>
                                <td class="text-center">{{ $item->items->count() }} item</td>
                                <td class="text-center">
                                    @if($item->status == 'Approved') <span class="badge badge-success">Approved</span>
                                    @elseif($item->status == 'Pending') <span class="badge badge-warning">Pending</span>
                                    @elseif($item->status == 'Canceled') <span class="badge badge-secondary">Canceled</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php $role = auth()->user()->role; @endphp

                                    <div class="dropdown action-dropdown">
                                        <button class="btn btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                            {{-- VIEW --}}
                                            <a class="dropdown-item" href="{{ route('penerimaan-barang.show', $item->id) }}">
                                                <i class="fas fa-eye fa-fw mr-2 text-info"></i> Lihat Detail
                                            </a>

                                            {{-- APPROVE --}}
                                            @if(in_array($role, ['admin', 'super_admin']) && $item->status == 'Pending')
                                                <form action="{{ route('penerimaan-barang.approve', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-check fa-fw mr-2 text-success"></i> Approve
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- CANCEL --}}
                                            @if(in_array($role, ['admin', 'super_admin']) && $item->status != 'Canceled')
                                                @if($role == 'super_admin' || $item->status == 'Pending')
                                                    <button type="button" class="dropdown-item" data-toggle="modal"
                                                        data-target="#cancelModal" data-action="{{ route('penerimaan-barang.cancel', $item->id) }}">
                                                        <i class="fas fa-ban fa-fw mr-2 text-secondary"></i> Batalkan
                                                    </button>
                                                @endif
                                            @endif

                                            {{-- UNCANCEL --}}
                                            @if($item->status == 'Canceled' && $role == 'super_admin')
                                                <button type="button" class="dropdown-item" data-toggle="modal"
                                                    data-target="#uncancelModal" data-action="{{ route('penerimaan-barang.uncancel', $item->id) }}">
                                                    <i class="fas fa-undo fa-fw mr-2 text-info"></i> Batalkan Pembatalan
                                                </button>
                                            @endif

                                            {{-- DELETE --}}
                                            @if($role == 'super_admin')
                                                <div class="dropdown-divider"></div>
                                                <button type="button" class="dropdown-item text-danger" data-toggle="modal"
                                                    data-target="#deleteModal" data-action="{{ route('penerimaan-barang.destroy', $item->id) }}"
                                                    data-nomor="{{ $item->nomor }}">
                                                    <i class="fas fa-trash fa-fw mr-2"></i> Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data penerimaan barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $penerimaans->links() }}
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>menghapus</strong> penerimaan barang <strong id="deleteNomor"></strong>?</p>
                    <p class="text-warning"><small><i class="fas fa-exclamation-circle"></i> Jika sudah approved, stok yang telah ditambahkan akan dikurangi kembali.</small></p>
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

    {{-- Cancel Modal --}}
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Pembatalan</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>membatalkan</strong> transaksi ini?</p>
                    <p class="text-warning"><small><i class="fas fa-exclamation-circle"></i> Jika sudah approved, stok yang telah ditambahkan akan dikurangi kembali.</small></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Tidak</button>
                    <form id="cancelForm" method="POST">@csrf
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
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Tidak</button>
                    <form id="uncancelForm" method="POST">@csrf
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
        $(this).find('#deleteForm').attr('action', action);
        $(this).find('#deleteNomor').text(nomor);
    });
    $('#cancelModal').on('show.bs.modal', function (event) {
        var action = $(event.relatedTarget).data('action');
        $(this).find('#cancelForm').attr('action', action);
    });
    $('#uncancelModal').on('show.bs.modal', function (event) {
        var action = $(event.relatedTarget).data('action');
        $(this).find('#uncancelForm').attr('action', action);
    });
</script>
@endpush
