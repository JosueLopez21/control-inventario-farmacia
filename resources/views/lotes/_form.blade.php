@php
    $lote = $lote ?? null;
    $medicamentoSeleccionado = $medicamentoSeleccionado ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="medicamento_id" class="form-label">Medicamento *</label>
        <select name="medicamento_id" id="medicamento_id" class="form-select @error('medicamento_id') is-invalid @enderror" required>
            <option value="">Selecciona un medicamento</option>
            @foreach ($medicamentos as $medicamento)
                <option value="{{ $medicamento->id }}" @selected((string) old('medicamento_id', $lote?->medicamento_id ?? $medicamentoSeleccionado) === (string) $medicamento->id)>{{ $medicamento->nombre_comercial }}</option>
            @endforeach
        </select>
        @error('medicamento_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="numero_lote" class="form-label">Número de lote *</label>
        <input type="text" name="numero_lote" id="numero_lote" class="form-control @error('numero_lote') is-invalid @enderror"
               value="{{ old('numero_lote', $lote?->numero_lote) }}" placeholder="Ej. LOT-000001" required>
        @error('numero_lote')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento *</label>
        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control @error('fecha_vencimiento') is-invalid @enderror"
               value="{{ old('fecha_vencimiento', $lote?->fecha_vencimiento?->format('Y-m-d')) }}" required>
        @error('fecha_vencimiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="cantidad_stock" class="form-label">Cantidad disponible *</label>
        <input type="number" min="0" name="cantidad_stock" id="cantidad_stock" class="form-control @error('cantidad_stock') is-invalid @enderror"
               value="{{ old('cantidad_stock', $lote?->cantidad_stock ?? 0) }}" required>
        @error('cantidad_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
