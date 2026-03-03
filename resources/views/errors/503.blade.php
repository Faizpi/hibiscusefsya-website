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
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #2563eb 100%);
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
            max-width: 200px;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2));
        }

        .gear-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            margin-bottom: 25px;
            animation: pulse 3s ease-in-out infinite;
        }

        .gear-icon i {
            font-size: 2.5rem;
            color: #ffffff;
            animation: spin 8s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.2);
            }

            50% {
                box-shadow: 0 0 0 20px rgba(255, 255, 255, 0);
            }
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

        .info-card:hover {
            transform: translateY(-4px);
        }

        .info-card i {
            color: #3b82f6;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .info-card span {
            color: #1e3a5f;
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

        /* Floating cards */
        .floating-card {
            position: absolute;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 14px 20px;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .floating-card i {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
        }

        .floating-card span {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            font-size: 0.8rem;
        }

        .floating-card.fc-1 {
            top: 8%;
            left: 8%;
            animation: float1 6s ease-in-out infinite;
        }

        .floating-card.fc-2 {
            top: 15%;
            right: 6%;
            animation: float2 5.5s ease-in-out infinite 1s;
        }

        .floating-card.fc-3 {
            bottom: 20%;
            left: 5%;
            animation: float3 6.5s ease-in-out infinite 0.5s;
        }

        .floating-card.fc-4 {
            bottom: 10%;
            right: 8%;
            animation: float4 5s ease-in-out infinite 2s;
        }

        @keyframes float1 {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        @keyframes float2 {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes float3 {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes float4 {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        /* Progress bar animation */
        .progress-bar-wrapper {
            max-width: 300px;
            margin: 0 auto 30px;
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

        /* Mobile */
        @media (max-width: 575.98px) {
            .maintenance-container {
                padding: 30px 20px;
            }

            .logo-box img {
                max-width: 140px;
            }

            .gear-icon {
                width: 70px;
                height: 70px;
            }

            .gear-icon i {
                font-size: 2rem;
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

            .floating-card {
                display: none;
            }
        }

        @media (max-width: 991.98px) and (min-width: 576px) {
            .floating-card {
                padding: 10px 14px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body>

    {{-- Floating background cards --}}
    <div class="floating-card fc-1">
        <i class="fas fa-chart-line"></i>
        <span>Laporan Real-time</span>
    </div>
    <div class="floating-card fc-2">
        <i class="fas fa-file-invoice"></i>
        <span>Invoice Otomatis</span>
    </div>
    <div class="floating-card fc-3">
        <i class="fas fa-cloud"></i>
        <span>Cloud Storage</span>
    </div>
    <div class="floating-card fc-4">
        <i class="fas fa-shield-alt"></i>
        <span>Data Aman</span>
    </div>

    {{-- Main content --}}
    <div class="maintenance-container">
        <div class="logo-box">
            <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Hibiscus Efsya Logo">
        </div>

        <div class="gear-icon">
            <i class="fas fa-cog"></i>
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
