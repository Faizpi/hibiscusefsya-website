<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hibiscus Efsya — Base Preview</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon-rounded.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon-rounded.png') }}">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('template/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">

    <!-- Keep existing SB Admin assets to avoid breaking JS/plugins -->
    <link href="{{ asset('template/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/bundle/select2/dist/css/select2.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --base-bg: #f5f6f8;
            --base-surface: #ffffff;
            --base-border: #e5e7eb;
            --base-primary: #2563eb;
            --base-muted: #6b7280;
            --base-text: #111827;
        }

        * {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
        }

        body {
            background: var(--base-bg);
        }

        /* Layout shell mimicking Base: clean topbar + collapsible sidebar */
        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 16rem;
            min-width: 16rem;
            background: var(--base-surface);
            border-right: 1px solid var(--base-border);
            position: fixed;
            inset: 0 auto 0 0;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar .brand {
            display: flex;
            align-items: center;
            height: 64px;
            padding: 0 1rem;
            border-bottom: 1px solid var(--base-border);
        }

        .sidebar .brand img {
            height: 32px;
            border-radius: 6px;
        }

        .sidebar .section {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--base-muted);
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem;
            color: var(--base-muted);
            border-radius: 8px;
            transition: background .15s ease, color .15s ease;
        }

        .sidebar .nav-link:hover {
            background: #eef2ff;
            color: var(--base-primary);
        }

        .sidebar .nav-link i {
            color: inherit;
        }

        .sidebar .nav-item.active .nav-link {
            background: #eef2ff;
            color: var(--base-primary);
            font-weight: 600;
        }

        #content-wrapper {
            margin-left: 16rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 900;
            height: 64px;
            background: var(--base-surface);
            border-bottom: 1px solid var(--base-border);
            display: flex;
            align-items: center;
            padding: 0 1rem;
        }

        .topbar .title {
            font-weight: 600;
            color: var(--base-text);
        }

        .topbar .spacer {
            flex: 1;
        }

        .topbar .user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--base-text);
        }

        .container-fluid {
            padding: 1.5rem;
        }

        .card {
            background: var(--base-surface);
            border: 1px solid var(--base-border);
            border-radius: 12px;
            box-shadow: none;
        }

        .card-header {
            background: var(--base-surface);
            border-bottom: 1px solid var(--base-border);
            font-weight: 600;
        }

        .btn-primary {
            background: var(--base-primary);
            border: none;
        }

        /* Footer */
        .sticky-footer {
            background: var(--base-surface);
            border-top: 1px solid var(--base-border);
            padding: 1rem;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .25s ease;
                width: 80vw;
                min-width: 80vw;
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 4px 0 20px rgba(0, 0, 0, .15);
            }

            #content-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    @guest
        <div class="container">
            @yield('content')
        </div>
    @endguest

    @auth
        <div id="wrapper">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="brand">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center">
                        <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo">
                    </a>
                </div>

                <div class="section">Menu Utama</div>
                <ul class="list-unstyled mb-2">
                    <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}" class="nav-link"><i
                                class="fas fa-home"></i><span>Dashboard</span></a>
                    </li>
                </ul>

                <div class="section">Transaksi</div>
                <ul class="list-unstyled mb-2">
                    <li class="nav-item {{ Route::is('penjualan.*') ? 'active' : '' }}">
                        <a href="{{ route('penjualan.index') }}" class="nav-link"><i
                                class="fas fa-file-invoice-dollar"></i><span>Penjualan</span></a>
                    </li>
                    <li class="nav-item {{ Route::is('pembelian.*') ? 'active' : '' }}">
                        <a href="{{ route('pembelian.index') }}" class="nav-link"><i
                                class="fas fa-shopping-bag"></i><span>Pembelian</span></a>
                    </li>
                    <li class="nav-item {{ Route::is('biaya.*') ? 'active' : '' }}">
                        <a href="{{ route('biaya.index') }}" class="nav-link"><i
                                class="fas fa-wallet"></i><span>Biaya</span></a>
                    </li>
                    <li class="nav-item {{ Route::is('kunjungan.*') ? 'active' : '' }}">
                        <a href="{{ route('kunjungan.index') }}" class="nav-link"><i
                                class="fas fa-map-marker-alt"></i><span>Kunjungan</span></a>
                    </li>
                </ul>

                @if(auth()->user()->role == 'super_admin')
                    <div class="section">Pengaturan</div>
                    <ul class="list-unstyled mb-2">
                        <li class="nav-item {{ Route::is('admin-gudang.*') ? 'active' : '' }}"><a
                                href="{{ route('admin-gudang.index') }}" class="nav-link"><i
                                    class="fas fa-user-tie"></i><span>Admin Gudang</span></a></li>
                        <li class="nav-item {{ Route::is('users.*') ? 'active' : '' }}"><a href="{{ route('users.index') }}"
                                class="nav-link"><i class="fas fa-users"></i><span>Pengguna</span></a></li>
                        <li class="nav-item {{ Route::is('gudang.*') ? 'active' : '' }}"><a href="{{ route('gudang.index') }}"
                                class="nav-link"><i class="fas fa-warehouse"></i><span>Gudang</span></a></li>
                        <li class="nav-item {{ Route::is('produk.*') ? 'active' : '' }}"><a href="{{ route('produk.index') }}"
                                class="nav-link"><i class="fas fa-box"></i><span>Produk</span></a></li>
                    </ul>
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                    <div class="section">Master Data</div>
                    <ul class="list-unstyled mb-2">
                        <li class="nav-item {{ Route::is('kontak.*') ? 'active' : '' }}"><a href="{{ route('kontak.index') }}"
                                class="nav-link"><i class="fas fa-address-card"></i><span>Kontak</span></a></li>
                        <li class="nav-item {{ Route::is('stok.*') ? 'active' : '' }}"><a href="{{ route('stok.index') }}"
                                class="nav-link"><i class="fas fa-boxes"></i><span>Stok Gudang</span></a></li>
                    </ul>
                @endif
            </aside>

            <!-- Content wrapper -->
            <div id="content-wrapper" class="d-flex flex-column">
                <div class="topbar">
                    <div class="title">Base Preview</div>
                    <div class="spacer"></div>
                    <div class="user">
                        <span>{{ Auth::user()->name }}</span>
                        <img class="img-profile rounded-circle" style="width:32px;height:32px;"
                            src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=2563eb&color=fff&size=128">
                    </div>
                </div>
                <div id="content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </div>
                <footer class="sticky-footer">
                    <div class="container my-auto">
                        <div class="copyright text-center my-auto">&copy; {{ date('Y') }} Hibiscus Efsya</div>
                    </div>
                </footer>
            </div>
        </div>
    @endauth

    <!-- Scripts: keep current stack/plugins -->
    <script src="{{ asset('template/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('template/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('template/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('template/js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('assets/bundle/select2/dist/js/select2.min.js') }}"></script>

    @stack('scripts')
</body>

</html>