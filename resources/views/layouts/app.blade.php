<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hibiscus Efsya</title>

    <link href="{{ asset('template/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('template/css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-bg: #ffffff;
            --sidebar-hover: #eff6ff;
            --sidebar-active: #3b82f6;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --bg-light: #f9fafb;
        }
        
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        body {
            background: var(--bg-light);
            overflow-x: hidden;
        }
        
        /* ========== WRAPPER ========== */
        #wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* ========== SIDEBAR - White Blue Style ========== */
        .sidebar {
            background: var(--sidebar-bg) !important;
            min-height: 100vh;
            width: 14rem !important;
            flex-shrink: 0;
            overflow-y: auto;
            overflow-x: hidden;
            border-right: 1px solid var(--border-color);
        }
        
        .sidebar .sidebar-brand {
            height: 65px;
            padding: 0 1rem;
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .sidebar .sidebar-brand-text {
            color: var(--sidebar-active);
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .sidebar .sidebar-brand-icon img {
            filter: none !important;
            height: 36px;
        }
        
        .sidebar .nav-item {
            margin: 2px 8px;
        }
        
        .sidebar .nav-item .nav-link {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            transition: all 0.15s ease;
            border-radius: 8px;
        }
        
        .sidebar .nav-item .nav-link i {
            width: 20px;
            font-size: 1rem;
            margin-right: 0.75rem;
            color: var(--text-muted);
        }
        
        .sidebar .nav-item .nav-link:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-active);
        }
        
        .sidebar .nav-item .nav-link:hover i {
            color: var(--sidebar-active);
        }
        
        .sidebar .nav-item.active .nav-link {
            background: var(--sidebar-hover);
            color: var(--sidebar-active);
            font-weight: 600;
        }
        
        .sidebar .nav-item.active .nav-link i {
            color: var(--sidebar-active);
        }
        
        .sidebar .sidebar-heading {
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1.25rem 1rem 0.5rem;
        }
        
        .sidebar hr.sidebar-divider {
            border-top: 1px solid var(--border-color);
            margin: 0.5rem 1rem;
        }
        
        .sidebar #sidebarToggle {
            background: var(--sidebar-hover);
            border: 1px solid var(--border-color);
        }
        
        .sidebar #sidebarToggle::after {
            color: var(--sidebar-active);
        }
        
        .sidebar #sidebarToggle:hover {
            background: var(--sidebar-active);
        }
        
        .sidebar #sidebarToggle:hover::after {
            color: #fff;
        }
        
        /* ========== TOPBAR ========== */
        .topbar {
            background: #fff;
            height: 65px;
            padding: 0 1.5rem;
            box-shadow: none;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .topbar .navbar-nav .nav-item .nav-link {
            color: var(--text-secondary);
            padding: 0.5rem;
        }
        
        .topbar .dropdown-toggle::after {
            display: none;
        }
        
        .topbar .user-info {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: background 0.15s ease;
        }
        
        .topbar .user-info:hover {
            background: var(--bg-light);
        }
        
        .topbar .user-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
            margin-right: 0.5rem;
        }
        
        .topbar .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        .topbar .img-profile {
            width: 36px;
            height: 36px;
            margin-left: 0.75rem;
        }
        
        .topbar .dropdown-menu {
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 0.5rem;
            min-width: 200px;
        }
        
        .topbar .dropdown-item {
            padding: 0.6rem 1rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            border-radius: 6px;
        }
        
        .topbar .dropdown-item:hover {
            background: var(--bg-light);
        }
        
        .topbar .dropdown-item i {
            width: 20px;
            color: var(--text-muted);
        }
        
        /* ========== CONTENT ========== */
        #content-wrapper {
            background: var(--bg-light);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
        }
        
        #content {
            padding-top: 0;
            flex: 1;
        }
        
        .container-fluid {
            padding: 1.5rem;
        }
        
        /* Page Title */
        .page-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
        }
        
        .h3.text-gray-800 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary) !important;
        }
        
        /* ========== CARDS ========== */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: none;
            background: #fff;
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
        }
        
        .card-header.bg-primary,
        .card-header.bg-gradient-primary {
            background: var(--sidebar-active) !important;
            border: none;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        /* ========== BUTTONS ========== */
        .btn {
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            transition: all 0.15s ease;
        }
        
        .btn-primary {
            background: var(--sidebar-active);
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-success {
            background: #10b981;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-danger {
            background: #ef4444;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: #fff;
        }
        
        .btn-warning:hover {
            background: #d97706;
            color: #fff;
        }
        
        .btn-secondary {
            background: #6b7280;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-outline-primary {
            border: 1px solid var(--sidebar-active);
            color: var(--sidebar-active);
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: var(--sidebar-active);
            color: #fff;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }
        
        /* ========== FORMS ========== */
        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            transition: border-color 0.15s ease;
            height: auto;
            min-height: 38px;
            line-height: 1.5;
        }
        
        .form-control:focus {
            border-color: var(--sidebar-active);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
        }
        
        /* Select / Dropdown */
        select.form-control {
            padding-right: 2rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 12px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        /* Select2 Styling */
        .select2-container--default .select2-selection--single {
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            height: 38px !important;
            padding: 0.25rem 0.5rem !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            padding-left: 0.25rem !important;
            color: var(--text-primary) !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: var(--text-muted) !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: var(--sidebar-active) !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1) !important;
        }
        
        .select2-dropdown {
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        
        .select2-results__option {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--sidebar-active) !important;
        }
        
        label {
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
        }
        
        /* ========== TABLES ========== */
        .table {
            font-size: 0.875rem;
        }
        
        .table thead th {
            background: var(--bg-light);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border-bottom: 1px solid var(--border-color);
            padding: 0.875rem 1rem;
        }
        
        .table tbody td {
            padding: 0.875rem 1rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background: var(--bg-light);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* ========== BADGES ========== */
        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
        }
        
        .badge-primary {
            background: rgba(59,130,246,0.1);
            color: var(--sidebar-active);
        }
        
        .badge-success {
            background: rgba(16,185,129,0.1);
            color: #059669;
        }
        
        .badge-danger {
            background: rgba(239,68,68,0.1);
            color: #dc2626;
        }
        
        .badge-warning {
            background: rgba(245,158,11,0.1);
            color: #d97706;
        }
        
        /* ========== ALERTS ========== */
        .alert {
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            padding: 0.875rem 1rem;
        }
        
        .alert-success {
            background: rgba(16,185,129,0.1);
            color: #059669;
        }
        
        .alert-danger {
            background: rgba(239,68,68,0.1);
            color: #dc2626;
        }
        
        .alert-warning {
            background: rgba(245,158,11,0.1);
            color: #d97706;
        }
        
        .alert-info {
            background: rgba(59,130,246,0.1);
            color: var(--sidebar-active);
        }
        
        /* ========== MODAL ========== */
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
        }
        
        .modal-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
        }
        
        .modal-body {
            padding: 1.25rem;
            color: var(--text-secondary);
        }
        
        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
        }
        
        /* ========== FOOTER ========== */
        .sticky-footer {
            background: #fff;
            border-top: 1px solid var(--border-color);
            padding: 1rem;
        }
        
        .sticky-footer .copyright {
            font-size: 0.8125rem;
            color: var(--text-muted);
        }
        
        /* ========== PAGINATION ========== */
        .pagination .page-link {
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 0.875rem;
            padding: 0.5rem 0.875rem;
        }
        
        .pagination .page-item.active .page-link {
            background: var(--sidebar-active);
            border-color: var(--sidebar-active);
        }
        
        .pagination .page-link:hover {
            background: var(--bg-light);
        }
        
        /* ========== UTILITIES ========== */
        .text-primary {
            color: var(--sidebar-active) !important;
        }
        
        .bg-primary {
            background: var(--sidebar-active) !important;
        }
        
        /* ========== SIDEBAR TOGGLED (Minimized) ========== */
        .sidebar.toggled {
            width: 6.5rem !important;
            overflow-x: visible;
            overflow-y: auto;
        }
        
        .sidebar.toggled .sidebar-brand {
            padding: 1.25rem 0;
            justify-content: center;
        }
        
        .sidebar.toggled .sidebar-brand .sidebar-brand-icon {
            margin: 0 auto;
        }
        
        .sidebar.toggled .sidebar-brand .sidebar-brand-icon img {
            height: 28px !important;
        }
        
        .sidebar.toggled .nav-item .nav-link {
            padding: 0.75rem;
            justify-content: center;
            text-align: center;
            overflow: visible;
        }
        
        .sidebar.toggled .nav-item .nav-link i {
            margin-right: 0;
            font-size: 1.1rem;
            width: auto;
        }
        
        .sidebar.toggled .nav-item .nav-link span {
            display: none;
        }
        
        .sidebar.toggled .sidebar-heading {
            display: none;
        }
        
        .sidebar.toggled hr.sidebar-divider {
            margin: 0.5rem 0.75rem;
        }
        
        /* Tooltip for minimized sidebar */
        .sidebar.toggled .nav-item {
            position: relative;
            overflow: visible;
        }
        
        .sidebar.toggled .nav-item .nav-link::after {
            content: attr(data-title);
            position: absolute;
            left: calc(100% + 0.5rem);
            top: 50%;
            transform: translateY(-50%);
            background: var(--sidebar-active);
            color: #fff;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: all 0.15s ease;
            z-index: 1100;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .sidebar.toggled .nav-item:hover .nav-link::after {
            opacity: 1;
            visibility: visible;
        }
        
        /* ========== TABLES RESPONSIVE ========== */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            min-width: 100%;
            white-space: nowrap;
        }
        
        .table td, .table th {
            white-space: normal;
            word-wrap: break-word;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .topbar .user-name,
            .topbar .user-role {
                display: none;
            }
            
            .sidebar {
                width: 6.5rem !important;
                overflow-x: visible;
            }
            
            .sidebar .sidebar-brand {
                padding: 1.25rem 0;
                justify-content: center;
            }
            
            .sidebar .nav-item .nav-link {
                padding: 0.75rem;
                justify-content: center;
                overflow: visible;
            }
            
            .sidebar .nav-item .nav-link i {
                margin-right: 0;
            }
            
            .sidebar .nav-item .nav-link span,
            .sidebar .sidebar-heading {
                display: none;
            }
            
            .container-fluid {
                padding: 1rem;
            }
        }
        
        /* Fix DataTables overflow */
        .dataTables_wrapper {
            overflow-x: auto;
        }
    </style>
