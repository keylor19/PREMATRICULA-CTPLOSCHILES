<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Portal Académico Los Chiles</title>

    @fonts

    {{-- Styles / Scripts del proyecto --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #003366, #0059b3, #0a74da);
            color: white;
            overflow-x: hidden;
        }

        .page-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            width: 100%;
            padding: 25px 50px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        .nav {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: white;
            font-size: 15px;
            font-weight: 600;
            padding: 10px 20px;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 8px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(5px);
        }

        .nav-link:hover {
            background: white;
            color: #003366;
            border-color: white;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 30px;
        }

        .hero-card {
            width: 100%;
            max-width: 900px;
            padding: 50px 30px;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.10);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.20);
        }

        .logo-ctp {
            width: 260px;
            max-width: 85%;
            height: auto;
            margin: 0 auto 30px auto;
            filter: drop-shadow(0 10px 18px rgba(0, 0, 0, 0.35));
        }

        .title {
            font-size: 4.5rem;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: 1px;
            text-shadow: 0 7px 18px rgba(0, 0, 0, 0.45);
        }

        .subtitle {
            margin-top: 18px;
            font-size: 1.35rem;
            font-weight: 400;
            opacity: 0.95;
        }

        .footer-text {
            margin-top: 35px;
            font-size: 0.95rem;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .header {
                padding: 20px;
            }

            .nav-link {
                font-size: 14px;
                padding: 9px 15px;
            }

            .hero-card {
                padding: 35px 20px;
            }

            .logo-ctp {
                width: 190px;
                margin-bottom: 25px;
            }

            .title {
                font-size: 2.7rem;
            }

            .subtitle {
                font-size: 1.05rem;
            }
        }

        @media (max-width: 480px) {
            .header {
                justify-content: center;
            }

            .title {
                font-size: 2.2rem;
            }

            .subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="page-container">

        {{-- Encabezado con login sin dañar la autenticación --}}
        <header class="header">
            @if (Route::has('login'))
                <nav class="nav">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="nav-link">
                            Panel principal
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">
                            Log in Prematrícula
                        </a>
                    @endauth
                </nav>
            @endif
        </header>
        

        {{-- Contenido principal --}}
        <main class="main-content">
            <section class="hero-card">

                {{-- Imagen del CTP --}}
                <img 
                    src="{{ asset('images/logo-ctp.jpg') }}" 
                    alt="Logo CTP Los Chiles" 
                    class="logo-ctp"
                >

                <h1 class="title">
                    Portal Académico<br>Los Chiles
                </h1>

                <p class="subtitle">
                    Sistema institucional para la gestión académica en Prematricula y Matricula Estudiantil.
                </p>

                <p class="footer-text">
                    Colegio Técnico Profesional Los Chiles
                </p>

            </section>
        </main>

    </div>
</body>
</html>