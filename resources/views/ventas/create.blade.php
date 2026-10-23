@extends('layouts.app')

@section('titulo', 'Nueva venta')
@section('subtitulo', 'Punto de venta táctil · descuento FEFO automático')

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
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white"><i class="bi bi-upc-scan text-farma"></i></span>
                            <input type="search" id="buscar" class="form-control" placeholder="Escribe al menos 2 letras..." autocomplete="off">
                        </div>
                        <div id="resultados" class="pos-resultados mt-3" aria-live="polite">
                            <div class="pos-pista">
                                <i class="bi bi-hand-index-thumb"></i>
                                <span>Los medicamentos con stock vigente aparecerán aquí.</span>
                            </div>
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
                        <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>El sistema descuenta automáticamente del lote que vence primero (FEFO) y bloquea lotes vencidos.</p>
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
