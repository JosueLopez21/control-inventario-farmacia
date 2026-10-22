@php($usuario = $usuario ?? null)
@php($esEdicion = $usuario !== null)

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Nombre completo *</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $usuario?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Correo electrónico *</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $usuario?->email) }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="rol" class="form-label">Rol *</label>
        <select name="rol" id="rol" class="form-select @error('rol') is-invalid @enderror" required>
            <option value="administrador" @selected(old('rol', $usuario?->rol) === 'administrador')>Administrador</option>
            <option value="farmaceutico" @selected(old('rol', $usuario?->rol ?? 'farmaceutico') === 'farmaceutico')>Farmacéutico</option>
            <option value="doctor" @selected(old('rol', $usuario?->rol) === 'doctor')>Doctor</option>
        </select>
        @error('rol')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" role="switch" name="activo" id="activo" value="1"
                   @checked(old('activo', $usuario?->activo ?? true))>
            <label class="form-check-label" for="activo">Usuario activo</label>
        </div>
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">{{ $esEdicion ? 'Nueva contraseña (opcional)' : 'Contraseña *' }}</label>
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" @required(! $esEdicion)>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Mínimo 8 caracteres.@if ($esEdicion) Déjala en blanco para conservarla.@endif</div>
    </div>
    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" @required(! $esEdicion)>
    </div>

    @if ($esEdicion && $usuario->id === auth()->id())
        <div class="col-12">
            <div class="alert alert-info py-2 small mb-0"><i class="bi bi-info-circle me-1"></i>Por seguridad, no puedes desactivar tu propia cuenta ni quitarte el rol de administrador.</div>
        </div>
    @endif
</div>
