@extends('layouts.app')

@section('titulo', 'Alertas')
@section('subtitulo', 'Semáforo de vencimientos y stock')

@section('contenido')
<div class="alertas-hero mb-4">
    <div class="row align-items-center g-3 position-relative">
        <div class="col-lg-8">
            <span class="hero-pill mb-3"><i class="bi bi-stoplights"></i>Semáforo de inventario</span>
            <h2 class="mb-2">Lo urgente, primero</h2>
            <p class="mb-0">Rojo: no vender. Ámbar: revisar y priorizar salida. Gris: reponer stock. Toca una tarjeta para ir directo a la lista.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            @if (auth()->user()->puedeGestionarInventario())
                <a href="{{ route('lotes.create') }}" class="btn btn-light btn-lg fw-semibold">
                    <i class="bi bi-plus-lg me-1"></i>Registrar lote
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <button type="button" class="alerta-resumen alerta-resumen--rojo" data-alerta-resumen data-bs-toggle="tab" data-bs-target="#panel-vencidos" aria-controls="panel-vencidos">
            <span class="alerta-semaforo" aria-hidden="true"></span>
            <span class="alerta-resumen__cuerpo">
                <span class="alerta-resumen__label">Lotes vencidos</span>
                <strong class="alerta-resumen__valor" data-contador="totalVencidos" data-valor="{{ $totalVencidos }}" data-formato="numero">{{ $totalVencidos }}</strong>
                <span class="alerta-resumen__nota">Bloqueados para la venta</span>
            </span>
            <i class="bi bi-arrow-right-circle alerta-resumen__flecha"></i>
        </button>
    </div>
    <div class="col-md-4">
        <button type="button" class="alerta-resumen alerta-resumen--ambar activo" data-alerta-resumen data-bs-toggle="tab" data-bs-target="#panel-porvencer" aria-controls="panel-porvencer">
            <span class="alerta-semaforo" aria-hidden="true"></span>
            <span class="alerta-resumen__cuerpo">
                <span class="alerta-resumen__label">Por vencer (30 días)</span>
                <strong class="alerta-resumen__valor" data-contador="totalPorVencer" data-valor="{{ $totalPorVencer }}" data-formato="numero">{{ $totalPorVencer }}</strong>
                <span class="alerta-resumen__nota">Prioriza su salida: vence primero</span>
            </span>
            <i class="bi bi-arrow-right-circle alerta-resumen__flecha"></i>
        </button>
    </div>
    <div class="col-md-4">
        <button type="button" class="alerta-resumen alerta-resumen--gris" data-alerta-resumen data-bs-toggle="tab" data-bs-target="#panel-stock" aria-controls="panel-stock">
            <span class="alerta-semaforo" aria-hidden="true"></span>
            <span class="alerta-resumen__cuerpo">
                <span class="alerta-resumen__label">Stock bajo mínimo</span>
                <strong class="alerta-resumen__valor" data-contador="totalStockBajo" data-valor="{{ $totalStockBajo }}" data-formato="numero">{{ $totalStockBajo }}</strong>
                <span class="alerta-resumen__nota">Medicamentos que necesitan reposición</span>
            </span>
            <i class="bi bi-arrow-right-circle alerta-resumen__flecha"></i>
        </button>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h2 class="h6 fw-bold mb-0">Panel de alertas</h2>
    <span class="leyenda-semaforo">
        <span class="punto punto--rojo"></span>No vender
        <span class="punto punto--ambar"></span>Priorizar
        <span class="punto punto--gris"></span>Reponer
    </span>
</div>

