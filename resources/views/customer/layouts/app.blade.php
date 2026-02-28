<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Portal Customer') - Hibiscus Efsya</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            color: var(--gray-900);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-brand {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--blue-700);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-brand i { font-size: 1.1rem; }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
        }
        .nav-links a {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--gray-500);
            text-decoration: none;
            transition: all 0.15s;
        }
        .nav-links a:hover { color: var(--gray-900); background: var(--gray-100); }
        .nav-links a.active { color: var(--blue-700); background: var(--blue-50); }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }
        .nav-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--blue-50);
            color: var(--blue-700);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem;
            cursor: pointer;
        }
        .nav-user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--gray-700);
            cursor: pointer;
        }
        .nav-dropdown {
            display: none;
            position: absolute;
            top: 44px;
            right: 0;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            min-width: 200px;
            overflow: hidden;
            z-index: 200;
        }
        .nav-dropdown.show { display: block; }
        .nav-dropdown-info {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .nav-dropdown-info .name { font-size: 0.85rem; font-weight: 600; color: var(--gray-900); }
        .nav-dropdown-info .phone { font-size: 0.75rem; color: var(--gray-500); }
        .nav-dropdown form { margin: 0; }
        .nav-dropdown button {
            width: 100%;
            padding: 10px 16px;
            border: none;
            background: none;
            text-align: left;
            font-family: 'Inter', sans-serif;
            font-size: 0.82rem;
            color: #dc2626;
            cursor: pointer;
            transition: background 0.15s;
        }
        .nav-dropdown button:hover { background: #fef2f2; }

        /* Mobile nav toggle */
        .nav-toggle { display: none; background: none; border: none; font-size: 1.2rem; color: var(--gray-700); cursor: pointer; }

        /* Content */
        .main { flex: 1; padding: 24px; max-width: 1100px; width: 100%; margin: 0 auto; }

        /* Footer */
        .footer {
            text-align: center;
            padding: 16px 24px;
            font-size: 0.75rem;
            color: var(--gray-500);
            border-top: 1px solid #e5e7eb;
            background: #fff;
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.82rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* Cards */
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-header i { color: var(--blue-600); }
        .card-body { padding: 20px; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.72rem;
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
            padding: 10px 16px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            background: var(--gray-50);
            border-bottom: 1px solid #e5e7eb;
        }
        table td {
            padding: 12px 16px;
            font-size: 0.82rem;
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
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-primary { background: var(--blue-700); color: #fff; }
        .btn-primary:hover { background: var(--blue-600); color: #fff; }
        .btn-outline { background: #fff; color: var(--gray-700); border: 1px solid var(--gray-300); }
        .btn-outline:hover { background: var(--gray-50); color: var(--gray-900); }
        .btn-sm { padding: 6px 10px; font-size: 0.75rem; }
        .btn-icon { padding: 6px 8px; }

        @media (max-width: 768px) {
            .navbar { padding: 0 16px; }
            .nav-links { display: none; }
            .nav-links.show {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 60px; left: 0; right: 0;
                background: #fff;
                border-bottom: 1px solid #e5e7eb;
                padding: 8px 16px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            }
            .nav-toggle { display: block; }
            .nav-user-name { display: none; }
            .main { padding: 16px; }
        }

        @stack('styles')
    </style>
</head>
<body>
    <nav class="navbar">
        <div style="display:flex; align-items:center; gap:20px;">
            <a class="nav-brand" href="{{ route('customer.dashboard') }}">
                <i class="fas fa-store"></i> Hibiscus Efsya
            </a>
            <button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        @if(session('customer_id'))
            <ul class="nav-links">
                <li><a href="{{ route('customer.dashboard') }}" class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Dashboard
                </a></li>
                <li><a href="{{ route('customer.history') }}" class="{{ request()->routeIs('customer.history*') ? 'active' : '' }}">
                    <i class="fas fa-receipt"></i> Riwayat
                </a></li>
            </ul>
            <div class="nav-user" onclick="document.querySelector('.nav-dropdown').classList.toggle('show')">
                <div class="nav-avatar">{{ strtoupper(substr(session('customer_nama', 'C'), 0, 1)) }}</div>
                <span class="nav-user-name">{{ session('customer_nama') }}</span>
                <div class="nav-dropdown">
                    <div class="nav-dropdown-info">
                        <div class="name">{{ session('customer_nama') }}</div>
                        <div class="phone"><i class="fas fa-phone fa-xs"></i> {{ session('customer_no_telp') }}</div>
                    </div>
                    <form action="{{ route('customer.logout') }}" method="POST">
                        @csrf
                        <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
                    </form>
                </div>
            </div>
        @endif
    </nav>

    <div class="main">
        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
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
            var nu = document.querySelector('.nav-user');
            if (dd && nu && !nu.contains(e.target)) dd.classList.remove('show');
        });
    </script>
    @stack('scripts')
</body>
</html>
