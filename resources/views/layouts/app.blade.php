<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hibiscus Efsya Jurnal</title>

    <link href="{{ asset('template/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('template/css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        /* ========== SIDEBAR ========== */
        .sidebar {
            background: #ffffff !important;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }
        
        .sidebar .sidebar-brand {
            background: #4e73df;
            padding: 1.2rem;
        }
        
        .sidebar .nav-item .nav-link {
            color: #6c757d;
            font-weight: 500;
            padding: 0.8rem 1rem;
            margin: 2px 10px;
            border-radius: 8px;
        }
        
        .sidebar .nav-item .nav-link i {
            color: #adb5bd;
        }
        
        .sidebar .nav-item .nav-link:hover {
            background: #f0f4ff;
            color: #4e73df;
        }
        
        .sidebar .nav-item .nav-link:hover i {
            color: #4e73df;
        }
        
        .sidebar .nav-item.active .nav-link {
            background: #4e73df;
            color: #ffffff;
        }
        
        .sidebar .nav-item.active .nav-link i {
            color: #ffffff;
        }
        
        .sidebar .sidebar-heading {
            color: #4e73df;
            font-weight: 600;
            font-size: 0.7rem;
            padding: 1rem 1.2rem 0.5rem;
        }
        
        .sidebar .sidebar-divider {
            border-color: #eee;
            margin: 0.5rem 1rem;
        }
        
        .sidebar #sidebarToggle {
            background: #4e73df;
        }
        
        /* ========== NAVBAR ========== */
        .topbar {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .topbar .nav-link {
            color: #6c757d;
        }
        
        .topbar .dropdown-menu {
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        .topbar .dropdown-item {
            padding: 0.6rem 1rem;
            font-weight: 500;
        }
        
        .topbar .dropdown-item:hover {
            background: #f0f4ff;
            color: #4e73df;
        }
        
        .topbar .img-profile {
            width: 36px;
            height: 36px;
            border: 2px solid #4e73df;
        }
        
        .user-role-badge {
            background: #4e73df;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            margin-left: 0.5rem;
        }
        
        /* ========== CONTENT ========== */
        #content-wrapper {
            background: #f8f9fc;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        
        .btn-primary {
            background: #4e73df;
            border: none;
        }
        
        .btn-primary:hover {
            background: #3a5bc7;
        }
        
        .btn-success {
            background: #1cc88a;
            border: none;
        }
        
        .btn-danger {
            background: #e74a3b;
            border: none;
        }
        
        .btn-warning {
            background: #f6c23e;
            border: none;
        }
        
        .btn-info {
            background: #36b9cc;
            border: none;
        }
        
        /* Forms */
        .form-control {
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 0.6rem 0.8rem;
        }
        
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 2px rgba(78,115,223,0.1);
        }
        
        /* Tables */
        .table thead th {
            background: #4e73df;
            color: #fff;
            font-weight: 600;
            border: none;
        }
        
        .table tbody tr:hover {
            background: #f8f9fc;
        }
        
        /* Footer */
        .sticky-footer {
            background: #fff;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.03);
        }
        
        .sticky-footer .copyright {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        /* Modal */
        .modal-content {
            border: none;
            border-radius: 10px;
        }
        
        .modal-header {
            background: #4e73df;
            color: #fff;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-header .close {
            color: #fff;
        }
        
        /* Alerts */
        .alert {
            border: none;
            border-radius: 8px;
        }
        
        /* Page Title */
        .h3.text-gray-800 {
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body id="page-top" class="{{ Request::is('login') || Request::is('register') ? 'bg-gradient-primary' : '' }}">
    @guest
        <div class="container">
            @yield('content')
        </div>
    @endguest

    @auth
    <div id="wrapper">
        <ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon">
                    <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo" class="img-fluid" style="max-height: 40px; filter: brightness(0) invert(1);">
                </div>
            </a>
            
            <hr class="sidebar-divider my-0">

            <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Transaksi</div>

            <li class="nav-item {{ Route::is('penjualan.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('penjualan.index') }}">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span>Penjualan</span>
                </a>
            </li>
            <li class="nav-item {{ Route::is('pembelian.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('pembelian.index') }}">
                    <i class="fas fa-fw fa-box-open"></i>
                    <span>Pembelian</span>
                </a>
            </li>
            <li class="nav-item {{ Route::is('biaya.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('biaya.index') }}">
                    <i class="fas fa-fw fa-receipt"></i>
                    <span>Biaya</span>
                </a>
            </li>

            @if(auth()->user()->role == 'super_admin')
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Super Admin</div>

                <li class="nav-item {{ Route::is('users.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('users.index') }}">
                        <i class="fas fa-fw fa-users-cog"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('gudang.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('gudang.index') }}">
                        <i class="fas fa-fw fa-warehouse"></i>
                        <span>Master Gudang</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('produk.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('produk.index') }}">
                        <i class="fas fa-fw fa-boxes"></i>
                        <span>Master Produk</span>
                    </a>
                </li>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                @if(auth()->user()->role == 'admin') 
                    <hr class="sidebar-divider">
                    <div class="sidebar-heading">Admin</div>
                @endif
                
                <li class="nav-item {{ Route::is('kontak.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('kontak.index') }}">
                        <i class="fas fa-fw fa-address-book"></i>
                        <span>Master Kontak</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('stok.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('stok.index') }}">
                        <i class="fas fa-fw fa-cubes"></i>
                        <span>Cek Stok Gudang</span>
                    </a>
                </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600">
                                    {{ Auth::user()->name }}
                                    <span class="user-role-badge">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</span>
                                </span>
                                <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4e73df&color=fff">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>

            <footer class="sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>&copy; {{ date('Y') }} Hibiscus Efsya</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Keluar?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Apakah Anda yakin ingin keluar dari sistem?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <a class="btn btn-primary" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
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
