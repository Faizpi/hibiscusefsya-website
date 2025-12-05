@extends('layouts.app')

@section('content')
    {{-- Chart Container Styles --}}
    <style>
        .chart-area {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .chart-pie {
            position: relative;
            height: 240px;
            width: 100%;
        }

        .chart-bar {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Samakan tinggi card chart */
        .row.chart-row {
            display: flex;
            align-items: stretch;
        }

        .row.chart-row>div {
            display: flex;
        }

        .row.chart-row .card {
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .row.chart-row .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    </style>

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

    {{-- ROW 1: Cards Utama --}}
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
                            <div class="text-xs text-muted mt-1">
                                {{ $penjualanCountBulanIni ?? 0 }} transaksi
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($pembelianNominalBulanIni ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                {{ $pembelianBulanIni }} transaksi
                            </div>
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
                            <div class="text-xs text-muted mt-1">
                                {{ $biayaCountBulanIni ?? 0 }} transaksi
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

    {{-- ROW 2: Cards Statistik Tambahan --}}
    <div class="row">
        {{-- Card Total Penjualan --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2" style="border-left-color: #6c5ce7 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6c5ce7;">
                                Total Penjualan (All Time)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($penjualanTotal ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Card Total Pembelian --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2" style="border-left-color: #00b894 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #00b894;">
                                Total Pembelian (All Time)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($pembelianTotal ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-chart-bar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Card Total Biaya --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2" style="border-left-color: #e17055 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #e17055;">
                                Total Biaya (All Time)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($biayaTotal ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-wallet fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Card Produk & Transaksi --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                @if(auth()->user()->role == 'user')
                                    Produk di Gudang Anda
                                @else
                                    Total Produk
                                @endif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProduk ?? 0 }}</div>
                            <div class="text-xs text-muted mt-1">
                                {{ $totalTransaksi ?? 0 }} total transaksi
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-boxes fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS SECTION (untuk Super Admin & Admin) --}}
    @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
        <div class="row chart-row">
            {{-- Line Chart: Tren 6 Bulan --}}
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line mr-2"></i>Tren Transaksi 6 Bulan Terakhir
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Doughnut Chart: Status Transaksi --}}
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie mr-2"></i>Komposisi Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div class="mt-3 text-center small">
                            <span class="mr-2">
                                <i class="fas fa-circle text-warning"></i> Pending
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-success"></i> Approved
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-secondary"></i> Canceled
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAR CHART: Transaksi per Gudang --}}
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-warehouse mr-2"></i>Transaksi per Gudang (Bulan Ini)
                        </h6>
                        <span class="text-muted small">Data transaksi Approved/Lunas</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar">
                            <canvas id="gudangChart"></canvas>
                        </div>
                        <div class="mt-3 text-center small">
                            <span class="mr-3">
                                <i class="fas fa-square" style="color: #4e73df;"></i> Penjualan
                            </span>
                            <span class="mr-3">
                                <i class="fas fa-square" style="color: #1cc88a;"></i> Pembelian
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                                                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}<br><small
                                                        class="text-muted">{{ $item->created_at->format('H:i') }}</small></td>
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
                                                <td>{{ $item->tgl_transaksi->format('d/m/Y') }}<br><small
                                                        class="text-muted">{{ $item->created_at->format('H:i') }}</small></td>
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
                                <option value="Lunas">Lunas</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Canceled">Canceled</option>
                            </select>
                        </div>

                        {{-- Gudang Filter --}}
                        @if(isset($gudangs) && $gudangs->count() > 0)
                            <div class="form-group" id="gudangFilterGroup">
                                <label for="gudang_id">Filter Gudang</label>
                                <select class="form-control" name="gudang_id" id="gudang_id">
                                    <option value="">Semua Gudang</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">*Filter gudang hanya berlaku untuk Penjualan dan Pembelian</small>
                            </div>
                        @endif
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
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    {{-- Chart Scripts --}}
    @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Data dari Controller
                const chartLabels = @json($chartLabels ?? []);
                const chartPenjualan = @json($chartPenjualan ?? []);
                const chartPembelian = @json($chartPembelian ?? []);
                const chartBiaya = @json($chartBiaya ?? []);

                const statusPending = {{ $statusPending ?? 0 }};
                const statusApproved = {{ $statusApproved ?? 0 }};
                const statusCanceled = {{ $statusCanceled ?? 0 }};

                // Helper format Rupiah
                function formatRupiah(value) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                }

                // LINE CHART: Tren 6 Bulan
                const trendCtx = document.getElementById('trendChart');
                if (trendCtx) {
                    new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: chartLabels,
                            datasets: [
                                {
                                    label: 'Penjualan',
                                    data: chartPenjualan,
                                    borderColor: 'rgb(78, 115, 223)',
                                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                                    tension: 0.3,
                                    fill: true
                                },
                                {
                                    label: 'Pembelian',
                                    data: chartPembelian,
                                    borderColor: 'rgb(28, 200, 138)',
                                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                                    tension: 0.3,
                                    fill: true
                                },
                                {
                                    label: 'Biaya',
                                    data: chartBiaya,
                                    borderColor: 'rgb(54, 185, 204)',
                                    backgroundColor: 'rgba(54, 185, 204, 0.1)',
                                    tension: 0.3,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            return context.dataset.label + ': ' + formatRupiah(context.parsed.y);
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            if (value >= 1000000) {
                                                return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                            } else if (value >= 1000) {
                                                return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                            }
                                            return 'Rp ' + value;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // DOUGHNUT CHART: Status Transaksi
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Pending', 'Approved', 'Canceled'],
                            datasets: [{
                                data: [statusPending, statusApproved, statusCanceled],
                                backgroundColor: [
                                    '#f6c23e', // warning - Pending
                                    '#1cc88a', // success - Approved
                                    '#858796'  // secondary - Canceled
                                ],
                                hoverBackgroundColor: [
                                    '#dda20a',
                                    '#17a673',
                                    '#6b6c77'
                                ],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            },
                            cutout: '60%'
                        }
                    });
                }

                // BAR CHART: Transaksi per Gudang
                const gudangLabels = @json($gudangLabels ?? []);
                const gudangPenjualan = @json($gudangPenjualan ?? []);
                const gudangPembelian = @json($gudangPembelian ?? []);

                const gudangCtx = document.getElementById('gudangChart');
                if (gudangCtx) {
                    new Chart(gudangCtx, {
                        type: 'bar',
                        data: {
                            labels: gudangLabels,
                            datasets: [
                                {
                                    label: 'Penjualan',
                                    data: gudangPenjualan,
                                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                                    borderColor: 'rgb(78, 115, 223)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Pembelian',
                                    data: gudangPembelian,
                                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                                    borderColor: 'rgb(28, 200, 138)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            return context.dataset.label + ': ' + formatRupiah(context.parsed.y);
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            if (value >= 1000000) {
                                                return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                            } else if (value >= 1000) {
                                                return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                            }
                                            return 'Rp ' + value;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endif

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

            // Toggle Gudang Filter visibility based on Transaction Type
            const transactionTypeSelect = document.getElementById('transaction_type');
            const gudangFilterGroup = document.getElementById('gudangFilterGroup');
            const gudangSelect = document.getElementById('gudang_id');

            if (transactionTypeSelect && gudangFilterGroup) {
                function toggleGudangFilter() {
                    const selectedType = transactionTypeSelect.value;
                    // Hide gudang filter if only "biaya" is selected
                    if (selectedType === 'biaya') {
                        gudangFilterGroup.style.display = 'none';
                        if (gudangSelect) gudangSelect.value = '';
                    } else {
                        gudangFilterGroup.style.display = 'block';
                    }
                }

                // Initial check
                toggleGudangFilter();

                // Listen to changes
                transactionTypeSelect.addEventListener('change', toggleGudangFilter);
            }
        });
    </script>
@endpush