@php($categoria = $categoria ?? null)

<div class="mb-3">
    <label for="nombre" class="form-label">Nombre *</label>
    <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $categoria?->nombre) }}" required>
    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="descripcion" class="form-label">Descripción</label>
    <textarea name="descripcion" id="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $categoria?->descripcion) }}</textarea>
    @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
