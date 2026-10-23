<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Bienvenida') · FarmaSalud</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/feria.css') }}?v={{ filemtime(public_path('css/feria.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
</head>
<body class="landing-body">
    <header class="landing-nav">
        <div class="container d-flex align-items-center justify-content-between gap-3">
            <a href="{{ route('bienvenida') }}" class="landing-marca">
                <span class="landing-marca__icono"><i class="bi bi-heart-pulse-fill"></i></span>
                <span>
                    <strong>FarmaSalud</strong>
                    <small>Control de inventario</small>
                </span>
            </a>
            <a href="{{ route('login') }}" class="btn btn-light border fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
            </a>
        </div>
    </header>

    <main>
        @if (session('exito'))
            <div class="container pt-3">
                <div class="alert alert-success border-0 shadow-sm mb-0">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ session('exito') }}
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="container pt-3">
                <div class="alert alert-danger border-0 shadow-sm mb-0">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i>{{ session('error') }}
                </div>
            </div>
        @endif

        @yield('contenido')
    </main>

    <footer class="landing-footer">
        <div class="container d-flex flex-wrap justify-content-between gap-2">
            <span><i class="bi bi-heart-pulse me-1"></i>FarmaSalud · Proyecto académico</span>
            <span>Control de inventario, ventas y alertas con FEFO</span>
        </div>
    </footer>

    <script src="{{ asset('js/feria.js') }}?v={{ filemtime(public_path('js/feria.js')) }}"></script>
    @stack('scripts')
</body>
</html>
