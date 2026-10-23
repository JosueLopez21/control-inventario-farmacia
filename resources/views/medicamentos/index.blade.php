@extends('layouts.app')

@section('titulo', 'Medicamentos')
@section('subtitulo', 'Catálogo vivo de la farmacia')

@section('contenido')
<div class="catalogo-hero mb-4">
    <div class="row align-items-center g-3 position-relative">
        <div class="col-lg-8">
            <span class="hero-pill mb-3"><i class="bi bi-stars"></i>Catálogo de feria</span>
            <h2 class="mb-2">Encuentra el medicamento correcto en segundos</h2>
            <p class="mb-0">Explora por categoría, revisa el stock vigente y conoce qué lote vence primero. Todo el catálogo disponible para los tres roles.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="d-inline-flex align-items-center gap-3 rounded-4 px-3 py-2" style="background: rgba(255,255,255,.12);">
                <div>
                    <div class="fs-4 fw-bold lh-1">{{ $medicamentos->total() }}</div>
                    <div class="small text-white-50">medicamentos</div>
                </div>
                @if (auth()->user()->puedeGestionarInventario())
                    <a href="{{ route('medicamentos.create') }}" class="btn btn-light fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="catalogo-filtros p-3 p-md-4 mb-4">
    <form method="GET" action="{{ route('medicamentos.index') }}" id="formCatalogo" class="row g-3 align-items-end">
        <div class="col-lg-5">
            <label for="q" class="form-label">Buscar medicamento</label>
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white"><i class="bi bi-search text-farma"></i></span>
                <input type="search" name="q" id="q" value="{{ $q }}" class="form-control" placeholder="Nombre comercial o principio activo" autocomplete="off">
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <label for="laboratorio_id" class="form-label">Laboratorio</label>
            <select name="laboratorio_id" id="laboratorio_id" class="form-select form-select-lg">
                <option value="">Todos los laboratorios</option>
                @foreach ($laboratorios as $laboratorio)
                    <option value="{{ $laboratorio->id }}" @selected((string) $laboratorioId === (string) $laboratorio->id)>{{ $laboratorio->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <input type="hidden" name="categoria_id" id="categoria_id" value="{{ $categoriaId }}">
            <button class="btn btn-primary btn-lg w-100" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
        </div>
        <div class="col-lg-2 d-flex justify-content-lg-end align-items-center gap-2">
            <div class="view-switch" role="group" aria-label="Cambiar vista del catálogo">
                <button type="button" data-vista="tarjetas" title="Vista de tarjetas" aria-label="Vista de tarjetas"><i class="bi bi-grid-3x3-gap-fill"></i></button>
                <button type="button" data-vista="tabla" title="Vista de tabla" aria-label="Vista de tabla"><i class="bi bi-table"></i></button>
            </div>
            <a href="{{ route('medicamentos.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros" aria-label="Limpiar filtros">
                <i class="bi bi-eraser"></i>
            </a>
        </div>
    </form>
</div>

<div id="resultadosCatalogo" data-url="{{ route('medicamentos.index') }}" aria-live="polite">
    @include('medicamentos._resultados')
</div>

<div class="modal fade modal-feria" id="modalMedicamento" tabindex="-1" aria-labelledby="modalMedicamentoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header align-items-start gap-3">
                <span class="med-modal-icon" id="modalMedicamentoIcono"><i class="bi bi-capsule"></i></span>
                <div class="flex-grow-1">
                    <span class="small text-white-50" id="modalMedicamentoCategoria">Categoría</span>
                    <h2 class="h5 mb-1" id="modalMedicamentoTitulo">Medicamento</h2>
                    <p class="mb-0 small text-white-50" id="modalMedicamentoPrincipio">Principio activo</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="dato-grid mb-3">
                    <div class="dato"><span>Presentación</span><strong id="modalMedicamentoPresentacion">—</strong></div>
                    <div class="dato"><span>Laboratorio</span><strong id="modalMedicamentoLaboratorio">—</strong></div>
                    <div class="dato"><span>Precio de venta</span><strong class="text-farma" id="modalMedicamentoPrecio">—</strong></div>
                    <div class="dato"><span>Stock vigente / mínimo</span><strong id="modalMedicamentoStock">—</strong></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="h6 fw-bold mb-0"><i class="bi bi-box-seam me-1 text-farma"></i>Lotes FEFO</h3>
                    <span class="small text-muted" id="modalMedicamentoEstado"></span>
                </div>
                <div class="lotes-lista" id="modalMedicamentoLotes"></div>
                <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>El sistema vende primero el lote vigente con vencimiento más cercano y bloquea los lotes vencidos.</p>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <div class="ms-auto d-flex flex-wrap gap-2">
                    @if (auth()->user()->puedeGestionarInventario())
                        <a href="#" class="btn btn-outline-primary" id="modalMedicamentoEditar"><i class="bi bi-pencil me-1"></i>Editar</a>
                    @endif
                    @if (auth()->user()->esAdministrador())
                        <button type="button" class="btn btn-outline-danger" id="modalMedicamentoEliminar" data-eliminar="" data-nombre=""><i class="bi bi-trash3 me-1"></i>Eliminar</button>
                    @endif
                    @if (auth()->user()->puedeOperarVentas())
                        <a href="#" class="btn btn-primary" id="modalMedicamentoVender"><i class="bi bi-cart-plus me-1"></i>Vender</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/catalogo.js') }}?v={{ filemtime(public_path('js/catalogo.js')) }}"></script>
@endpush
