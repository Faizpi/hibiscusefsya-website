<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Masukkan PIN - Hibiscus Efsya</title>
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
        .pin-container {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            text-align: center;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 500;
            margin-bottom: 28px;
            transition: color 0.2s;
        }
        .back-link:hover { color: #1e40af; }
        .back-link i { margin-right: 6px; font-size: 0.75rem; }
        .pin-container h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 6px;
        }
        .pin-container .desc {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.5;
        }
        .pin-container .desc strong { color: #1f2937; }
        .pin-boxes {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .pin-box {
            width: 48px;
            height: 56px;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            -webkit-text-security: disc;
        }
        .pin-box:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .pin-box.filled { border-color: #1e40af; }
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
        }
        .btn-submit:hover { background: #1d4ed8; }
        .btn-submit:active { background: #1e3a8a; }
        .btn-submit:disabled { background: #93c5fd; cursor: not-allowed; }
        .forgot-pin {
            display: block;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #6b7280;
        }
        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 20px;
            text-align: left;
        }
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        @media (max-width: 480px) {
            body { padding: 16px; }
            .pin-container { padding: 32px 24px; }
            .pin-box { width: 42px; height: 50px; font-size: 1.1rem; }
            .pin-boxes { gap: 8px; }
        }
    </style>
</head>
<body>
    <div class="pin-container">
        <a href="{{ route('customer.login') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        <h2>Masukkan PIN</h2>
        <div class="desc">
            Silakan masukkan 6 digit kode PIN akun<br>
            <strong>{{ $nama ?? '' }}</strong>
        </div>

        @if(isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        <form method="POST" action="{{ route('customer.login.submit') }}" id="pinForm">
            @csrf
            <input type="hidden" name="no_telp" value="{{ $no_telp }}">
            <input type="hidden" name="pin" id="pinValue">

            <div class="pin-boxes">
                <input type="text" class="pin-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="0" autofocus>
                <input type="text" class="pin-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1">
                <input type="text" class="pin-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2">
                <input type="text" class="pin-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3">
                <input type="text" class="pin-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4">
                <input type="text" class="pin-box" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5">
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit" disabled>Lanjutkan</button>
        </form>

        <span class="forgot-pin">Lupa PIN? Hubungi sales Anda</span>
    </div>

    <script>
        (function() {
            var boxes = document.querySelectorAll('.pin-box');
            var pinValue = document.getElementById('pinValue');
            var btnSubmit = document.getElementById('btnSubmit');

            function updatePin() {
                var pin = '';
                boxes.forEach(function(b) { pin += b.value; });
                pinValue.value = pin;
                btnSubmit.disabled = pin.length < 6;
                boxes.forEach(function(b) {
                    if (b.value) b.classList.add('filled');
                    else b.classList.remove('filled');
                });
            }

            boxes.forEach(function(box, i) {
                box.addEventListener('input', function(e) {
                    var val = this.value.replace(/[^0-9]/g, '');
                    this.value = val.charAt(0) || '';
                    updatePin();
                    if (val && i < 5) boxes[i + 1].focus();
                });

                box.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && i > 0) {
                        boxes[i - 1].focus();
                        boxes[i - 1].value = '';
                        updatePin();
                    }
                });

                box.addEventListener('paste', function(e) {
                    e.preventDefault();
                    var paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                    for (var j = 0; j < 6 && j < paste.length; j++) {
                        boxes[j].value = paste[j];
                    }
                    updatePin();
                    var next = Math.min(paste.length, 5);
                    boxes[next].focus();
                });

                box.addEventListener('focus', function() { this.select(); });
            });

            // Auto-submit when all 6 filled
            pinValue.addEventListener = pinValue.addEventListener;
            var observer = new MutationObserver(function() {});
            setInterval(function() {
                if (pinValue.value.length === 6 && btnSubmit.disabled === false) {
                    // Small delay then auto-submit
                }
            }, 100);
        })();
    </script>
</body>
</html>
