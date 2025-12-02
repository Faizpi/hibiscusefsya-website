<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hibiscus Efsya Jurnal</title>

    <link href="{{ asset('template/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="{{ asset('template/css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    <style>
        /* Modern Sidebar Styles */
        .sidebar-modern {
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fc 100%) !important;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
        }
        
        .sidebar-modern .sidebar-brand {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            padding: 1.5rem 1rem;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-modern .sidebar-brand-icon img {
            filter: brightness(0) invert(1);
        }
        
        .sidebar-modern .nav-item {
            margin: 4px 12px;
        }
        
        .sidebar-modern .nav-link {
            color: #5a5c69 !important;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.85rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .sidebar-modern .nav-link i {
            color: #b7b9cc;
            margin-right: 0.75rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .sidebar-modern .nav-link:hover {
            background: linear-gradient(135deg, #eef2ff 0%, #e8efff 100%);
            color: #4e73df !important;
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.15);
        }
        
        .sidebar-modern .nav-link:hover i {
            color: #4e73df;
        }
        
        .sidebar-modern .nav-item.active .nav-link {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #ffffff !important;
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.4);
            transform: translateX(5px);
        }
        
        .sidebar-modern .nav-item.active .nav-link i {
            color: #ffffff;
        }
        
        .sidebar-modern .sidebar-heading {
            color: #4e73df;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            padding: 1rem 1.5rem 0.5rem;
            margin-top: 0.5rem;
        }
        
        .sidebar-modern .sidebar-divider {
            border-top: 1px solid #e3e6f0;
            margin: 0.5rem 1rem;
        }
        
        .sidebar-modern #sidebarToggle {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            width: 2.5rem;
            height: 2.5rem;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
            transition: all 0.3s ease;
        }
        
        .sidebar-modern #sidebarToggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.5);
        }
        
        .sidebar-modern #sidebarToggle::after {
            color: #ffffff;
        }
        
        /* Scrollbar styling */
        .sidebar-modern::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar-modern::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .sidebar-modern::-webkit-scrollbar-thumb {
            background: #4e73df;
            border-radius: 10px;
        }
        
        /* Content wrapper adjustment */
        #content-wrapper {
            background: #f4f6f9;
        }
        
        /* ========== MODERN NAVBAR ========== */
        .topbar-modern {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%) !important;
            border-bottom: none;
            padding: 0.75rem 1.5rem;
            margin: 1rem 1rem 1.5rem 1rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
        }
        
        .topbar-modern #sidebarToggleTop {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #ffffff;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
            transition: all 0.3s ease;
        }
        
        .topbar-modern #sidebarToggleTop:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.5);
        }
        
        .topbar-modern .user-info {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f8f9fc 0%, #eef2ff 100%);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .topbar-modern .user-info:hover {
            background: linear-gradient(135deg, #eef2ff 0%, #e8efff 100%);
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.15);
        }
        
        .topbar-modern .user-name {
            font-weight: 600;
            color: #5a5c69;
            margin-right: 0.75rem;
        }
        
        .topbar-modern .user-role {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            margin-right: 0.75rem;
        }
        
        .topbar-modern .img-profile {
            width: 40px;
            height: 40px;
            border: 3px solid #4e73df;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
            transition: all 0.3s ease;
        }
        
        .topbar-modern .img-profile:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.5);
        }
        
        .topbar-modern .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 0.75rem;
            margin-top: 0.75rem;
        }
        
        .topbar-modern .dropdown-item {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            color: #5a5c69;
            transition: all 0.3s ease;
        }
        
        .topbar-modern .dropdown-item:hover {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #ffffff;
            transform: translateX(5px);
        }
        
        .topbar-modern .dropdown-item i {
            transition: all 0.3s ease;
        }
        
        .topbar-modern .dropdown-item:hover i {
            color: #ffffff;
        }
        
        /* ========== MODERN CONTENT ========== */
        .content-modern {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin: 0 1rem 1.5rem 1rem;
        }
        
        /* Modern Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
            border-bottom: 1px solid #e3e6f0;
            font-weight: 700;
            color: #5a5c69;
            padding: 1rem 1.5rem;
        }
        
        .card-header.bg-primary,
        .card-header.bg-gradient-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
            color: #ffffff;
        }
        
        /* Modern Buttons */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.6rem 1.25rem;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #224abe 0%, #1a3a9e 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.5);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13a06d 100%);
            box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #13a06d 0%, #0e8055 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 200, 138, 0.5);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);
            box-shadow: 0 4px 15px rgba(231, 74, 59, 0.3);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a02d23 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 74, 59, 0.5);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
            box-shadow: 0 4px 15px rgba(246, 194, 62, 0.3);
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #dda20a 0%, #b88508 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(246, 194, 62, 0.5);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
            box-shadow: 0 4px 15px rgba(54, 185, 204, 0.3);
        }
        
        .btn-info:hover {
            background: linear-gradient(135deg, #258391 0%, #1d6b77 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(54, 185, 204, 0.5);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #858796 0%, #60616f 100%);
            box-shadow: 0 4px 15px rgba(133, 135, 150, 0.3);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #60616f 0%, #4a4b56 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(133, 135, 150, 0.5);
        }
        
        /* Modern Form Controls */
        .form-control {
            border-radius: 10px;
            border: 2px solid #e3e6f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        }
        
        /* Modern Tables */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #ffffff;
            font-weight: 700;
            border: none;
            padding: 1rem;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: linear-gradient(135deg, #eef2ff 0%, #e8efff 100%);
            transform: scale(1.01);
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        /* ========== MODERN FOOTER ========== */
        .footer-modern {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
            border-top: none;
            padding: 1.25rem;
            margin: 0 1rem 1rem 1rem;
            border-radius: 15px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .footer-modern .copyright {
            color: #5a5c69;
            font-weight: 600;
        }
        
        .footer-modern .copyright span {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* ========== MODERN MODAL ========== */
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #ffffff;
            border-bottom: none;
            padding: 1.25rem 1.5rem;
        }
        
        .modal-header .close {
            color: #ffffff;
            text-shadow: none;
            opacity: 0.8;
        }
        
        .modal-header .close:hover {
            opacity: 1;
        }
        
        .modal-body {
            padding: 1.5rem;
            color: #5a5c69;
        }
        
        .modal-footer {
            border-top: 1px solid #e3e6f0;
            padding: 1rem 1.5rem;
        }
        
        /* ========== ALERTS ========== */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            color: #856404;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }
        
        /* ========== PAGE HEADING ========== */
        .h3.text-gray-800 {
            color: #3a3b45 !important;
            font-weight: 700;
            position: relative;
            padding-bottom: 0.75rem;
        }
        
        .h3.text-gray-800::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 2px;
        }
        
        /* ========== BADGE ========== */
        .badge {
            border-radius: 20px;
            padding: 0.4rem 0.8rem;
            font-weight: 600;
        }
        
        .badge-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }
        
        .badge-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13a06d 100%);
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);
        }
        
        .badge-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
            color: #333;
        }
        
        /* ========== PAGINATION ========== */
        .pagination .page-link {
            border: none;
            border-radius: 8px;
            margin: 0 3px;
            color: #5a5c69;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .pagination .page-link:hover {
            background: linear-gradient(135deg, #eef2ff 0%, #e8efff 100%);
            color: #4e73df;
            transform: translateY(-2px);
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
        }
    </style>
</head>
<body id="page-top" class="{{ Request::is('login') || Request::is('register') ? 'bg-gradient-primary' : '' }}">
    @guest
        <div class="container">
            @yield('content')
        </div>
    @endguest

    {{-- Jika user sudah login, tampilkan layout admin lengkap --}}
    @auth
    <div id="wrapper">

<ul class="navbar-nav sidebar sidebar-modern accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon">
                    <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo" class="img-fluid" style="max-height: 40px;">
                </div>
            </a>
            <hr class="sidebar-divider my-0">

            <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Transaksi</div>

            <li class="nav-item {{ Route::is('penjualan.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('penjualan.index') }}"><i class="fas fa-fw fa-shopping-cart"></i><span>Penjualan</span></a>
            </li>
            <li class="nav-item {{ Route::is('pembelian.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('pembelian.index') }}"><i class="fas fa-fw fa-box-open"></i><span>Pembelian</span></a>
            </li>
            <li class="nav-item {{ Route::is('biaya.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('biaya.index') }}"><i class="fas fa-fw fa-receipt"></i><span>Biaya</span></a>
            </li>

            @if(auth()->user()->role == 'super_admin')
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Super Admin Area</div>

                <li class="nav-item {{ Route::is('users.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-fw fa-users-cog"></i><span>User Management</span></a>
                </li>
                <li class="nav-item {{ Route::is('gudang.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('gudang.index') }}"><i class="fas fa-fw fa-warehouse"></i><span>Master Gudang</span></a>
                </li>
                <li class="nav-item {{ Route::is('produk.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('produk.index') }}"><i class="fas fa-fw fa-boxes"></i><span>Master Produk</span></a>
                </li>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                @if(auth()->user()->role == 'admin') 
                    <hr class="sidebar-divider">
                    <div class="sidebar-heading">Admin Area</div>
                @endif
                
                <li class="nav-item {{ Route::is('kontak.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('kontak.index') }}"><i class="fas fa-fw fa-address-book"></i><span>Master Kontak</span></a>
                </li>
                <li class="nav-item {{ Route::is('stok.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('stok.index') }}"><i class="fas fa-fw fa-cubes"></i><span>Cek Stok Gudang</span></a>
                </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content" class="flex-grow-1">

                <nav class="navbar navbar-expand navbar-light topbar-modern mb-0 static-top">
                    <button id="sidebarToggleTop" class="btn d-md-none mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <div class="d-none d-sm-flex align-items-center">
                        <h5 class="mb-0 text-gray-600 font-weight-bold">
                            <i class="fas fa-home mr-2 text-primary"></i>
                            Selamat Datang!
                        </h5>
                    </div>
                    
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle user-info" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="user-name d-none d-lg-inline">{{ Auth::user()->name }}</span>
                                <span class="user-role d-none d-lg-inline">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</span>
                                <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4e73df&color=fff&bold=true">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-primary"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                
                <div class="container-fluid px-4">
                    @yield('content')
                </div>
            </div>
            
            <footer class="footer-modern">
                <div class="container">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                        <div class="copyright mb-2 mb-sm-0">
                            <span>Hibiscus Efsya</span> &copy; {{ date('Y') }} — All Rights Reserved
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="text-muted small">
                                <i class="fas fa-code mr-1"></i> Made with <i class="fas fa-heart text-danger mx-1"></i> in Indonesia
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
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