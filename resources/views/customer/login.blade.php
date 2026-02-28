<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Hibiscus Efsya Customer Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #e91e63; --primary-dark: #c2185b; }
        body {
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 50%, #f48fb1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .login-header h3 { font-weight: 700; margin-bottom: 0.3rem; }
        .login-header p { opacity: 0.85; margin-bottom: 0; font-size: 0.9rem; }
        .login-body { padding: 2rem; }
        .login-icon {
            width: 70px; height: 70px; border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
        }
        .btn-login {
            background: var(--primary); border-color: var(--primary);
            color: #fff; font-weight: 600; padding: 0.7rem;
            font-size: 1rem; border-radius: 8px;
        }
        .btn-login:hover { background: var(--primary-dark); border-color: var(--primary-dark); color: #fff; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(233,30,99,0.25); }
        .input-group-text { background: #fce4ec; border-color: #ddd; color: var(--primary); }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-user fa-2x"></i>
            </div>
            <h3>Portal Customer</h3>
            <p>Hibiscus Efsya</p>
        </div>
        <div class="login-body">
            @if(session('error'))
                <div class="alert alert-danger py-2 small">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success py-2 small">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('customer.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label class="font-weight-bold small">No. Telepon</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        </div>
                        <input type="text" name="no_telp" class="form-control" 
                               placeholder="Masukkan no. telepon"
                               value="{{ old('no_telp') }}" required autofocus>
                    </div>
                    @error('no_telp')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small">PIN (6 digit)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="password" name="pin" class="form-control" 
                               placeholder="Masukkan PIN 6 digit"
                               maxlength="6" required>
                    </div>
                    @error('pin')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <button type="submit" class="btn btn-login btn-block mt-4">
                    <i class="fas fa-sign-in-alt mr-1"></i> Masuk
                </button>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted">
                    Hubungi admin untuk mendapatkan akun customer.
                </small>
            </div>
        </div>
    </div>
</body>
</html>
