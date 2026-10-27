@extends('layouts.guest')

@section('titulo', 'Iniciar sesión')

@section('contenido')
<div class="card login-card">
    <div class="card-body p-4 p-sm-5">
        <div class="login-logo"><i class="bi bi-heart-pulse-fill"></i></div>
        <h1 class="h4 text-center fw-bold mb-1">FarmaSalud</h1>
        <p class="text-center text-muted small mb-4">Sistema de control de inventario de farmacia</p>

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="usuario@farmacia.test" autocomplete="username" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••" autocomplete="current-password" required>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small" for="remember">Recordarme</label>
            </div>

            <button type="submit" class="btn btn-farma w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
            </button>
        </form>

        @if (config('feria.mostrar_usuarios'))
            <div class="usuarios-autorizados mt-4" data-usuarios-autorizados>
                <h2 class="usuarios-autorizados__titulo"><i class="bi bi-people me-1"></i>Usuarios autorizados</h2>
                <p class="usuarios-autorizados__ayuda">Toca un usuario para llenar el formulario.</p>
                @foreach (config('feria.usuarios') as $usuario)
                    <button type="button" class="usuario-demo"
                            data-usuario-demo
                            data-email="{{ $usuario['email'] }}"
                            data-password="{{ $usuario['password'] }}">
                        <span class="usuario-demo__icono"><i class="bi {{ $usuario['icono'] }}"></i></span>
                        <span class="usuario-demo__datos">
                            <strong>{{ $usuario['rol_etiqueta'] }}</strong>
                            <small>{{ $usuario['descripcion'] }}</small>
                            <span class="usuario-demo__credencial"><i class="bi bi-envelope"></i> {{ $usuario['email'] }}</span>
                            <span class="usuario-demo__credencial"><i class="bi bi-key"></i> <code>{{ $usuario['password'] }}</code></span>
                        </span>
                        <i class="bi bi-box-arrow-in-right usuario-demo__usar" aria-hidden="true"></i>
                    </button>
                @endforeach
            </div>
        @endif

        <p class="text-center text-muted mt-4 mb-0" style="font-size: .74rem;">
            Acceso restringido · Solo personal autorizado
        </p>
        <p class="text-center mt-2 mb-0">
            <a href="{{ route('bienvenida') }}" class="small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Volver a la portada
            </a>
        </p>
    </div>
</div>
@endsection

@if (config('feria.mostrar_usuarios'))
@push('scripts')
<script>
    document.querySelectorAll('[data-usuario-demo]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            document.getElementById('email').value = boton.dataset.email;
            document.getElementById('password').value = boton.dataset.password;
            document.querySelectorAll('[data-usuario-demo]').forEach(function (otro) {
                otro.classList.toggle('is-activo', otro === boton);
            });
            document.querySelector('form button[type="submit"]').focus();
        });
    });
</script>
@endpush
@endif
