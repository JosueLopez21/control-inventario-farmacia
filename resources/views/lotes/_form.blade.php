@php
    $lote = $lote ?? null;
    $medicamentoSeleccionado = $medicamentoSeleccionado ?? null;
    $medicamentoInicial = $medicamentoInicial ?? null;
    $medicamentoActual = old('medicamento_id', $lote?->medicamento_id ?? $medicamentoSeleccionado);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="medicamento_id" id="etiquetaMedicamento" class="form-label">Medicamento *</label>

        @if ($lote)
            <select name="medicamento_id" id="medicamento_id" class="form-select @error('medicamento_id') is-invalid @enderror" required>
                <option value="">Selecciona un medicamento</option>
                @foreach ($medicamentos as $medicamento)
                    <option value="{{ $medicamento->id }}" @selected((string) $medicamentoActual === (string) $medicamento->id)>{{ $medicamento->nombre_comercial }}</option>
                @endforeach
            </select>
        @else
            <div class="selector-lote"
                 data-selector-lote
                 data-selector-url="{{ route('medicamentos.buscar') }}">
                <div class="selector-lote__nativo-wrap">
                    <select name="medicamento_id" id="medicamento_id" class="form-select selector-lote__nativo @error('medicamento_id') is-invalid @enderror"
                            aria-labelledby="etiquetaMedicamento" data-selector-nativo required>
                        <option value="">Selecciona un medicamento</option>
                        @foreach ($medicamentos as $medicamento)
                            <option value="{{ $medicamento->id }}" @selected((string) $medicamentoActual === (string) $medicamento->id)>{{ $medicamento->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="selector-lote__buscador">
                    <div class="input-group selector-lote__campo">
                        <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                        <input type="search"
                               id="buscadorMedicamento"
                               class="form-control"
                               placeholder="Buscar por nombre, principio activo o presentación..."
                               autocomplete="off"
                               role="combobox"
                               aria-autocomplete="list"
                               aria-haspopup="listbox"
                               aria-expanded="false"
                               aria-controls="resultadosMedicamentos"
                               aria-labelledby="etiquetaMedicamento"
                               data-selector-input>
                        <button type="button" class="btn btn-outline-secondary" data-selector-limpiar aria-label="Limpiar búsqueda" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="selector-lote__resultados"
                         id="resultadosMedicamentos"
                         role="listbox"
                         aria-label="Medicamentos disponibles"
                         data-selector-resultados
                         hidden></div>
                    <p class="selector-lote__ayuda">Escribe al menos 2 letras. Usa ↑ ↓ para moverte y Enter para elegir.</p>
                </div>

                <div class="selector-lote__elegido" data-selector-elegido @if (! $medicamentoInicial) hidden @endif>
                    <span class="selector-lote__elegido-icono" aria-hidden="true"><i class="bi bi-capsule"></i></span>
                    <div class="selector-lote__elegido-texto">
                        <span class="selector-lote__elegido-etiqueta">Medicamento seleccionado</span>
                        <strong data-selector-elegido-nombre>{{ $medicamentoInicial?->nombre_comercial }}</strong>
                        <span class="selector-lote__elegido-detalle" data-selector-elegido-detalle>
                            @if ($medicamentoInicial)
                                {{ collect([$medicamentoInicial->principio_activo, $medicamentoInicial->presentacion, $medicamentoInicial->laboratorio?->nombre])->filter()->join(' · ') }}
                            @endif
                        </span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary selector-lote__cambiar" data-selector-cambiar>
                        Cambiar
                    </button>
                </div>

                <p class="visually-hidden" aria-live="polite" data-selector-estado></p>
            </div>
        @endif

        @error('medicamento_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
