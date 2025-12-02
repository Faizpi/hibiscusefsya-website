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

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
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

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})</span>
                                <img class="img-profile rounded-circle" src="https://startbootstrap.github.io/startbootstrap-sb-admin-2/img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
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
            
            <footer class="sticky-footer bg-white">
                <div class="container m-y-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Hibiscus Efsya {{ date('Y') }}</span>
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