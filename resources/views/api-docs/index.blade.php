<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Hibiscus Efsya Sales</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon-rounded.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #dbeafe;
            --bg-light: #f9fafb;
            --bg-white: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border: #e5e7eb;
            --border-light: #f3f4f6;
            --sidebar-width: 300px;
            --header-height: 64px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* ==================== HEADER ==================== */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: var(--header-height);
            background: var(--bg-white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 18px;
            color: var(--text-primary);
            text-decoration: none;
        }

        .header-brand img {
            height: 36px;
            width: auto;
        }

        .header-brand span {
            color: var(--primary);
        }

        .header-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-outline:hover {
            background: var(--bg-light);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--text-primary);
            cursor: pointer;
            padding: 4px 8px;
        }

        /* ==================== SIDEBAR ==================== */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--bg-white);
            border-right: 1px solid var(--border);
            overflow-y: auto;
            padding: 16px 0;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-search {
            padding: 0 16px 16px;
            position: sticky;
            top: 0;
            background: var(--bg-white);
            z-index: 1;
        }

        .sidebar-search input {
            width: 100%;
            padding: 10px 14px 10px 36px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: var(--bg-light);
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: border-color 0.2s;
        }

        .sidebar-search input:focus {
            border-color: var(--primary);
            background: var(--bg-white);
        }

        .sidebar-search .search-icon {
            position: absolute;
            left: 28px;
            top: 50%;
            transform: translateY(calc(-50% - 8px));
            color: var(--text-muted);
            font-size: 13px;
        }

        .sidebar-group {
            margin-bottom: 4px;
        }

        .sidebar-group-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            color: var(--text-secondary);
            transition: all 0.15s;
            user-select: none;
        }

        .sidebar-group-header:hover {
            color: var(--primary);
            background: var(--bg-light);
        }

        .sidebar-group-header .group-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #fff;
            flex-shrink: 0;
        }

        .sidebar-group-header .chevron {
            margin-left: auto;
            transition: transform 0.2s;
            font-size: 10px;
        }

        .sidebar-group.open .chevron {
            transform: rotate(90deg);
        }

        .sidebar-items {
            display: none;
            padding: 2px 0;
        }

        .sidebar-group.open .sidebar-items {
            display: block;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 20px 7px 58px;
            cursor: pointer;
            font-size: 13px;
            color: var(--text-secondary);
            transition: all 0.15s;
            text-decoration: none;
        }

        .sidebar-item:hover {
            color: var(--primary);
            background: var(--primary-light);
        }

        .sidebar-item.active {
            color: var(--primary);
            background: var(--primary-light);
            font-weight: 600;
        }

        .sidebar-item .method-badge {
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            flex-shrink: 0;
            min-width: 36px;
            text-align: center;
        }

        .method-GET {
            background: #dcfce7;
            color: #166534;
        }

        .method-POST {
            background: #dbeafe;
            color: #1e40af;
        }

        .method-PUT {
            background: #fef3c7;
            color: #92400e;
        }

        .method-DELETE {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ==================== MAIN CONTENT ==================== */
        .main {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 32px 40px;
            max-width: 900px;
        }

        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            border-radius: 16px;
            padding: 40px;
            color: #fff;
            margin-bottom: 32px;
        }

        .hero h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .hero p {
            opacity: 0.9;
            font-size: 15px;
            max-width: 600px;
        }

        .hero-meta {
            display: flex;
            gap: 24px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .hero-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }

        .auth-info {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .auth-info h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .auth-info h3 i {
            color: var(--primary);
        }

        .auth-code {
            background: #1e293b;
            color: #e2e8f0;
            padding: 14px 18px;
            border-radius: 8px;
            font-family: 'Fira Code', 'JetBrains Mono', monospace;
            font-size: 13px;
            margin-top: 12px;
            overflow-x: auto;
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .role-card {
            background: var(--bg-light);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            padding: 12px;
        }

        .role-card .role-name {
            font-weight: 700;
            font-size: 13px;
            color: var(--primary);
            font-family: monospace;
        }

        .role-card .role-desc {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* ==================== ENDPOINT SECTION ==================== */
        .endpoint-group {
            margin-bottom: 40px;
        }

        .endpoint-group-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--border-light);
        }

        .endpoint-group-header .group-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
        }

        .endpoint-group-header h2 {
            font-size: 20px;
            font-weight: 700;
        }

        .endpoint-count {
            margin-left: auto;
            background: var(--bg-light);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .endpoint-card {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 12px;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }

        .endpoint-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .endpoint-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            cursor: pointer;
            user-select: none;
        }

        .endpoint-card-header .method-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.5px;
            min-width: 52px;
            text-align: center;
        }

        .endpoint-card-header .endpoint-path {
            font-family: 'Fira Code', 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .endpoint-card-header .endpoint-title {
            margin-left: auto;
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .endpoint-card-header .auth-badge {
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        .auth-required {
            background: #fef3c7;
            color: #92400e;
        }

        .auth-public {
            background: #dcfce7;
            color: #166534;
        }

        .endpoint-card-header .expand-icon {
            color: var(--text-muted);
            transition: transform 0.2s;
            font-size: 12px;
        }

        .endpoint-card.open .expand-icon {
            transform: rotate(180deg);
        }

        .endpoint-card-body {
            display: none;
            padding: 0 20px 20px;
            border-top: 1px solid var(--border-light);
        }

        .endpoint-card.open .endpoint-card-body {
            display: block;
        }

        .endpoint-desc {
            font-size: 14px;
            color: var(--text-secondary);
            padding: 16px 0 12px;
        }

        .roles-list {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .role-badge {
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
            background: #ede9fe;
            color: #5b21b6;
            font-weight: 600;
        }

        .param-section h4 {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .param-section h4 i {
            color: var(--primary);
            font-size: 12px;
        }

        .param-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .param-table th {
            text-align: left;
            padding: 8px 12px;
            background: var(--bg-light);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .param-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: top;
        }

        .param-name {
            font-family: 'Fira Code', 'JetBrains Mono', monospace;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 12px;
        }

        .param-type {
            font-size: 11px;
            color: var(--primary);
            background: var(--primary-light);
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        .param-required {
            font-size: 10px;
            color: #dc2626;
            font-weight: 700;
        }

        .param-optional {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .response-section h4 {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .response-section h4 i {
            color: #10b981;
            font-size: 12px;
        }

        .response-code {
            background: #1e293b;
            color: #e2e8f0;
            padding: 16px;
            border-radius: 8px;
            font-family: 'Fira Code', 'JetBrains Mono', monospace;
            font-size: 12px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.5;
            position: relative;
        }

        .copy-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #94a3b8;
            padding: 4px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        /* ==================== SCROLL-TO-TOP ==================== */
        .scroll-top {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--primary);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            opacity: 0;
            transition: all 0.3s;
            pointer-events: none;
        }

        .scroll-top.visible {
            opacity: 1;
            pointer-events: auto;
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            }

            .main {
                margin-left: 0;
                padding: 20px 16px;
            }

            .hamburger {
                display: block;
            }

            .hero {
                padding: 24px;
            }

            .hero h1 {
                font-size: 22px;
            }

            .header-actions .btn-text {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .endpoint-card-header {
                flex-wrap: wrap;
            }

            .endpoint-card-header .endpoint-title {
                margin-left: 0;
                width: 100%;
                order: 5;
                margin-top: 4px;
            }

            .hero-meta {
                gap: 8px;
            }

            .hero-meta-item {
                font-size: 12px;
                padding: 6px 10px;
            }
        }

        /* ==================== OVERLAY ==================== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 99;
        }

        .sidebar-overlay.visible {
            display: block;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header class="header">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <a href="{{ url('/docs') }}" class="header-brand">
            <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo">
            <span>API</span> Docs
        </a>
        <div class="header-actions">
            <a href="{{ url('/docs/json') }}" target="_blank" class="btn btn-outline btn-sm">
                <i class="fas fa-code"></i> <span class="btn-text">JSON</span>
            </a>
            <a href="{{ url('/docs/download') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-download"></i> <span class="btn-text">Download JSON</span>
            </a>
        </div>
    </header>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" placeholder="Cari endpoint..." oninput="filterEndpoints(this.value)">
        </div>

        @foreach($docs['endpoints'] as $gi => $group)
            <div class="sidebar-group open" data-group="{{ $gi }}">
                <div class="sidebar-group-header" onclick="toggleGroup(this)">
                    <div class="group-icon" style="background: {{ $group['color'] }}">
                        <i class="fas {{ $group['icon'] }}"></i>
                    </div>
                    <span>{{ $group['group'] }}</span>
                    <i class="fas fa-chevron-right chevron"></i>
                </div>
                <div class="sidebar-items">
                    @foreach($group['items'] as $ei => $ep)
                        <a href="#ep-{{ $gi }}-{{ $ei }}" class="sidebar-item" onclick="closeSidebarMobile()">
                            <span class="method-badge method-{{ $ep['method'] }}">{{ $ep['method'] }}</span>
                            <span>{{ $ep['title'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <!-- Main Content -->
    <div class="main">

        <!-- Hero -->
        <div class="hero">
            <h1>Hibiscus Efsya Sales API</h1>
            <p>Dokumentasi lengkap REST API untuk aplikasi mobile POS & Inventory Management System.</p>
            <div class="hero-meta">
                <div class="hero-meta-item">
                    <i class="fas fa-tag"></i> v1.0.0
                </div>
                <div class="hero-meta-item">
                    <i class="fas fa-link"></i> {{ url('/api/v1') }}
                </div>
                <div class="hero-meta-item">
                    <i class="fas fa-shield-alt"></i> Bearer Token Auth
                </div>
                <div class="hero-meta-item">
                    <i class="fas fa-cube"></i>
                    {{ collect($docs['endpoints'])->sum(function ($g) {
    return count($g['items']); }) }} Endpoints
                </div>
            </div>
        </div>

        <!-- Auth Info -->
        <div class="auth-info" id="authentication">
            <h3><i class="fas fa-lock"></i> Authentication</h3>
            <p style="font-size: 14px; color: var(--text-secondary);">
                {{ $docs['authentication']['description'] }}
            </p>
            <div class="auth-code">
                <span style="color: #94a3b8;">// Header yang harus dikirim di setiap request</span><br>
                <span style="color: #f472b6;">Authorization</span>: <span style="color: #a5f3fc;">Bearer</span> <span
                    style="color: #fbbf24;">{token_dari_login}</span>
            </div>

            <h3 style="margin-top: 20px;"><i class="fas fa-user-shield"></i> Roles</h3>
            <div class="roles-grid">
                @foreach($docs['roles'] as $role)
                    <div class="role-card">
                        <div class="role-name">{{ $role['role'] }}</div>
                        <div class="role-desc">{{ $role['description'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Endpoints -->
        @foreach($docs['endpoints'] as $gi => $group)
            <div class="endpoint-group" id="group-{{ $gi }}" data-group-index="{{ $gi }}">
                <div class="endpoint-group-header">
                    <div class="group-icon" style="background: {{ $group['color'] }}">
                        <i class="fas {{ $group['icon'] }}"></i>
                    </div>
                    <h2>{{ $group['group'] }}</h2>
                    <span class="endpoint-count">{{ count($group['items']) }} endpoints</span>
                </div>

                @foreach($group['items'] as $ei => $ep)
                    <div class="endpoint-card" id="ep-{{ $gi }}-{{ $ei }}"
                        data-search="{{ strtolower($ep['method'] . ' ' . $ep['path'] . ' ' . $ep['title'] . ' ' . $ep['description']) }}">
                        <div class="endpoint-card-header" onclick="toggleCard(this)">
                            <span class="method-badge method-{{ $ep['method'] }}">{{ $ep['method'] }}</span>
                            <span class="endpoint-path">{{ $ep['path'] }}</span>
                            <span class="auth-badge {{ ($ep['auth'] ?? true) ? 'auth-required' : 'auth-public' }}">
                                {{ ($ep['auth'] ?? true) ? '🔒 Auth' : '🌐 Public' }}
                            </span>
                            <span class="endpoint-title">{{ $ep['title'] }}</span>
                            <i class="fas fa-chevron-down expand-icon"></i>
                        </div>
                        <div class="endpoint-card-body">
                            <div class="endpoint-desc">{{ $ep['description'] }}</div>

                            @if(!empty($ep['roles']))
                                <div class="roles-list">
                                    <span
                                        style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-right: 4px;">Roles:</span>
                                    @foreach($ep['roles'] as $role)
                                        <span class="role-badge">{{ $role }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($ep['params']))
                                <div class="param-section">
                                    <h4><i class="fas fa-question-circle"></i> Query Parameters</h4>
                                    <table class="param-table">
                                        <thead>
                                            <tr>
                                                <th>Parameter</th>
                                                <th>Type</th>
                                                <th>Wajib</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ep['params'] as $param)
                                                <tr>
                                                    <td><span class="param-name">{{ $param['name'] }}</span></td>
                                                    <td><span class="param-type">{{ $param['type'] }}</span></td>
                                                    <td>
                                                        @if($param['required'] ?? false)
                                                            <span class="param-required">WAJIB</span>
                                                        @else
                                                            <span class="param-optional">opsional</span>
                                                        @endif
                                                    </td>
                                                    <td style="color: var(--text-secondary);">{{ $param['description'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if(!empty($ep['body']))
                                <div class="param-section">
                                    <h4><i class="fas fa-paper-plane"></i> Request Body</h4>
                                    <table class="param-table">
                                        <thead>
                                            <tr>
                                                <th>Parameter</th>
                                                <th>Type</th>
                                                <th>Wajib</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ep['body'] as $param)
                                                <tr>
                                                    <td><span class="param-name">{{ $param['name'] }}</span></td>
                                                    <td><span class="param-type">{{ $param['type'] }}</span></td>
                                                    <td>
                                                        @if($param['required'] ?? false)
                                                            <span class="param-required">WAJIB</span>
                                                        @else
                                                            <span class="param-optional">opsional</span>
                                                        @endif
                                                    </td>
                                                    <td style="color: var(--text-secondary);">{{ $param['description'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if(!empty($ep['response']))
                                <div class="response-section">
                                    <h4><i class="fas fa-check-circle"></i> Response (200 OK)</h4>
                                    <div class="response-code">
                                        <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                                        {!! formatJson($ep['response']) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <!-- Footer -->
        <div style="text-align: center; padding: 40px 0; color: var(--text-muted); font-size: 13px;">
            <p>&copy; {{ date('Y') }} Hibiscus Efsya. All rights reserved.</p>
        </div>
    </div>

    <!-- Scroll to top -->
    <button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script>
        // Toggle sidebar group
        function toggleGroup(el) {
            el.closest('.sidebar-group').classList.toggle('open');
        }

        // Toggle endpoint card
        function toggleCard(el) {
            el.closest('.endpoint-card').classList.toggle('open');
        }

        // Toggle mobile sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('visible');
        }
        function closeSidebarMobile() {
            if (window.innerWidth <= 900) {
                document.getElementById('sidebar').classList.remove('open');
                document.querySelector('.sidebar-overlay').classList.remove('visible');
            }
        }

        // Search/filter endpoints
        function filterEndpoints(query) {
            query = query.toLowerCase().trim();
            document.querySelectorAll('.endpoint-card').forEach(function (card) {
                var match = !query || card.dataset.search.indexOf(query) > -1;
                card.style.display = match ? '' : 'none';
            });
            document.querySelectorAll('.endpoint-group').forEach(function (grp) {
                var cards = grp.querySelectorAll('.endpoint-card');
                var anyVisible = Array.from(cards).some(function (c) { return c.style.display !== 'none'; });
                grp.style.display = anyVisible ? '' : 'none';
            });
            // Also filter sidebar items
            document.querySelectorAll('.sidebar-item').forEach(function (item) {
                var text = item.textContent.toLowerCase();
                item.style.display = (!query || text.indexOf(query) > -1) ? '' : 'none';
            });
        }

        // Copy response code
        function copyCode(btn) {
            var code = btn.parentElement.textContent.replace(' Copy', '').trim();
            if (navigator.clipboard) {
                navigator.clipboard.writeText(code);
            } else {
                var ta = document.createElement('textarea');
                ta.value = code;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(function () { btn.innerHTML = '<i class="far fa-copy"></i> Copy'; }, 2000);
        }

        // Scroll to top button
        window.addEventListener('scroll', function () {
            document.getElementById('scrollTop').classList.toggle('visible', window.scrollY > 300);
        });

        // Smooth scroll to anchor and open card
        document.addEventListener('click', function (e) {
            var link = e.target.closest('a[href^="#ep-"]');
            if (link) {
                e.preventDefault();
                var target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    target.classList.add('open');
                    setTimeout(function () {
                        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 50);
                    // Set active state
                    document.querySelectorAll('.sidebar-item').forEach(function (si) { si.classList.remove('active'); });
                    link.classList.add('active');
                }
            }
        });

        // Open first endpoint by default
        var firstCard = document.querySelector('.endpoint-card');
        if (firstCard) firstCard.classList.add('open');
    </script>
</body>

</html>