@extends('layouts.app')

@section('titulo', 'Nueva venta')
@section('subtitulo', 'Punto de venta táctil · descuento automático del lote que vence antes')

@section('contenido')
<div id="posApp"
     data-endpoint="{{ route('medicamentos.buscar') }}"
     data-items="{{ json_encode($itemsIniciales) }}">
    <div class="dashboard-hero mb-4">
        <div class="row align-items-center g-3 position-relative">
            <div class="col-lg-8">
                <span class="hero-pill mb-3"><i class="bi bi-bag-check"></i>Punto de venta</span>
                <h2 class="mb-2">Cobra rápido, vende seguro</h2>
                <p class="mb-0">Busca, toca y suma. El sistema descuenta siempre del lote vigente con vencimiento más cercano y bloquea los vencidos.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('ventas.create', ['modo' => 'clasico']) }}" class="btn btn-outline-light">
                    <i class="bi bi-keyboard me-1"></i>Formulario clásico
                </a>
            </div>
        </div>
    </div>

    <noscript>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            El punto de venta táctil necesita JavaScript.
            <a href="{{ route('ventas.create', ['modo' => 'clasico']) }}" class="alert-link">Usa el formulario clásico</a>.
        </div>
    </noscript>

    <form method="POST" action="{{ route('ventas.store') }}" id="formVenta" novalidate>
        @csrf
        <input type="hidden" name="items" id="items" value="">

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card pos-buscador mb-3">
                    <div class="card-header"><i class="bi bi-search me-2 text-farma"></i>Buscar medicamento</div>
                    <div class="card-body">
                        <label class="form-label" for="buscar">Nombre comercial o principio activo</label>
                        <div class="pos-buscador__campo">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white"><i class="bi bi-upc-scan text-farma"></i></span>
                                <input type="search"
                                       id="buscar"
                                       class="form-control"
                                       placeholder="Escribe 3 letras o elige una categoría..."
                                       autocomplete="off"
                                       role="combobox"
                                       aria-autocomplete="list"
                                       aria-expanded="false"
                                       aria-controls="sugerencias"
                                       aria-haspopup="listbox">
                            </div>
                            <div id="sugerencias" class="pos-sugerencias" role="listbox" aria-label="Sugerencias de medicamentos" hidden></div>
                        </div>
                        <div class="form-text">Se ignoran mayúsculas y tildes. Usa ↑ ↓ para navegar, Enter para agregar y Esc para cerrar.</div>
                    </div>
                </div>

                <div class="card pos-catalogo mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                            <div>
                                <h2 class="h6 fw-bold mb-1"><i class="bi bi-grid-3x3-gap me-1 text-farma"></i>Productos disponibles</h2>
                                <p class="small text-muted mb-0" id="posGridEstado" aria-live="polite">Cargando catálogo...</p>
                            </div>
                            <span class="badge rounded-pill bg-farma-soft text-farma" id="posConteo">0</span>
                        </div>

                        <div class="pos-categorias" id="posCategorias" role="group" aria-label="Filtrar por categoría">
                            <button type="button" class="pos-chip activo" data-categoria="" aria-pressed="true">
                                <i class="bi bi-grid"></i>Todas
                            </button>
                            @foreach ($categorias as $categoria)
                                @php($ui = categoria_ui($categoria->nombre))
                                <button type="button"
                                        class="pos-chip"
                                        data-categoria="{{ $categoria->id }}"
                                        aria-pressed="false"
                                        style="--chip-color: {{ $ui['color'] }}; --chip-suave: {{ $ui['suave'] }};">
                                    <i class="bi {{ $ui['icono'] }}"></i>{{ $categoria->nombre }}
                                </button>
                            @endforeach
                        </div>

                        <div id="posGrid" class="pos-grid mt-3" aria-live="polite" aria-busy="true"></div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary" id="posVerMas" hidden>
                                <i class="bi bi-plus-circle me-1"></i>Ver más productos
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card pos-carrito">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-basket me-2 text-farma"></i>Carrito <span class="badge rounded-pill bg-farma-soft text-farma" id="posContador">0</span></span>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="posVaciar"><i class="bi bi-trash3 me-1"></i>Vaciar</button>
                    </div>
                    <div class="card-body p-0">
                        <div id="carritoLista" class="pos-lista"></div>
                        <div id="carritoVacio" class="empty-state">
                            <i class="bi bi-basket d-block mb-2"></i>
                            <h3 class="h6 fw-semibold text-secondary">El carrito está vacío</h3>
                            <p class="small mb-0">Busca un medicamento y tócalo para agregarlo.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card pos-resumen" style="position: sticky; top: 90px;">
                    <div class="card-header"><i class="bi bi-calculator me-2 text-farma"></i>Resumen de la venta</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ítems</span>
                            <span id="resumenItems" class="fw-semibold">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Unidades</span>
                            <span id="resumenUnidades" class="fw-semibold">0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-semibold">Total a pagar</span>
                            <span id="resumenTotal" class="fs-3 fw-bold text-farma">Bs 0,00</span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold" id="btnConfirmar" disabled>
                            <i class="bi bi-check2-circle me-1"></i>Confirmar venta
                        </button>
                        <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancelar</a>
                        <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>El sistema descuenta automáticamente del lote que vence primero <x-ayuda-fefo sigla /> y bloquea lotes vencidos.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/pos.js') }}?v={{ filemtime(public_path('js/pos.js')) }}"></script>
@endpush
