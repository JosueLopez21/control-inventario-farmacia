@php($user = auth()->user())
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Acceso') · FarmaSalud</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        body {
            min-height: 100vh; margin: 0;
            background: radial-gradient(circle at 20% 20%, #14b8a6 0%, transparent 45%),
                        radial-gradient(circle at 85% 80%, #1d4ed8 0%, transparent 45%),
                        linear-gradient(135deg, #0f766e 0%, #115e59 50%, #0c4a6e 100%);
            display: grid; place-items: center; font-family: "Segoe UI", system-ui, sans-serif;
        }
        .login-card { width: 100%; max-width: 430px; border: 0; border-radius: 20px; box-shadow: 0 24px 60px rgba(0,0,0,.28); }
        .login-logo { width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, #0f766e, #14b8a6); display: grid; place-items: center; color: #fff; font-size: 1.8rem; margin: 0 auto 1rem; }
        .form-control:focus { border-color: #14b8a6; box-shadow: 0 0 0 .2rem rgba(20,184,166,.15); }
        .btn-farma { background: #0f766e; border-color: #0f766e; color: #fff; }
        .btn-farma:hover { background: #0b5d57; border-color: #0b5d57; color: #fff; }
    </style>
</head>
<body>
    <div class="w-100" style="max-width: 430px; padding: 1rem;">
        @if (session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
        @yield('contenido')
    </div>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
