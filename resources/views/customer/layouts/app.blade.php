<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Portal Customer') - Hibiscus Efsya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #e91e63;
            --primary-dark: #c2185b;
            --primary-light: #f8bbd0;
            --secondary: #ff6090;
            --bg-light: #fce4ec;
        }
        body { background: #f5f5f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        .navbar-custom .navbar-brand { color: #fff; font-weight: 700; font-size: 1.3rem; }
        .navbar-custom .nav-link { color: rgba(255,255,255,0.85) !important; font-weight: 500; }
        .navbar-custom .nav-link:hover { color: #fff !important; }
        .navbar-custom .nav-link.active { color: #fff !important; border-bottom: 2px solid #fff; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .card-header { background: #fff; border-bottom: 2px solid var(--primary-light); border-radius: 12px 12px 0 0 !important; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); }
        .btn-outline-primary:hover { background: var(--primary); border-color: var(--primary); color: #fff; }
        .badge-primary { background: var(--primary); }
        .text-primary { color: var(--primary) !important; }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        footer { background: #fff; border-top: 1px solid #eee; }
        .customer-avatar {
            width: 40px; height: 40px; border-radius: 50%; background: var(--primary-light);
            color: var(--primary); display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.1rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('customer.dashboard') }}">
                <i class="fas fa-store mr-2"></i>Hibiscus Efsya
            </a>
            <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#customerNav">
                <i class="fas fa-bars text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="customerNav">
                @if(session('customer_id'))
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}" 
                               href="{{ route('customer.dashboard') }}">
                                <i class="fas fa-home mr-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customer.history*') ? 'active' : '' }}" 
                               href="{{ route('customer.history') }}">
                                <i class="fas fa-receipt mr-1"></i> Riwayat Pembelian
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-toggle="dropdown">
                                <div class="customer-avatar mr-2">
                                    {{ strtoupper(substr(session('customer_nama', 'C'), 0, 1)) }}
                                </div>
                                {{ session('customer_nama') }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <span class="dropdown-item-text small text-muted">
                                    <i class="fas fa-phone mr-1"></i>{{ session('customer_no_telp') }}
                                </span>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('customer.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                @endif
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @yield('content')
    </div>

    {{-- Footer --}}
    <footer class="py-3 mt-5">
        <div class="container text-center text-muted small">
            &copy; {{ date('Y') }} Hibiscus Efsya. Portal Customer.
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
