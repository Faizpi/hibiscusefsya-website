@extends('layouts.app')

@section('content')

    <style>
        /* Override layout container untuk login page */
        body {
            background: #ffffff !important;
            min-height: 100vh;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
        }

        body>.container {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            width: 100vw;
            margin: 0;
            padding: 0;
        }

        /* Left Side - Form */
        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 40px;
            background: linear-gradient(180deg, #ffffff 0%, #f0f7ff 50%, #dbeafe 100%);
            position: relative;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 35px;
            line-height: 1.5;
        }

        .form-group-login {
            margin-bottom: 22px;
        }

        .form-group-login label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
        }

        .input-field {
            width: 100%;
            padding: 16px 22px;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 1rem;
            background: #ffffff;
            transition: all 0.3s ease;
            color: #1e293b;
        }

        .input-field::placeholder {
            color: #94a3b8;
        }

        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .input-field.is-invalid {
            border-color: #ef4444;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .input-field {
            padding-right: 50px;
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            font-size: 1.1rem;
        }

        .password-toggle:hover {
            color: #3b82f6;
        }

        .btn-submit {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 50px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .login-footer {
            margin-top: 35px;
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
        }

        /* Right Side - Branding */
        .login-right {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #1e40af 100%);
        }

        .login-right-content {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 40px;
        }

        .logo-box {
            background: #ffffff;
            border-radius: 24px;
            padding: 30px 40px;
            margin-bottom: 30px;
            display: inline-block;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .logo-box img {
            max-width: 200px;
            height: auto;
        }

        .login-right-content h2 {
            color: #ffffff;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .login-right-content p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            line-height: 1.6;
            max-width: 320px;
            margin: 0 auto 25px;
        }

        /* Feature badge */
        .feature-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50px;
            padding: 12px 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-badge i {
            color: #3b82f6;
            margin-right: 10px;
            font-size: 1rem;
        }

        .feature-badge span {
            color: #1e3a5f;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Decorative circles */
        .login-right::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .login-right::after {
            content: '';
            position: absolute;
            bottom: -25%;
            left: -15%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.06) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* Floating cards */
        .floating-card {
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 14px 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            z-index: 3;
            display: flex;
            align-items: center;
        }

        .floating-card.card-1 {
            top: 8%;
            right: 12%;
            animation: float1 5s ease-in-out infinite;
        }

        .floating-card.card-2 {
            top: 20%;
            left: 5%;
            animation: float2 6s ease-in-out infinite 1s;
        }

        .floating-card.card-3 {
            bottom: 25%;
            right: 5%;
            animation: float3 5.5s ease-in-out infinite 0.5s;
        }

        .floating-card.card-4 {
            bottom: 12%;
            left: 10%;
            animation: float4 6.5s ease-in-out infinite 2s;
        }

        .floating-card.card-5 {
            top: 45%;
            left: 3%;
            animation: float5 5s ease-in-out infinite 1.5s;
        }

        .floating-card i {
            color: #3b82f6;
            margin-right: 10px;
            font-size: 1rem;
        }

        .floating-card span {
            color: #1e3a5f;
            font-weight: 600;
            font-size: 0.85rem;
        }

        @keyframes float1 {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-15px) rotate(2deg);
            }
        }

        @keyframes float2 {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-12px) rotate(-2deg);
            }
        }

        @keyframes float3 {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-18px) rotate(3deg);
            }
        }

        @keyframes float4 {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-10px) rotate(-1deg);
            }
        }

        @keyframes float5 {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-14px) rotate(2deg);
            }
        }

        /* ========== MOBILE STYLES ========== */
        @media (max-width: 991.98px) {
            .login-page {
                flex-direction: column;
            }

            .login-right {
                order: -1;
                min-height: 300px;
                flex: none;
            }

            .login-right-content {
                padding: 30px 20px;
            }

            .logo-box {
                padding: 20px 30px;
                margin-bottom: 20px;
            }

            .logo-box img {
                max-width: 140px;
            }

            .login-right-content h2 {
                font-size: 1.4rem;
                margin-bottom: 8px;
            }

            .login-right-content p {
                font-size: 0.9rem;
                margin-bottom: 15px;
            }

            .feature-badge {
                padding: 10px 18px;
            }

            .feature-badge span {
                font-size: 0.8rem;
            }

            .floating-card {
                display: none;
            }

            .login-left {
                padding: 40px 25px;
                flex: none;
            }

            .login-form-wrapper {
                max-width: 100%;
            }

            .login-title {
                font-size: 1.6rem;
                text-align: center;
            }

            .login-subtitle {
                text-align: center;
                margin-bottom: 28px;
                font-size: 0.95rem;
            }
        }

        @media (max-width: 575.98px) {
            .login-right {
                min-height: 260px;
            }

            .logo-box {
                padding: 18px 25px;
                border-radius: 18px;
            }

            .logo-box img {
                max-width: 120px;
            }

            .login-right-content h2 {
                font-size: 1.25rem;
            }

            .login-right-content p {
                display: none;
            }

            .login-left {
                padding: 35px 20px;
            }

            .login-title {
                font-size: 1.4rem;
            }

            .login-subtitle {
                font-size: 0.9rem;
            }

            .input-field {
                padding: 14px 18px;
            }

            .password-wrapper .input-field {
                padding-right: 45px;
            }

            .btn-submit {
                padding: 14px 20px;
            }

            .login-footer {
                margin-top: 25px;
            }
        }
    </style>

    <div class="login-page">

        {{-- Left Side - Form --}}
        <div class="login-left">
            <div class="login-form-wrapper">
                <h1 class="login-title">Masuk ke Akun Anda</h1>
                <p class="login-subtitle">Selamat datang kembali! Silakan masukkan detail Anda.</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group-login">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="input-field @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="Masukkan email Anda" required>
                        @error('email')
                            <small class="text-danger mt-2 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group-login">
                        <label for="password">Kata Sandi</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password"
                                class="input-field @error('password') is-invalid @enderror"
                                placeholder="Masukkan kata sandi" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <small class="text-danger mt-2 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit" class="btn-submit">
                        Masuk
                    </button>
                </form>

                <p class="login-footer">
                    © {{ date('Y') }} Hibiscus Efsya. All rights reserved.
                </p>
            </div>
        </div>

        {{-- Right Side - Branding --}}
        <div class="login-right">
            <div class="floating-card card-1">
                <i class="fas fa-chart-line"></i>
                <span>Laporan Real-time</span>
            </div>
            <div class="floating-card card-2">
                <i class="fas fa-file-invoice"></i>
                <span>Invoice Otomatis</span>
            </div>
            <div class="floating-card card-3">
                <i class="fas fa-calculator"></i>
                <span>Hitung Cepat</span>
            </div>
            <div class="floating-card card-4">
                <i class="fas fa-cloud"></i>
                <span>Cloud Storage</span>
            </div>
            <div class="floating-card card-5">
                <i class="fas fa-users"></i>
                <span>Multi User</span>
            </div>

            <div class="login-right-content">
                <div class="logo-box">
                    <img src="{{ asset('assets/img/logoHE.png') }}" alt="Hibiscus Efsya Logo">
                </div>
                <h2>Hibiscus Efsya</h2>
                <p>Platform Akuntansi Online Terpercaya untuk Mengelola Bisnis Anda</p>
                <div class="feature-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Data Aman & Terpercaya</span>
                </div>
            </div>
        </div>

    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>

@endsection