</head>
<body id="page-top">
    @guest
        <div class="container">
            @yield('content')
        </div>
    @endguest

    @auth
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav sidebar accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon">
                    <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo" style="height: 36px;">
                </div>
            </a>

            <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}" data-title="Dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Transaksi</div>

            <li class="nav-item {{ Route::is('penjualan.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('penjualan.index') }}" data-title="Penjualan">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Penjualan</span>
                </a>
            </li>
            <li class="nav-item {{ Route::is('pembelian.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('pembelian.index') }}" data-title="Pembelian">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Pembelian</span>
                </a>
            </li>
            <li class="nav-item {{ Route::is('biaya.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('biaya.index') }}" data-title="Biaya">
                    <i class="fas fa-wallet"></i>
                    <span>Biaya</span>
                </a>
            </li>

            @if(auth()->user()->role == 'super_admin')
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Pengaturan</div>

                <li class="nav-item {{ Route::is('users.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('users.index') }}" data-title="Pengguna">
                        <i class="fas fa-users"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('gudang.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('gudang.index') }}" data-title="Gudang">
                        <i class="fas fa-warehouse"></i>
                        <span>Gudang</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('produk.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('produk.index') }}" data-title="Produk">
                        <i class="fas fa-box"></i>
                        <span>Produk</span>
                    </a>
                </li>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                @if(auth()->user()->role == 'admin') 
                    <hr class="sidebar-divider">
                    <div class="sidebar-heading">Master Data</div>
                @endif
                
                <li class="nav-item {{ Route::is('kontak.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('kontak.index') }}" data-title="Kontak">
                        <i class="fas fa-address-card"></i>
                        <span>Kontak</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('stok.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('stok.index') }}" data-title="Stok Gudang">
                        <i class="fas fa-boxes"></i>
                        <span>Stok Gudang</span>
                    </a>
                </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End Sidebar -->

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light topbar static-top">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle user-info" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="d-none d-lg-block text-right mr-2">
                                    <div class="user-name">{{ Auth::user()->name }}</div>
                                    <div class="user-role">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</div>
                                </div>
                                <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=3b82f6&color=fff&size=128">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Keluar
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End Topbar -->

                <!-- Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        &copy; {{ date('Y') }} Hibiscus Efsya. All rights reserved.
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Keluar dari Sistem</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Apakah Anda yakin ingin keluar?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <a class="btn btn-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-1"></i> Keluar
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <script src="{{ asset('template/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('template/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('template/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('template/js/sb-admin-2.min.js') }}"></script>

    @stack('scripts')
</body>
</html>
