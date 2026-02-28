<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Hibiscus Efsya</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-container {
            width: 100%;
            max-width: 900px;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            display: flex;
            min-height: 420px;
        }
        .login-left {
            flex: 1;
            background: #1e40af;
            color: #fff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-left h2 {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 16px;
        }
        .login-left p {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
            line-height: 1.6;
        }
        .login-left .brand {
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.6);
            margin-bottom: 32px;
        }
        .login-right {
            flex: 1;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-right h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .login-right .subtitle {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 28px;
        }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .phone-input {
            display: flex;
            align-items: center;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .phone-input:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .phone-prefix {
            background: #f9fafb;
            padding: 12px 14px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            border-right: 1.5px solid #d1d5db;
            white-space: nowrap;
        }
        .phone-input input {
            flex: 1;
            border: none;
            outline: none;
            padding: 12px 14px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
            background: transparent;
        }
        .phone-input input::placeholder { color: #9ca3af; }
        .btn-submit {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: #1e40af;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 24px;
        }
        .btn-submit:hover { background: #1d4ed8; }
        .btn-submit:active { background: #1e3a8a; }
        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        @media (max-width: 640px) {
            body { padding: 16px; }
            .login-container {
                flex-direction: column;
                max-width: 440px;
            }
            .login-left {
                padding: 32px 28px 28px;
            }
            .login-left h2 { font-size: 1.35rem; }
            .login-left .brand { margin-bottom: 20px; }
            .login-right { padding: 28px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="brand">Hibiscus Efsya</div>
            <h2>Selamat Datang di Portal Customer</h2>
            <p>Pantau seluruh riwayat transaksi pembelian Anda di sini. Login dengan nomor telepon yang terdaftar untuk mengakses akun Anda.</p>
        </div>
        <div class="login-right">
            <h3>Masukkan Nomor HP</h3>
            <div class="subtitle">Gunakan nomor yang terdaftar di Hibiscus Efsya</div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('customer.check.phone') }}">
                @csrf
                <label class="form-label">Nomor Telepon</label>
                <div class="phone-input">
                    <span class="phone-prefix">+62</span>
                    <input type="tel" name="no_telp" placeholder="Masukkan nomor HP di sini"
                           value="{{ old('no_telp') }}" required autofocus
                           inputmode="numeric" autocomplete="tel">
                </div>
                <button type="submit" class="btn-submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
