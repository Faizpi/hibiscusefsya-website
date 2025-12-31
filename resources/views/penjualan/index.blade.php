@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-2 mb-sm-0 text-gray-800">Penjualan</h1>
        @if(auth()->user()->role !== 'spectator')
            <a href="{{ route('penjualan.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Buat Penagihan Baru
            </a>
        @endif
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
                                {{ number_format($totalBelumDibayar, 0, ',', '.') }}
                            </div>
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
                                {{ number_format($totalTelatDibayar, 0, ',', '.') }}
                            </div>
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
                                {{ number_format($pelunasan30Hari, 0, ',', '.') }}
                            </div>
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
                                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}<br><small
                                        class="text-muted">{{ $item->created_at->format('H:i') }}</small></td>
                                <td>
                                    <a href="{{ route('penjualan.show', $item->id) }}">
                                        {{-- Tampilkan Nomor Custom --}}
                                        <strong>{{ $item->custom_number }}</strong>
                                    </a>
                                </td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->status == 'Pending' ? '-' : ($item->approver->name ?? '-') }}</td>
                                <td>{{ $item->pelanggan }}</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-center" style="white-space: nowrap;">
                                    @if($item->status == 'Approved') <span class="badge badge-info">Approved</span>
                                    @elseif($item->status == 'Lunas') <span class="badge badge-success">Lunas</span>
                                    @elseif($item->status == 'Pending') <span class="badge badge-warning">Pending</span>
                                    @elseif($item->status == 'Canceled') <span class="badge badge-secondary">Canceled</span>
                                    @endif

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
                                            <a class="dropdown-item" href="{{ route('penjualan.show', $item->id) }}">
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
                                                        <button type="button" class="dropdown-item form-submit-btn"
                                                            data-action="{{ route('penjualan.approve', $item->id) }}"
                                                            data-method="POST">
                                                            <i class="fas fa-check fa-fw mr-2 text-success"></i> Approve
                                                        </button>
                                                    @endif
                                                @endif

                                                {{-- MARK PAID: Hanya jika Approved --}}
                                                @if($item->status == 'Approved')
                                                    <button type="button" class="dropdown-item form-submit-btn"
                                                        data-action="{{ route('penjualan.markAsPaid', $item->id) }}"
                                                        data-method="POST">
                                                        <i class="fas fa-dollar-sign fa-fw mr-2 text-primary"></i> Tandai Lunas
                                                    </button>
                                                @endif

                                                {{-- CANCEL: Jika belum Canceled, hanya super_admin bisa cancel Approved/Lunas --}}
                                                @if($item->status != 'Canceled')
                                                    @if($role == 'super_admin' || $item->status == 'Pending')
                                                        <button type="button" class="dropdown-item" data-toggle="modal"
                                                            data-target="#cancelModal"
                                                            data-action="{{ route('penjualan.cancel', $item->id) }}">
                                                            <i class="fas fa-ban fa-fw mr-2 text-secondary"></i> Batalkan
                                                        </button>
                                                    @endif
                                                @endif
                                            @endif

                                            {{-- EDIT & DELETE: Super Admin saja --}}
                                            @if($role == 'super_admin')
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

        // ============ FIX DROPDOWN DI TABEL ============
        $(document).ready(function () {
            // Buat overlay untuk mobile
            var $overlay = $('<div id="dropdown-overlay"></div>').css({
                'position': 'fixed',
                'top': 0,
                'left': 0,
                'right': 0,
                'bottom': 0,
                'background': 'rgba(0,0,0,0.3)',
                'z-index': 1050,
                'display': 'none'
            });
            $('body').append($overlay);

            // Click overlay to close
            $overlay.on('click', function () {
                $('.action-dropdown .dropdown-menu').removeClass('show').removeAttr('style');
                $('.action-dropdown').removeClass('show');
                $('.action-dropdown .dropdown-toggle').attr('aria-expanded', 'false');
                $overlay.hide();
            });

            // Override default dropdown behavior untuk action-dropdown di tabel
            $(document).on('click', '.action-dropdown .dropdown-toggle', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $toggle = $(this);
                var $dropdown = $toggle.closest('.action-dropdown');
                var $menu = $dropdown.find('.dropdown-menu');
                var isOpen = $menu.hasClass('show');

                // Tutup semua dropdown lain dulu
                $('.action-dropdown .dropdown-menu').removeClass('show').removeAttr('style');
                $('.action-dropdown').removeClass('show');
                $('.action-dropdown .dropdown-toggle').attr('aria-expanded', 'false');

                if (isOpen) {
                    // Tutup dropdown ini
                    $overlay.hide();
                    return;
                }

                // Buka dropdown ini
                $dropdown.addClass('show');
                $toggle.attr('aria-expanded', 'true');

                // Cek jika mobile
                var isMobile = $(window).width() <= 768;

                if (isMobile) {
                    // Mobile: tampilkan sebagai bottom sheet
                    $menu.addClass('show').css({
                        'position': 'fixed',
                        'bottom': '0',
                        'left': '0',
                        'right': '0',
                        'top': 'auto',
                        'width': '100%',
                        'max-width': '100%',
                        'border-radius': '16px 16px 0 0',
                        'padding': '16px',
                        'margin': '0',
                        'z-index': 1060,
                        'box-shadow': '0 -4px 20px rgba(0,0,0,0.15)',
                        'background': '#fff',
                        'transform': 'none'
                    });
                    $overlay.show();
                } else {
                    // Desktop: tampilkan di posisi relatif ke tombol
                    var offset = $toggle.offset();
                    var scrollTop = $(window).scrollTop();

                    $menu.addClass('show').css({
                        'position': 'fixed',
                        'top': (offset.top - scrollTop + $toggle.outerHeight() + 4) + 'px',
                        'left': 'auto',
                        'right': ($(window).width() - offset.left - $toggle.outerWidth()) + 'px',
                        'bottom': 'auto',
                        'z-index': 1060,
                        'transform': 'none'
                    });
                }
            });

            // Tutup dropdown saat klik di luar
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.action-dropdown').length) {
                    $('.action-dropdown .dropdown-menu').removeClass('show').removeAttr('style');
                    $('.action-dropdown').removeClass('show');
                    $('.action-dropdown .dropdown-toggle').attr('aria-expanded', 'false');
                    $overlay.hide();
                }
            });

            // Tutup dropdown saat scroll tabel
            $('.table-responsive').on('scroll', function () {
                $('.action-dropdown .dropdown-menu').removeClass('show').removeAttr('style');
                $('.action-dropdown').removeClass('show');
                $('.action-dropdown .dropdown-toggle').attr('aria-expanded', 'false');
                $overlay.hide();
            });

            // Handle form-submit-btn clicks
            $(document).on('click', '.form-submit-btn', function (e) {
                e.preventDefault();
                var action = $(this).data('action');
                var method = $(this).data('method') || 'POST';

                // Create a temporary form and submit
                var form = $('<form>', {
                    method: 'POST',
                    action: action,
                    style: 'display:none'
                });

                // Add CSRF token
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: $('meta[name="csrf-token"]').attr('content')
                }));

                // Add method override if needed
                if (method !== 'POST') {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: '_method',
                        value: method
                    }));
                }

                $('body').append(form);
                form.submit();
            });
        });
    </script>
@endpush