<ul class="nav nav-tabs alertas-tabs" id="tabsAlertas" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-porvencer" data-bs-toggle="tab" data-bs-target="#panel-porvencer" type="button" role="tab" aria-selected="true">
            <i class="bi bi-hourglass-split me-1"></i>Por vencer <span class="badge bg-warning text-dark">{{ $totalPorVencer }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-vencidos" data-bs-toggle="tab" data-bs-target="#panel-vencidos" type="button" role="tab" aria-selected="false">
            <i class="bi bi-x-octagon me-1"></i>Vencidos <span class="badge bg-danger">{{ $totalVencidos }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-stock" data-bs-toggle="tab" data-bs-target="#panel-stock" type="button" role="tab" aria-selected="false">
            <i class="bi bi-arrow-down-circle me-1"></i>Stock bajo <span class="badge bg-secondary">{{ $totalStockBajo }}</span>
        </button>
    </li>
</ul>

<div class="tab-content bg-white border border-top-0 rounded-bottom p-3 alertas-panel">
    <div class="tab-pane fade show active" id="panel-porvencer" role="tabpanel" aria-labelledby="tab-porvencer">
        <div class="alert alert-warning border-0 py-2 small"><i class="bi bi-lightbulb me-1"></i>Estos lotes todavía se pueden vender: el sistema elegirá primero el de vencimiento más cercano.</div>
        @if ($porVencer->isEmpty())
            <x-empty-state icono="bi-check2-circle" titulo="Sin lotes por vencer" mensaje="Ningún lote vence en los próximos 30 días." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Lote</th><th>Medicamento</th><th>Vencimiento</th><th class="text-center">Días restantes</th><th class="text-center">Stock</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @foreach ($porVencer as $lote)
                            @php($dias = dias_restantes($lote->fecha_vencimiento))
                            <tr>
                                <td class="fw-semibold"><span class="punto punto--ambar me-1"></span>{{ $lote->numero_lote }}</td>
                                <td>{{ $lote->medicamento?->nombre_comercial ?? '—' }}</td>
                                <td>{{ formato_fecha($lote->fecha_vencimiento) }}</td>
                                <td class="text-center"><span class="badge {{ $dias <= 7 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $dias }} día(s)</span></td>
                                <td class="text-center">{{ $lote->cantidad_stock }}</td>
                                <td><x-lote-badge :lote="$lote" /></td>
                                <td class="text-end text-nowrap">
                                    @if ($lote->medicamento)
                                        <a href="{{ route('medicamentos.show', $lote->medicamento) }}" class="btn btn-sm btn-outline-secondary" title="Ver medicamento" aria-label="Ver medicamento"><i class="bi bi-eye"></i></a>
                                    @endif
                                    @if (auth()->user()->puedeGestionarInventario())
                                        <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-sm btn-outline-primary" title="Editar lote" aria-label="Editar lote"><i class="bi bi-pencil"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $porVencer->links() }}</div>
        @endif
    </div>

    <div class="tab-pane fade" id="panel-vencidos" role="tabpanel" aria-labelledby="tab-vencidos">
        <div class="alert alert-danger border-0 py-2 small"><i class="bi bi-exclamation-octagon me-1"></i>Estos lotes están vencidos y <strong>no deben venderse</strong>. El sistema bloquea su dispensación automáticamente.</div>
        @if ($vencidos->isEmpty())
            <x-empty-state icono="bi-check2-circle" titulo="Sin lotes vencidos" mensaje="No hay medicamentos caducados en el inventario." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Lote</th><th>Medicamento</th><th>Vencimiento</th><th class="text-center">Días vencido</th><th class="text-center">Stock</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @foreach ($vencidos as $lote)
                            <tr>
                                <td class="fw-semibold"><span class="punto punto--rojo me-1"></span>{{ $lote->numero_lote }}</td>
                                <td>{{ $lote->medicamento?->nombre_comercial ?? '—' }}</td>
                                <td>{{ formato_fecha($lote->fecha_vencimiento) }}</td>
                                <td class="text-center"><span class="badge bg-danger">{{ abs(dias_restantes($lote->fecha_vencimiento)) }} día(s)</span></td>
                                <td class="text-center">{{ $lote->cantidad_stock }}</td>
                                <td><x-lote-badge :lote="$lote" /></td>
                                <td class="text-end text-nowrap">
                                    @if ($lote->medicamento)
                                        <a href="{{ route('medicamentos.show', $lote->medicamento) }}" class="btn btn-sm btn-outline-secondary" title="Ver medicamento" aria-label="Ver medicamento"><i class="bi bi-eye"></i></a>
                                    @endif
                                    @if (auth()->user()->puedeGestionarInventario())
                                        <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-sm btn-outline-primary" title="Editar lote" aria-label="Editar lote"><i class="bi bi-pencil"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $vencidos->links() }}</div>
        @endif
    </div>

    <div class="tab-pane fade" id="panel-stock" role="tabpanel" aria-labelledby="tab-stock">
        @if ($stockBajo->isEmpty())
            <x-empty-state icono="bi-check2-circle" titulo="Stock saludable" mensaje="Ningún medicamento está por debajo de su stock mínimo." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Medicamento</th><th>Categoría</th><th>Laboratorio</th><th class="text-center">Stock vigente</th><th class="text-center">Mínimo</th><th class="text-center">Déficit</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @foreach ($stockBajo as $medicamento)
                            @php($stock = (int) ($medicamento->stock_vigente ?? 0))
                            <tr>
                                <td class="fw-semibold"><span class="punto punto--gris me-1"></span>{{ $medicamento->nombre_comercial }}</td>
                                <td>{{ $medicamento->categoria?->nombre ?? '—' }}</td>
                                <td>{{ $medicamento->laboratorio?->nombre ?? '—' }}</td>
                                <td class="text-center"><span class="badge {{ $stock <= 0 ? 'bg-secondary' : 'bg-danger' }}">{{ $stock }}</span></td>
                                <td class="text-center">{{ $medicamento->stock_minimo }}</td>
                                <td class="text-center text-danger fw-semibold">{{ $medicamento->stock_minimo - $stock }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('medicamentos.show', $medicamento) }}" class="btn btn-sm btn-outline-secondary" title="Ver medicamento" aria-label="Ver medicamento"><i class="bi bi-eye"></i></a>
                                    @if (auth()->user()->puedeGestionarInventario())
                                        <a href="{{ route('lotes.create', ['medicamento_id' => $medicamento->id]) }}" class="btn btn-sm btn-outline-primary" title="Registrar lote" aria-label="Registrar lote"><i class="bi bi-box-arrow-in-down"></i></a>
                                    @endif
                                    @if (auth()->user()->puedeOperarVentas() && $stock > 0)
                                        <a href="{{ route('ventas.create', ['medicamento_id' => $medicamento->id]) }}" class="btn btn-sm btn-primary" title="Vender" aria-label="Vender"><i class="bi bi-cart-plus"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $stockBajo->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/alertas.js') }}?v={{ filemtime(public_path('js/alertas.js')) }}"></script>
@endpush
