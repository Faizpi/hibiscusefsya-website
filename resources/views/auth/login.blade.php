@extends('layouts.app')

@section('content')

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        min-height: 100vh;
    }
    
    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .login-container {
        width: 100%;
        max-width: 1000px;
    }
    
    /* Desktop: Side by side layout */
    .login-card {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .login-left {
        background: linear-gradient(135deg, #1a1c2c 0%, #2d3250 100%);
        padding: 60px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        min-height: 500px;
    }
    
    .login-left img {
        max-width: 220px;
        margin-bottom: 30px;
        filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
    }
    
    .login-left h2 {
        color: #ffffff;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .login-left p {
        color: rgba(255,255,255,0.7);
        font-size: 0.95rem;
        line-height: 1.6;
    }
    
    .login-right {
        padding: 50px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .login-right h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a1c2c;
        margin-bottom: 8px;
    }
    
    .login-right .subtitle {
        color: #6c757d;
        margin-bottom: 35px;
        font-size: 0.95rem;
    }
    
    .form-group-custom {
        margin-bottom: 20px;
    }
    
    .form-group-custom label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    
    .form-group-custom .input-wrapper {
        position: relative;
    }
    
    .form-group-custom .input-wrapper i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1rem;
    }
    
    .form-group-custom input {
        width: 100%;
        padding: 14px 16px 14px 45px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f9fafb;
    }
    
    .form-group-custom input:focus {
        outline: none;
        border-color: #667eea;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .form-group-custom input.is-invalid {
        border-color: #ef4444;
    }
    
    .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .custom-checkbox-modern {
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    
    .custom-checkbox-modern input {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        accent-color: #667eea;
        cursor: pointer;
    }
    
    .custom-checkbox-modern span {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .btn-login {
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
        color: #ffffff;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }
    
    .btn-login:active {
        transform: translateY(0);
    }
    
    .divider {
        display: flex;
        align-items: center;
        margin: 25px 0;
    }
    
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .divider span {
        padding: 0 15px;
        color: #9ca3af;
        font-size: 0.85rem;
    }
    
    .footer-text {
        text-align: center;
        color: #9ca3af;
        font-size: 0.85rem;
        margin-top: 20px;
    }
    
    /* Mobile Styles */
    @media (max-width: 991.98px) {
        .login-wrapper {
            padding: 15px;
        }
        
        .login-left {
            padding: 40px 30px;
            min-height: auto;
        }
        
        .login-left img {
            max-width: 160px;
            margin-bottom: 20px;
        }
        
        .login-left h2 {
            font-size: 1.25rem;
        }
        
        .login-left p {
            font-size: 0.9rem;
        }
        
        .login-right {
            padding: 35px 25px;
        }
        
        .login-right h1 {
            font-size: 1.5rem;
        }
    }
    
    @media (max-width: 575.98px) {
        .login-card {
            border-radius: 16px;
        }
        
        .login-left {
            padding: 30px 20px;
        }
        
        .login-left img {
            max-width: 140px;
        }
        
        .login-right {
            padding: 30px 20px;
        }
        
        .form-group-custom input {
            padding: 12px 14px 12px 42px;
        }
        
        .btn-login {
            padding: 12px 20px;
        }
    }
</style>

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-card">
            <div class="row no-gutters">
                
                {{-- Left Side - Branding --}}
                <div class="col-lg-5 login-left">
                    <img src="{{ asset('assets/img/logoHE.png') }}" alt="Hibiscus Efsya Logo">
                    <h2>Hibiscus Efsya</h2>
                    <p>Platform Akuntansi Online Terpercaya untuk Bisnis Anda</p>
                </div>
                
                {{-- Right Side - Login Form --}}
                <div class="col-lg-7 login-right">
                    <h1>Selamat Datang! 👋</h1>
                    <p class="subtitle">Silakan masuk ke akun Anda untuk melanjutkan</p>
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="form-group-custom">
                            <label for="email">Email</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" 
                                    value="{{ old('email') }}" 
                                    placeholder="nama@email.com"
                                    class="@error('email') is-invalid @enderror" required>
                            </div>
                            @error('email')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>
                        
                        <div class="form-group-custom">
                            <label for="password">Kata Sandi</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" 
                                    placeholder="Masukkan kata sandi"
                                    class="@error('password') is-invalid @enderror" required>
                            </div>
                            @error('password')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>
                        
                        <div class="remember-forgot">
                            <label class="custom-checkbox-modern">
                                <input type="checkbox" name="remember" id="remember">
                                <span>Ingat Saya</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-login">
                            <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                        </button>
                        
                        <div class="divider">
                            <span>Hibiscus Efsya</span>
                        </div>
                        
                        <p class="footer-text">
                            © {{ date('Y') }} Hibiscus Efsya. All rights reserved.
                        </p>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
</div>

@endsection
