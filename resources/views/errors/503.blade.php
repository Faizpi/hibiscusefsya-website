<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Maintenance - Hibiscus Efsya</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon-rounded.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon-rounded.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 35%, #C084FC 70%, #EC4899 100%);
            overflow: hidden;
            position: relative;
        }

        /* Decorative circles */
        body::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -15%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -25%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.06) 0%, transparent 70%);
            border-radius: 50%;
        }

        .maintenance-container {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 40px 30px;
            max-width: 600px;
            width: 100%;
        }

        .logo-box {
            background: transparent;
            border-radius: 24px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: inline-block;
        }

        .logo-box img {
            max-width: 220px;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2));
        }

        .maintenance-title {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 12px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .maintenance-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 35px;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Progress bar animation */
        .progress-bar-wrapper {
            max-width: 300px;
            margin: 0 auto 35px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            height: 6px;
            overflow: hidden;
        }

        .progress-bar-inner {
            height: 100%;
            width: 30%;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50px;
            animation: loading 2.5s ease-in-out infinite;
        }

        @keyframes loading {
            0% {
                width: 10%;
                margin-left: 0;
            }

            50% {
                width: 40%;
                margin-left: 30%;
            }

            100% {
                width: 10%;
                margin-left: 90%;
            }
        }

        /* Info cards */
        .info-cards {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 35px;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 18px 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.3s ease;
        }

        .info-card i {
            color: #7C3AED;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .info-card span {
            color: #4a1942;
            font-weight: 600;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        /* Footer */
        .maintenance-footer {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            margin-top: 10px;
        }

        /* Mobile */
        @media (max-width: 575.98px) {
            .maintenance-container {
                padding: 30px 20px;
            }

            .logo-box img {
                max-width: 160px;
            }

            .maintenance-title {
                font-size: 1.5rem;
            }

            .maintenance-subtitle {
                font-size: 0.95rem;
                margin-bottom: 25px;
            }

            .info-cards {
                flex-direction: column;
                align-items: center;
            }

            .info-card {
                width: 100%;
                max-width: 250px;
                justify-content: center;
            }

        }
    </style>
</head>

<body>

    {{-- Main content --}}
    <div class="maintenance-container">
        <div class="logo-box">
            <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Hibiscus Efsya Logo">
        </div>

        <h1 class="maintenance-title">Sedang Dalam Pemeliharaan</h1>
        <p class="maintenance-subtitle">
            Kami sedang melakukan peningkatan sistem untuk memberikan pengalaman yang lebih baik.
            Silakan kembali beberapa saat lagi.
        </p>

        <div class="progress-bar-wrapper">
            <div class="progress-bar-inner"></div>
        </div>

        <div class="info-cards">
            <div class="info-card">
                <i class="fas fa-tools"></i>
                <span>Update Sistem</span>
            </div>
            <div class="info-card">
                <i class="fas fa-clock"></i>
                <span>Segera Kembali</span>
            </div>
            <div class="info-card">
                <i class="fas fa-check-circle"></i>
                <span>Tetap Aman</span>
            </div>
        </div>

        <p class="maintenance-footer">
            &copy; {{ date('Y') }} Hibiscus Efsya. All rights reserved.
        </p>
    </div>

</body>

</html>