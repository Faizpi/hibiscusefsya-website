<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- SEO Meta Tags -->
        <title>Hibiscus Efsya</title>
        <meta name="description" content="Kami adalah perusahaan yang bergerak di berbagai bidang usaha dengan fokus pada pengembangan kemitraan dan franchise yang menguntungkan. Bergerak di bidang Body Care, Fashion, Travel, dan Technology.">
        <meta name="keywords" content="Hibiscus Efsya, Body Care, Fashion, Travel, Technology, Franchise, Kemitraan, Bisnis">
        <meta name="author" content="Hibiscus Efsya">
        <meta name="robots" content="index, follow">
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="Hibiscus Efsya">
        <meta property="og:description" content="Kami adalah perusahaan yang bergerak di berbagai bidang usaha dengan fokus pada pengembangan kemitraan dan franchise yang menguntungkan. Bergerak di bidang Body Care, Fashion, Travel, dan Technology.">
        <meta property="og:site_name" content="Hibiscus Efsya">
        <meta property="og:locale" content="id_ID">
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Hibiscus Efsya">
        <meta name="twitter:description" content="Kami adalah perusahaan yang bergerak di berbagai bidang usaha dengan fokus pada pengembangan kemitraan dan franchise yang menguntungkan. Bergerak di bidang Body Care, Fashion, Travel, dan Technology.">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    Laravel
                </div>

                <div class="links">
                    <a href="https://laravel.com/docs">Docs</a>
                    <a href="https://laracasts.com">Laracasts</a>
                    <a href="https://laravel-news.com">News</a>
                    <a href="https://blog.laravel.com">Blog</a>
                    <a href="https://nova.laravel.com">Nova</a>
                    <a href="https://forge.laravel.com">Forge</a>
                    <a href="https://vapor.laravel.com">Vapor</a>
                    <a href="https://github.com/laravel/laravel">GitHub</a>
                </div>
            </div>
        </div>
    </body>
</html>
