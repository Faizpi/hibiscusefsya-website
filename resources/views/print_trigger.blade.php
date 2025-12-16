<!DOCTYPE html>
<html>
<head>
    <title>Mencetak...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; }
        .btn { 
            background: #007bff; color: white; padding: 15px 30px; 
            text-decoration: none; border-radius: 5px; font-size: 18px; font-weight: bold;
        }
    </style>
</head>
<body>
    <h3>Siap Mencetak</h3>
    <p>Jika printer tidak merespon, klik tombol di bawah:</p>
    
    <a id="printBtn" href="rawbt:base64,{{ $base64 }}" class="btn">CETAK STRUK</a>

    <script>
        // Otomatis klik tombol saat halaman dimuat
        window.onload = function() {
            document.getElementById('printBtn').click();
        }
    </script>
</body>
</html>