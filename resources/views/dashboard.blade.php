@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-2 mb-sm-0 text-gray-800">Dashboard</h1>

        {{-- Tombol Export hanya untuk Admin/Super Admin --}}
        @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
            <div>
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#exportModal">
                    <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
                </button>
            </div>
        @endif
    </div>

    <div class="row">
        {{-- Card Penjualan --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Penjualan (Bulan Ini)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($penjualanBulanIni, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-shopping-cart fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Card Pembelian --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Pembelian (Bulan Ini)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pembelianBulanIni }} Transaksi</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-box-open fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Card Biaya --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Biaya (Bulan Ini)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($biayaBulanIni, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-receipt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Card Ke-4 Dinamis --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ $card_4_title }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $card_4_value }}</div>
                        </div>
                        <div class="col-auto"><i class="fas {{ $card_4_icon }} fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if(auth()->user()->role == 'super_admin')
            {{-- SUPER ADMIN: Lihat semua aktivitas --}}
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Semua Aktivitas Transaksi</h6>
                        <div class="d-flex align-items-center">
                            @if(isset($allTransactions))
                                <span class="text-muted small mr-3">Total: {{ $allTransactions->total() }} data</span>
                            @endif
                            <div class="col-auto">
                                <input type="text" class="form-control form-control-sm" id="adminSearchInput"
                                    placeholder="Cari data..." style="width: 200px;">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="adminMasterTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Tipe</th>
                                        <th>Nomor</th>
                                        <th>Tanggal</th>
                                        <th>Pembuat</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="adminMasterTableBody">
                                    @if(isset($allTransactions))
                                        @forelse($allTransactions as $item)
                                            <tr>
                                                <td>
                                                    @if($item->type == 'Penjualan')
                                                        <span class="badge badge-primary">Penjualan</span>
                                                    @elseif($item->type == 'Pembelian')
                                                        <span class="badge badge-success">Pembelian</span>
                                                    @else
                                                        <span class="badge badge-info">Biaya</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ $item->route }}"><strong>{{ $item->number }}</strong></a>
                                                </td>
                                                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}</td>
                                                <td>{{ $item->user->name }}</td>
                                                <td class="text-center">
                                                    @if($item->status == 'Approved')
                                                        <span class="badge badge-success">{{ $item->status }}</span>
                                                    @elseif($item->status == 'Pending')
                                                        <span class="badge badge-warning">{{ $item->status }}</span>
                                                    @elseif($item->status == 'Canceled')
                                                        <span class="badge badge-secondary">{{ $item->status }}</span>
                                                    @else
                                                        <span class="badge badge-danger">{{ $item->status }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @if(isset($item->grand_total))
                                                        Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada transaksi sama sekali.</td>
                                            </tr>
                                        @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        {{-- Pagination Links --}}
                        @if(isset($allTransactions) && $allTransactions->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $allTransactions->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        @elseif(auth()->user()->role == 'admin')
            {{-- ADMIN: Hanya lihat transaksi yang perlu approval --}}
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Transaksi Menunggu Approval</h6>
                        <div class="d-flex align-items-center">
                            @if(isset($allTransactions))
                                <span class="text-muted small mr-3">Total: {{ $allTransactions->total() }} data</span>
                            @endif
                            <div class="col-auto">
                                <input type="text" class="form-control form-control-sm" id="adminSearchInput"
                                    placeholder="Cari data..." style="width: 200px;">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="adminMasterTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Tipe</th>
                                        <th>Nomor</th>
                                        <th>Tanggal</th>
                                        <th>Pembuat</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="adminMasterTableBody">
                                    @if(isset($allTransactions))
                                        @forelse($allTransactions as $item)
                                            <tr>
                                                <td>
                                                    @if($item->type == 'Penjualan')
                                                        <span class="badge badge-primary">Penjualan</span>
                                                    @elseif($item->type == 'Pembelian')
                                                        <span class="badge badge-success">Pembelian</span>
                                                    @else
                                                        <span class="badge badge-info">Biaya</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ $item->route }}"><strong>{{ $item->number }}</strong></a>
                                                </td>
                                                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}</td>
                                                <td>{{ $item->user->name }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-warning">{{ $item->status }}</span>
                                                </td>
                                                <td class="text-right">
                                                    @if(isset($item->grand_total))
                                                        Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Tidak ada transaksi yang menunggu approval.</td>
                                            </tr>
                                        @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        {{-- Pagination Links --}}
                        @if(isset($allTransactions) && $allTransactions->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $allTransactions->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        @else

            {{-- TAMPILAN UNTUK USER BIASA: WELCOME CARD --}}
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Selamat Datang, {{ Auth::user()->name }}!</h6>
                    </div>
                    <div class="card-body">
                        <p>Anda login sebagai User (Staf). Semua data yang Anda buat (Biaya, Penjualan, Pembelian) akan
                            memerlukan persetujuan dari Admin sebelum diproses.</p>
                        <p>Anda dapat melihat status data yang Anda ajukan di masing-masing menu sidebar.</p>
                    </div>
                </div>
            </div>

        @endif
    </div>

    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">
                        <i class="fas fa-file-excel text-success mr-2"></i>Generate Report
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('report.export') }}" method="GET">
                    <div class="modal-body">
                        {{-- Info Role --}}
                        <div class="alert alert-info small mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            @if(auth()->user()->role == 'super_admin')
                                <strong>Super Admin:</strong> Anda dapat export semua data transaksi.
                            @else
                                <strong>Admin:</strong> Anda hanya dapat export data dimana Anda sebagai approver.
                            @endif
                        </div>

                        {{-- Tipe Transaksi --}}
                        <div class="form-group">
                            <label for="transaction_type"><strong>Tipe Transaksi</strong></label>
                            <select class="form-control" name="transaction_type" id="transaction_type" required>
                                <option value="all">Semua Transaksi</option>
                                <option value="penjualan">Penjualan</option>
                                <option value="pembelian">Pembelian</option>
                                <option value="biaya">Biaya</option>
                            </select>
                        </div>

                        <hr>

                        {{-- Rentang Tanggal --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_from">Dari Tanggal</label>
                                    <input type="date" class="form-control" name="date_from" id="date_from" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_to">Sampai Tanggal</label>
                                    <input type="date" class="form-control" name="date_to" id="date_to" required>
                                </div>
                            </div>
                        </div>

                        {{-- Status Filter --}}
                        <div class="form-group">
                            <label for="status_filter">Filter Status</label>
                            <select class="form-control" name="status_filter" id="status_filter">
                                <option value="all">Semua Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Canceled">Canceled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download mr-1"></i> Export ke Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- Script untuk FUNGSI SEARCH --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('adminSearchInput');
            const tableBody = document.getElementById('adminMasterTableBody');

            if (searchInput) {
                searchInput.addEventListener('keyup', function () {
                    const filter = searchInput.value.toUpperCase();
                    const rows = tableBody.getElementsByTagName('tr');

                    for (let i = 0; i < rows.length; i++) {
                        const cells = rows[i].getElementsByTagName('td');
                        let found = false;
                        for (let j = 0; j < cells.length; j++) {
                            const cell = cells[j];
                            if (cell) {
                                const txtValue = cell.textContent || cell.innerText;
                                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                                    found = true;
                                    break;
                                }
                            }
                        }
                        if (found) {
                            rows[i].style.display = "";
                        } else {
                            rows[i].style.display = "none";
                        }
                    }
                });
            }
        });
    </script>
@endpush