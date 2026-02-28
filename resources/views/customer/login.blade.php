<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Hibiscus Efsya</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #dbeafe 0%, #f0f4f8 50%, #ede9fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 24px;
            padding: 44px 36px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
        }

        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 36px;
        }

        .login-logo img {
            height: 38px;
        }

        .login-logo span {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e40af;
        }

        .login-card h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            text-align: center;
        }

        .login-card .subtitle {
            font-size: 0.95rem;
            color: #6b7280;
            margin-bottom: 32px;
            text-align: center;
            line-height: 1.5;
        }

        .form-label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #d1d5db;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            color: #1f2937;
            background: rgba(255, 255, 255, 0.6);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: #1e40af;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            margin-top: 24px;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .btn-submit:active {
            background: #1e3a8a;
            transform: scale(0.99);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
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

        .login-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 0.82rem;
            color: #9ca3af;
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .login-card {
                padding: 36px 24px;
            }
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-logo">
            <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo">
        </div>

        <h2>Selamat Datang</h2>
        <div class="subtitle">Masukkan nomor telepon yang terdaftar untuk mengakses portal customer Anda.</div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('customer.check.phone') }}">
            @csrf
            <label class="form-label">Nomor Telepon</label>
            <input type="tel" name="no_telp" class="form-input" placeholder="Contoh: 08123456789"
                value="{{ old('no_telp') }}" required autofocus inputmode="tel" autocomplete="tel">
            <button type="submit" class="btn-submit">Lanjutkan</button>
        </form>
        <div class="login-footer">Portal Customer Hibiscus Efsya</div>
    </div>
</body>

</html>