@php($medicamento = $medicamento ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre_comercial" class="form-label">Nombre comercial *</label>
        <input type="text" name="nombre_comercial" id="nombre_comercial" class="form-control @error('nombre_comercial') is-invalid @enderror"
               value="{{ old('nombre_comercial', $medicamento?->nombre_comercial) }}" required>
        @error('nombre_comercial')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="principio_activo" class="form-label">Principio activo *</label>
        <input type="text" name="principio_activo" id="principio_activo" class="form-control @error('principio_activo') is-invalid @enderror"
               value="{{ old('principio_activo', $medicamento?->principio_activo) }}" required>
        @error('principio_activo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="presentacion" class="form-label">Presentación *</label>
        <input type="text" name="presentacion" id="presentacion" class="form-control @error('presentacion') is-invalid @enderror"
               value="{{ old('presentacion', $medicamento?->presentacion) }}" placeholder="Ej. Tabletas 500mg x 20" required>
        @error('presentacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="precio_venta" class="form-label">Precio de venta (Bs) *</label>
        <input type="number" step="0.01" min="0.01" name="precio_venta" id="precio_venta" class="form-control @error('precio_venta') is-invalid @enderror"
               value="{{ old('precio_venta', $medicamento?->precio_venta) }}" required>
        @error('precio_venta')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="stock_minimo" class="form-label">Stock mínimo *</label>
        <input type="number" min="0" name="stock_minimo" id="stock_minimo" class="form-control @error('stock_minimo') is-invalid @enderror"
               value="{{ old('stock_minimo', $medicamento?->stock_minimo ?? 10) }}" required>
        @error('stock_minimo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="categoria_id" class="form-label">Categoría *</label>
        <select name="categoria_id" id="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
            <option value="">Selecciona una categoría</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected((string) old('categoria_id', $medicamento?->categoria_id) === (string) $categoria->id)>{{ $categoria->nombre }}</option>
            @endforeach
        </select>
        @error('categoria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="laboratorio_id" class="form-label">Laboratorio *</label>
        <select name="laboratorio_id" id="laboratorio_id" class="form-select @error('laboratorio_id') is-invalid @enderror" required>
            <option value="">Selecciona un laboratorio</option>
            @foreach ($laboratorios as $laboratorio)
                <option value="{{ $laboratorio->id }}" @selected((string) old('laboratorio_id', $medicamento?->laboratorio_id) === (string) $laboratorio->id)>{{ $laboratorio->nombre }}</option>
            @endforeach
        </select>
        @error('laboratorio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
