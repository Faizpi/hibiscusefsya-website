<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Portal Customer') - Hibiscus Efsya</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --blue-700: #1e40af;
            --blue-600: #2563eb;
            --blue-500: #3b82f6;
            --blue-50: #eff6ff;
            --gray-900: #111827;
            --gray-700: #374151;
            --gray-500: #6b7280;
            --gray-300: #d1d5db;
            --gray-100: #f3f4f6;
            --gray-50: #f9fafb;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f4f8;
            color: var(--gray-900);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 15px;
        }

        /* Navbar */
        .navbar {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(229,231,235,0.6);
            padding: 0 28px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .nav-brand img { height: 34px; }
        .nav-brand span {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--blue-700);
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            margin-left: 24px;
        }
        .nav-links a {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--gray-500);
            text-decoration: none;
            transition: all 0.15s;
        }
        .nav-links a:hover { color: var(--gray-900); background: var(--gray-100); }
        .nav-links a.active { color: var(--blue-700); background: var(--blue-50); font-weight: 600; }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }
        .nav-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: var(--blue-50);
            color: var(--blue-700);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.9rem;
            cursor: pointer;
        }
        .nav-user-name {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--gray-700);
            cursor: pointer;
        }
        .nav-dropdown {
            display: none;
            position: absolute;
            top: 48px;
            right: 0;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(229,231,235,0.7);
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.1);
            min-width: 220px;
            overflow: hidden;
            z-index: 200;
        }
        .nav-dropdown.show { display: block; }
        .nav-dropdown-info {
            padding: 14px 18px;
            border-bottom: 1px solid #e5e7eb;
        }
        .nav-dropdown-info .name { font-size: 0.92rem; font-weight: 600; color: var(--gray-900); }
        .nav-dropdown-info .phone { font-size: 0.8rem; color: var(--gray-500); margin-top: 2px; }
        .nav-dropdown form { margin: 0; }
        .nav-dropdown button {
            width: 100%;
            padding: 12px 18px;
            border: none;
            background: none;
            text-align: left;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            color: #dc2626;
            cursor: pointer;
            transition: background 0.15s;
        }
        .nav-dropdown button:hover { background: #fef2f2; }

        /* Mobile nav toggle */
        .nav-toggle {
            display: none;
            background: none;
            border: none;
            width: 40px; height: 40px;
            font-size: 1.4rem;
            color: var(--gray-700);
            cursor: pointer;
            border-radius: 10px;
            transition: background 0.15s;
        }
        .nav-toggle:hover { background: var(--gray-100); }

        /* Fullscreen mobile overlay nav */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 999;
            flex-direction: column;
            padding: 0;
        }
        .mobile-overlay.open { display: flex; }
        .mobile-overlay-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-bottom: 1px solid rgba(229,231,235,0.5);
        }
        .mobile-overlay-header img { height: 32px; }
        .mobile-overlay-close {
            background: none;
            border: none;
            font-size: 1.8rem;
            color: var(--gray-700);
            cursor: pointer;
            width: 44px; height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: background 0.15s;
            line-height: 1;
        }
        .mobile-overlay-close:hover { background: var(--gray-100); }
        .mobile-overlay-nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 32px;
            gap: 8px;
        }
        .mobile-overlay-nav a {
            display: block;
            padding: 18px 20px;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: 14px;
            transition: all 0.15s;
        }
        .mobile-overlay-nav a:hover,
        .mobile-overlay-nav a.active { color: var(--blue-700); background: var(--blue-50); }
        .mobile-overlay-footer {
            padding: 20px 32px;
            border-top: 1px solid rgba(229,231,235,0.5);
        }
        .mobile-overlay-user {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .mobile-overlay-user .avatar {
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--blue-50);
            color: var(--blue-700);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1rem;
        }
        .mobile-overlay-user .info .name { font-size: 1rem; font-weight: 600; color: var(--gray-900); }
        .mobile-overlay-user .info .phone { font-size: 0.85rem; color: var(--gray-500); }
        .mobile-overlay-logout {
            display: block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: #fef2f2;
            color: #dc2626;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-align: center;
            transition: background 0.15s;
        }
        .mobile-overlay-logout:hover { background: #fee2e2; }

        /* Content */
        .main { flex: 1; padding: 28px; max-width: 1100px; width: 100%; margin: 0 auto; }

        /* Footer */
        .footer {
            text-align: center;
            padding: 18px 24px;
            font-size: 0.82rem;
            color: var(--gray-500);
            border-top: 1px solid #e5e7eb;
            background: #fff;
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* Cards - glassmorphism */
        .card {
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(229,231,235,0.6);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid rgba(229,231,235,0.6);
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-body { padding: 22px; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-secondary { background: var(--gray-100); color: var(--gray-500); }

        /* Table */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; }
        table th {
            padding: 12px 18px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            background: var(--gray-50);
            border-bottom: 1px solid #e5e7eb;
        }
        table td {
            padding: 14px 18px;
            font-size: 0.9rem;
            color: var(--gray-700);
            border-bottom: 1px solid #f3f4f6;
        }
        table tr:hover td { background: var(--gray-50); }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 600; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-primary { background: var(--blue-700); color: #fff; }
        .btn-primary:hover { background: var(--blue-600); color: #fff; }
        .btn-outline { background: rgba(255,255,255,0.7); color: var(--gray-700); border: 1px solid var(--gray-300); }
        .btn-outline:hover { background: var(--gray-50); color: var(--gray-900); }
        .btn-sm { padding: 7px 12px; font-size: 0.82rem; }
        .btn-icon { padding: 7px 10px; }

        @media (max-width: 768px) {
            .navbar { padding: 0 16px; height: 60px; }
            .nav-links { display: none !important; }
            .nav-toggle { display: flex; align-items: center; justify-content: center; }
            .nav-user-name { display: none; }
            .main { padding: 16px; }
        }

        @media (min-width: 769px) {
            .nav-toggle { display: none !important; }
            .mobile-overlay { display: none !important; }
        }

        @stack('styles')
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a class="nav-brand" href="{{ route('customer.dashboard') }}">
                <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo">
                <span>Hibiscus Efsya</span>
            </a>
            @if(session('customer_id'))
                <ul class="nav-links">
                    <li><a href="{{ route('customer.dashboard') }}" class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                    <li><a href="{{ route('customer.history') }}" class="{{ request()->routeIs('customer.history*') ? 'active' : '' }}">Riwayat</a></li>
                </ul>
            @endif
        </div>

        @if(session('customer_id'))
            <div style="display:flex;align-items:center;gap:8px;">
                <button class="nav-toggle" onclick="document.getElementById('mobileOverlay').classList.add('open')">&#9776;</button>
                <div class="nav-right" onclick="document.querySelector('.nav-dropdown').classList.toggle('show')">
                    <div class="nav-avatar">{{ strtoupper(substr(session('customer_nama', 'C'), 0, 1)) }}</div>
                    <span class="nav-user-name">{{ session('customer_nama') }}</span>
                    <div class="nav-dropdown">
                        <div class="nav-dropdown-info">
                            <div class="name">{{ session('customer_nama') }}</div>
                            <div class="phone">{{ session('customer_no_telp') }}</div>
                        </div>
                        <form action="{{ route('customer.logout') }}" method="POST">
                            @csrf
                            <button type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </nav>

    {{-- Mobile fullscreen overlay nav --}}
    @if(session('customer_id'))
    <div class="mobile-overlay" id="mobileOverlay">
        <div class="mobile-overlay-header">
            <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo">
            <button class="mobile-overlay-close" onclick="document.getElementById('mobileOverlay').classList.remove('open')">&times;</button>
        </div>
        <div class="mobile-overlay-nav">
            <a href="{{ route('customer.dashboard') }}" class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('customer.history') }}" class="{{ request()->routeIs('customer.history*') ? 'active' : '' }}">Riwayat</a>
        </div>
        <div class="mobile-overlay-footer">
            <div class="mobile-overlay-user">
                <div class="avatar">{{ strtoupper(substr(session('customer_nama', 'C'), 0, 1)) }}</div>
                <div class="info">
                    <div class="name">{{ session('customer_nama') }}</div>
                    <div class="phone">{{ session('customer_no_telp') }}</div>
                </div>
            </div>
            <form action="{{ route('customer.logout') }}" method="POST">
                @csrf
                <button type="submit" class="mobile-overlay-logout">Logout</button>
            </form>
        </div>
    </div>
    @endif

    <div class="main">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    <footer class="footer">
        &copy; {{ date('Y') }} Hibiscus Efsya. Portal Customer.
    </footer>

    <script>
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            var dd = document.querySelector('.nav-dropdown');
            var nr = document.querySelector('.nav-right');
            if (dd && nr && !nr.contains(e.target)) dd.classList.remove('show');
        });
    </script>
    @stack('scripts')
</body>
</html>
