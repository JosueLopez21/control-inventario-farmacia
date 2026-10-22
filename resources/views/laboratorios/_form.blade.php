@php($laboratorio = $laboratorio ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre *</label>
        <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $laboratorio?->nombre) }}" required>
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="pais" class="form-label">País *</label>
        <input type="text" name="pais" id="pais" class="form-control @error('pais') is-invalid @enderror"
               value="{{ old('pais', $laboratorio?->pais) }}" required>
        @error('pais')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="direccion" class="form-label">Dirección</label>
        <input type="text" name="direccion" id="direccion" class="form-control @error('direccion') is-invalid @enderror"
               value="{{ old('direccion', $laboratorio?->direccion) }}">
        @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" name="telefono" id="telefono" class="form-control @error('telefono') is-invalid @enderror"
               value="{{ old('telefono', $laboratorio?->telefono) }}">
        @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="email" class="form-label">Correo</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $laboratorio?->email) }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
