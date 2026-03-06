<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Hibiscus Efsya Sales</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon-rounded.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #eff6ff;
            --bg: #f8fafc;
            --bg-white: #ffffff;
            --text: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --sidebar-w: 280px;
            --header-h: 56px;
            --green: #16a34a;
            --green-bg: #f0fdf4;
            --blue: #2563eb;
            --blue-bg: #eff6ff;
            --yellow: #d97706;
            --yellow-bg: #fffbeb;
            --red: #dc2626;
            --red-bg: #fef2f2;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* HEADER */
        .header {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            height: var(--header-h);
            background: var(--bg-white);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; padding: 0 20px;
        }
        .header-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; color: var(--text);
            font-family: 'Poppins', sans-serif;
            font-weight: 700; font-size: 15px;
        }
        .header-brand img { height: 32px; }
        .header-brand .sep { color: var(--border); font-weight: 400; }
        .header-brand .api-label {
            font-size: 11px; font-weight: 600;
            background: var(--primary); color: #fff;
            padding: 2px 8px; border-radius: 4px;
            letter-spacing: 0.3px;
        }
        .header-right { margin-left: auto; display: flex; align-items: center; gap: 8px; }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 6px; font-size: 12px;
            font-weight: 600; text-decoration: none; border: 1px solid var(--border);
            cursor: pointer; background: var(--bg-white); color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif; transition: all 0.15s;
        }
        .btn:hover { background: var(--bg); }
        .btn-primary { background: var(--primary); color: #fff; border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .hamburger {
            display: none; background: none; border: none;
            font-size: 18px; color: var(--text); cursor: pointer; padding: 4px 8px;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed; top: var(--header-h); left: 0; bottom: 0;
            width: var(--sidebar-w); background: var(--bg-white);
            border-right: 1px solid var(--border);
            overflow-y: auto; z-index: 100;
            transition: transform 0.25s ease;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        .sidebar-search {
            position: sticky; top: 0; background: var(--bg-white);
            padding: 12px 14px; border-bottom: 1px solid var(--border-light); z-index: 1;
        }
        .sidebar-search input {
            width: 100%; padding: 8px 12px 8px 32px;
            border: 1px solid var(--border); border-radius: 6px;
            font-size: 13px; outline: none; background: var(--bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .sidebar-search input:focus { border-color: var(--primary); background: #fff; }
        .sidebar-search .s-icon {
            position: absolute; left: 26px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 12px;
        }
        .nav-group { border-bottom: 1px solid var(--border-light); }
        .nav-group-title {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; cursor: pointer;
            font-size: 12px; font-weight: 700; color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: 0.4px;
            user-select: none;
        }
        .nav-group-title:hover { color: var(--primary); }
        .nav-group-title .dot {
            width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0;
        }
        .nav-group-title .arr {
            margin-left: auto; font-size: 9px; transition: transform 0.2s;
        }
        .nav-group.open .arr { transform: rotate(90deg); }
        .nav-items { display: none; padding-bottom: 6px; }
        .nav-group.open .nav-items { display: block; }
        .nav-item {
            display: flex; align-items: center; gap: 8px;
            padding: 5px 14px 5px 30px; font-size: 13px;
            color: var(--text-secondary); text-decoration: none;
            cursor: pointer; transition: all 0.1s;
        }
        .nav-item:hover { color: var(--primary); background: var(--primary-light); }
        .nav-item.active { color: var(--primary); background: var(--primary-light); font-weight: 600; }
        .badge {
            font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 3px;
            letter-spacing: 0.3px; flex-shrink: 0; min-width: 32px; text-align: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .badge-GET { background: var(--green-bg); color: var(--green); }
        .badge-POST { background: var(--blue-bg); color: var(--blue); }
        .badge-PUT { background: var(--yellow-bg); color: var(--yellow); }
        .badge-DELETE { background: var(--red-bg); color: var(--red); }

        /* MAIN */
        .main {
            margin-left: var(--sidebar-w);
            margin-top: var(--header-h);
            padding: 28px 32px;
        }

        /* HERO */
        .hero {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 28px 32px;
            margin-bottom: 28px;
        }
        .hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 22px; font-weight: 700; margin-bottom: 6px;
        }
        .hero p { font-size: 14px; color: var(--text-secondary); margin-bottom: 18px; }
        .hero-tags { display: flex; gap: 8px; flex-wrap: wrap; }
        .hero-tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--bg); border: 1px solid var(--border);
            padding: 5px 12px; border-radius: 6px;
            font-size: 12px; font-weight: 500; color: var(--text-secondary);
        }
        .hero-tag i { font-size: 11px; color: var(--primary); }

        /* INFO CARD */
        .info-card {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 24px 28px;
            margin-bottom: 28px;
        }
        .info-card h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 15px; font-weight: 600; margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px;
        }
        .info-card h3 i { color: var(--primary); font-size: 14px; }
        .info-card p { font-size: 13px; color: var(--text-secondary); }
        .code-block {
            background: #0f172a; color: #e2e8f0;
            padding: 14px 16px; border-radius: 6px;
            font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
            font-size: 12.5px; margin-top: 10px; overflow-x: auto;
            line-height: 1.7;
        }
        .code-block .c-comment { color: #64748b; }
        .code-block .c-key { color: #f472b6; }
        .code-block .c-val { color: #7dd3fc; }
        .code-block .c-token { color: #fbbf24; }
        .roles-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 8px; margin-top: 14px;
        }
        .role-item {
            background: var(--bg); border-radius: 6px; padding: 10px 14px;
        }
        .role-item .rn {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px; font-weight: 700; color: var(--primary);
        }
        .role-item .rd { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }

        /* ENDPOINT GROUP */
        .ep-group { margin-bottom: 36px; }
        .ep-group-title {
            display: flex; align-items: center; gap: 10px;
            padding-bottom: 10px; margin-bottom: 14px;
            border-bottom: 1px solid var(--border);
            font-family: 'Poppins', sans-serif;
        }
        .ep-group-title .gi {
            width: 30px; height: 30px; border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; color: #fff; flex-shrink: 0;
        }
        .ep-group-title h2 { font-size: 17px; font-weight: 700; }
        .ep-group-title .cnt {
            margin-left: auto; font-size: 11px; font-weight: 600;
            color: var(--text-muted); background: var(--bg);
            padding: 3px 10px; border-radius: 12px;
        }

        /* ENDPOINT CARD */
        .ep-card {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 8px;
            transition: border-color 0.15s;
        }
        .ep-card:hover { border-color: #cbd5e1; }
        .ep-card.open { border-color: var(--primary); }
        .ep-head {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; cursor: pointer; user-select: none;
        }
        .ep-head .badge {
            font-size: 10px; padding: 3px 8px; min-width: 46px;
        }
        .ep-head .path {
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 13px; font-weight: 500; color: var(--text);
        }
        .ep-head .auth-tag {
            font-size: 10px; padding: 2px 7px; border-radius: 3px; font-weight: 600;
        }
        .tag-auth { background: var(--yellow-bg); color: var(--yellow); }
        .tag-public { background: var(--green-bg); color: var(--green); }
        .ep-head .title {
            margin-left: auto; font-size: 12px; color: var(--text-muted); font-weight: 500;
            white-space: nowrap;
        }
        .ep-head .chev {
            color: var(--text-muted); font-size: 10px;
            transition: transform 0.2s; flex-shrink: 0;
        }
        .ep-card.open .chev { transform: rotate(180deg); }

        .ep-body {
            display: none; padding: 0 16px 16px;
            border-top: 1px solid var(--border-light);
        }
        .ep-card.open .ep-body { display: block; }
        .ep-desc { font-size: 13px; color: var(--text-secondary); padding: 14px 0 10px; }

        .ep-roles {
            display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 12px; align-items: center;
        }
        .ep-roles .label { font-size: 11px; font-weight: 600; color: var(--text-muted); margin-right: 2px; }
        .role-pill {
            font-size: 10px; padding: 2px 8px; border-radius: 3px;
            background: #f0e7fe; color: #7c3aed; font-weight: 600;
        }

        /* PARAM TABLE */
        .section-title {
            font-size: 12px; font-weight: 700; color: var(--text);
            margin-bottom: 8px; display: flex; align-items: center; gap: 6px;
        }
        .section-title i { color: var(--primary); font-size: 11px; }

        .p-table {
            width: 100%; border-collapse: collapse;
            font-size: 13px; margin-bottom: 14px;
        }
        .p-table th {
            text-align: left; padding: 7px 10px;
            background: var(--bg); font-weight: 600;
            border-bottom: 1px solid var(--border);
            color: var(--text-muted); font-size: 10px;
            text-transform: uppercase; letter-spacing: 0.4px;
        }
        .p-table td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: top;
        }
        .pn {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600; font-size: 12px; color: var(--text);
        }
        .pt {
            font-size: 10px; font-weight: 600;
            color: var(--primary); background: var(--primary-light);
            padding: 1px 6px; border-radius: 3px;
        }
        .pr { font-size: 10px; color: var(--red); font-weight: 700; }
        .po { font-size: 10px; color: var(--text-muted); }

        /* RESPONSE */
        .resp-block {
            background: #0f172a; color: #e2e8f0;
            padding: 14px 16px; border-radius: 6px;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 12px; overflow-x: auto;
            white-space: pre-wrap; word-break: break-word;
            line-height: 1.6; position: relative;
        }
        .cp-btn {
            position: absolute; top: 6px; right: 6px;
            background: rgba(255,255,255,0.08); border: none;
            color: #94a3b8; padding: 4px 8px; border-radius: 3px;
            cursor: pointer; font-size: 11px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .cp-btn:hover { background: rgba(255,255,255,0.15); color: #e2e8f0; }

        /* FOOTER */
        .footer {
            text-align: center; padding: 32px 0 24px;
            color: var(--text-muted); font-size: 12px;
        }

        /* SCROLL TOP */
        .scroll-top {
            position: fixed; bottom: 20px; right: 20px;
            width: 36px; height: 36px; border-radius: 8px;
            background: var(--bg-white); border: 1px solid var(--border);
            color: var(--text-secondary); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; opacity: 0; transition: all 0.2s; pointer-events: none;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .scroll-top.visible { opacity: 1; pointer-events: auto; }
        .scroll-top:hover { color: var(--primary); border-color: var(--primary); }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); box-shadow: 4px 0 16px rgba(0,0,0,0.08); }
            .main { margin-left: 0; padding: 20px 16px; }
            .hamburger { display: block; }
            .hero { padding: 20px; }
            .hero h1 { font-size: 18px; }
            .header-right .btn-text { display: none; }
        }
        @media (max-width: 600px) {
            .ep-head { flex-wrap: wrap; gap: 6px; }
            .ep-head .title { margin-left: 0; width: 100%; order: 9; }
        }

        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.2); z-index: 99;
        }
        .sidebar-overlay.visible { display: block; }
    </style>
</head>

<body>

<!-- Header -->
<header class="header">
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
        <i class="fas fa-bars"></i>
    </button>
    <a href="{{ url('/docs') }}" class="header-brand">
        <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo">
        <span class="sep">|</span>
        <span class="api-label">API v1</span>
        Documentation
    </a>
    <div class="header-right">
        <a href="{{ url('/docs/json') }}" target="_blank" class="btn">
            <i class="fas fa-code"></i> <span class="btn-text">JSON</span>
        </a>
        <a href="{{ url('/docs/download') }}" class="btn btn-primary">
            <i class="fas fa-download"></i> <span class="btn-text">Download</span>
        </a>
    </div>
</header>

<!-- Overlay -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-search">
        <i class="fas fa-search s-icon"></i>
        <input type="text" id="searchInput" placeholder="Cari endpoint..." oninput="filterEndpoints(this.value)">
    </div>
    @foreach($docs['endpoints'] as $gi => $group)
    <div class="nav-group open" data-group="{{ $gi }}">
        <div class="nav-group-title" onclick="toggleGroup(this)">
            <span class="dot" style="background:{{ $group['color'] }}"></span>
            <span>{{ $group['group'] }}</span>
            <i class="fas fa-chevron-right arr"></i>
        </div>
        <div class="nav-items">
            @foreach($group['items'] as $ei => $ep)
            <a href="#ep-{{ $gi }}-{{ $ei }}" class="nav-item" onclick="closeMobile()">
                <span class="badge badge-{{ $ep['method'] }}">{{ $ep['method'] }}</span>
                <span>{{ $ep['title'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</nav>

<!-- Main -->
<div class="main">
    <!-- Hero -->
    <div class="hero">
        <h1>Hibiscus Efsya Sales API</h1>
        <p>Dokumentasi lengkap REST API untuk aplikasi mobile POS & Inventory Management System.</p>
        <div class="hero-tags">
            <span class="hero-tag"><i class="fas fa-tag"></i> v1.0.0</span>
            <span class="hero-tag"><i class="fas fa-link"></i> {{ url('/api/v1') }}</span>
            <span class="hero-tag"><i class="fas fa-shield-alt"></i> Bearer Token</span>
            <span class="hero-tag"><i class="fas fa-cube"></i> {{ collect($docs['endpoints'])->sum(function($g){ return count($g['items']); }) }} Endpoints</span>
        </div>
    </div>

    <!-- Auth -->
    <div class="info-card" id="authentication">
        <h3><i class="fas fa-lock"></i> Authentication</h3>
        <p>{{ $docs['authentication']['description'] }}</p>
        <div class="code-block"><span class="c-comment">// Header yang harus dikirim di setiap request</span>
<span class="c-key">Authorization</span>: <span class="c-val">Bearer</span> <span class="c-token">{token_dari_login}</span></div>

        <h3 style="margin-top:20px"><i class="fas fa-user-shield"></i> Roles</h3>
        <div class="roles-grid">
            @foreach($docs['roles'] as $role)
            <div class="role-item">
                <div class="rn">{{ $role['role'] }}</div>
                <div class="rd">{{ $role['description'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Endpoints -->
    @foreach($docs['endpoints'] as $gi => $group)
    <div class="ep-group" id="group-{{ $gi }}" data-group-index="{{ $gi }}">
        <div class="ep-group-title">
            <div class="gi" style="background:{{ $group['color'] }}">
                <i class="fas {{ $group['icon'] }}"></i>
            </div>
            <h2>{{ $group['group'] }}</h2>
            <span class="cnt">{{ count($group['items']) }}</span>
        </div>

        @foreach($group['items'] as $ei => $ep)
        <div class="ep-card" id="ep-{{ $gi }}-{{ $ei }}" data-search="{{ strtolower($ep['method'].' '.$ep['path'].' '.$ep['title'].' '.$ep['description']) }}">
            <div class="ep-head" onclick="toggleCard(this)">
                <span class="badge badge-{{ $ep['method'] }}">{{ $ep['method'] }}</span>
                <span class="path">{{ $ep['path'] }}</span>
                <span class="auth-tag {{ ($ep['auth'] ?? true) ? 'tag-auth' : 'tag-public' }}">
                    {{ ($ep['auth'] ?? true) ? 'Auth' : 'Public' }}
                </span>
                <span class="title">{{ $ep['title'] }}</span>
                <i class="fas fa-chevron-down chev"></i>
            </div>
            <div class="ep-body">
                <div class="ep-desc">{{ $ep['description'] }}</div>

                @if(!empty($ep['roles']))
                <div class="ep-roles">
                    <span class="label">Roles:</span>
                    @foreach($ep['roles'] as $role)
                    <span class="role-pill">{{ $role }}</span>
                    @endforeach
                </div>
                @endif

                @if(!empty($ep['params']))
                <div style="margin-bottom:14px">
                    <div class="section-title"><i class="fas fa-question-circle"></i> Query Parameters</div>
                    <table class="p-table">
                        <thead><tr><th>Parameter</th><th>Type</th><th>Wajib</th><th>Keterangan</th></tr></thead>
                        <tbody>
                        @foreach($ep['params'] as $param)
                        <tr>
                            <td><span class="pn">{{ $param['name'] }}</span></td>
                            <td><span class="pt">{{ $param['type'] }}</span></td>
                            <td>@if($param['required'] ?? false)<span class="pr">WAJIB</span>@else<span class="po">opsional</span>@endif</td>
                            <td style="color:var(--text-secondary)">{{ $param['description'] }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if(!empty($ep['body']))
                <div style="margin-bottom:14px">
                    <div class="section-title"><i class="fas fa-paper-plane"></i> Request Body</div>
                    <table class="p-table">
                        <thead><tr><th>Parameter</th><th>Type</th><th>Wajib</th><th>Keterangan</th></tr></thead>
                        <tbody>
                        @foreach($ep['body'] as $param)
                        <tr>
                            <td><span class="pn">{{ $param['name'] }}</span></td>
                            <td><span class="pt">{{ $param['type'] }}</span></td>
                            <td>@if($param['required'] ?? false)<span class="pr">WAJIB</span>@else<span class="po">opsional</span>@endif</td>
                            <td style="color:var(--text-secondary)">{{ $param['description'] }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if(!empty($ep['response']))
                <div>
                    <div class="section-title"><i class="fas fa-check-circle" style="color:var(--green)"></i> Response (200 OK)</div>
                    <div class="resp-block"><button class="cp-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>{!! formatJson($ep['response']) !!}</div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endforeach

    <div class="footer">&copy; {{ date('Y') }} Hibiscus Efsya. All rights reserved.</div>
</div>

<button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="fas fa-chevron-up"></i>
</button>

<script>
function toggleGroup(el) { el.closest('.nav-group').classList.toggle('open'); }
function toggleCard(el) { el.closest('.ep-card').classList.toggle('open'); }
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('visible');
}
function closeMobile() {
    if (window.innerWidth <= 900) {
        document.getElementById('sidebar').classList.remove('open');
        document.querySelector('.sidebar-overlay').classList.remove('visible');
    }
}
function filterEndpoints(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.ep-card').forEach(function(c) {
        c.style.display = (!q || c.dataset.search.indexOf(q) > -1) ? '' : 'none';
    });
    document.querySelectorAll('.ep-group').forEach(function(g) {
        var cards = g.querySelectorAll('.ep-card');
        g.style.display = Array.from(cards).some(function(c){ return c.style.display !== 'none'; }) ? '' : 'none';
    });
    document.querySelectorAll('.nav-item').forEach(function(i) {
        i.style.display = (!q || i.textContent.toLowerCase().indexOf(q) > -1) ? '' : 'none';
    });
}
function copyCode(btn) {
    var code = btn.parentElement.textContent.replace(' Copy','').trim();
    if (navigator.clipboard) { navigator.clipboard.writeText(code); }
    else { var t=document.createElement('textarea'); t.value=code; document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t); }
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(function(){ btn.innerHTML = '<i class="far fa-copy"></i> Copy'; }, 1500);
}
window.addEventListener('scroll', function() {
    document.getElementById('scrollTop').classList.toggle('visible', window.scrollY > 300);
});
document.addEventListener('click', function(e) {
    var link = e.target.closest('a[href^="#ep-"]');
    if (link) {
        e.preventDefault();
        var t = document.querySelector(link.getAttribute('href'));
        if (t) {
            t.classList.add('open');
            setTimeout(function(){ t.scrollIntoView({behavior:'smooth',block:'start'}); }, 50);
            document.querySelectorAll('.nav-item').forEach(function(n){ n.classList.remove('active'); });
            link.classList.add('active');
        }
    }
});
var fc = document.querySelector('.ep-card');
if (fc) fc.classList.add('open');
</script>
</body>
</html>
