@extends('layouts.app')

@section('titulo', 'Cambiar contraseña')
@section('subtitulo', 'Seguridad de tu cuenta')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-key me-2"></i>Cambiar mi contraseña</div>
            <div class="card-body">
                <form method="POST" action="{{ route('perfil.password.update') }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña actual</label>
                        <input type="password" name="password_actual" id="password_actual"
                               class="form-control @error('password_actual') is-invalid @enderror" required>
                        @error('password_actual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        <div class="form-text">Mínimo 8 caracteres.</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Actualizar contraseña</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
