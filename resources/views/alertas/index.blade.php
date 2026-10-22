@extends('layouts.app')

@section('titulo', 'Alertas')
@section('subtitulo', 'Vencimientos y stock bajo mínimo')

@section('contenido')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <x-metric-card titulo="Por vencer (30 días)" :valor="$totalPorVencer" icono="bi-hourglass-split" color="ambar" />
    </div>
    <div class="col-md-4">
        <x-metric-card titulo="Lotes vencidos" :valor="$totalVencidos" icono="bi-x-octagon" color="rojo" />
    </div>
    <div class="col-md-4">
        <x-metric-card titulo="Medicamentos bajo mínimo" :valor="$totalStockBajo" icono="bi-arrow-down-circle" color="gris" />
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 fw-bold mb-0">Panel de alertas</h2>
    @if (auth()->user()->puedeGestionarInventario())
        <a href="{{ route('lotes.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Registrar nuevo lote</a>
    @endif
</div>

<ul class="nav nav-tabs" id="tabsAlertas" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-porvencer" data-bs-toggle="tab" data-bs-target="#panel-porvencer" type="button" role="tab">
            <i class="bi bi-hourglass-split me-1"></i>Por vencer <span class="badge bg-warning text-dark">{{ $totalPorVencer }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-vencidos" data-bs-toggle="tab" data-bs-target="#panel-vencidos" type="button" role="tab">
            <i class="bi bi-x-octagon me-1"></i>Vencidos <span class="badge bg-danger">{{ $totalVencidos }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-stock" data-bs-toggle="tab" data-bs-target="#panel-stock" type="button" role="tab">
            <i class="bi bi-arrow-down-circle me-1"></i>Stock bajo <span class="badge bg-secondary">{{ $totalStockBajo }}</span>
        </button>
    </li>
</ul>

<div class="tab-content bg-white border border-top-0 rounded-bottom p-3">
    <div class="tab-pane fade show active" id="panel-porvencer" role="tabpanel">
        @if ($porVencer->isEmpty())
            <x-empty-state icono="bi-check2-circle" titulo="Sin lotes por vencer" mensaje="Ningún lote vence en los próximos 30 días." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Lote</th><th>Medicamento</th><th>Vencimiento</th><th class="text-center">Días restantes</th><th class="text-center">Stock</th><th>Estado</th></tr></thead>
                    <tbody>
                        @foreach ($porVencer as $lote)
                            @php($dias = dias_restantes($lote->fecha_vencimiento))
                            <tr>
                                <td class="fw-semibold">{{ $lote->numero_lote }}</td>
                                <td>{{ $lote->medicamento?->nombre_comercial ?? '—' }}</td>
                                <td>{{ formato_fecha($lote->fecha_vencimiento) }}</td>
                                <td class="text-center"><span class="badge {{ $dias <= 7 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $dias }} día(s)</span></td>
                                <td class="text-center">{{ $lote->cantidad_stock }}</td>
                                <td><x-lote-badge :lote="$lote" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $porVencer->links() }}</div>
        @endif
    </div>

    <div class="tab-pane fade" id="panel-vencidos" role="tabpanel">
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-octagon me-1"></i>Estos lotes están vencidos y <strong>no deben venderse</strong>. El sistema bloquea su dispensación automáticamente.</div>
        @if ($vencidos->isEmpty())
            <x-empty-state icono="bi-check2-circle" titulo="Sin lotes vencidos" mensaje="No hay medicamentos caducados en el inventario." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Lote</th><th>Medicamento</th><th>Vencimiento</th><th class="text-center">Días vencido</th><th class="text-center">Stock</th><th>Estado</th></tr></thead>
                    <tbody>
                        @foreach ($vencidos as $lote)
                            <tr>
                                <td class="fw-semibold">{{ $lote->numero_lote }}</td>
                                <td>{{ $lote->medicamento?->nombre_comercial ?? '—' }}</td>
                                <td>{{ formato_fecha($lote->fecha_vencimiento) }}</td>
                                <td class="text-center"><span class="badge bg-danger">{{ abs(dias_restantes($lote->fecha_vencimiento)) }} día(s)</span></td>
                                <td class="text-center">{{ $lote->cantidad_stock }}</td>
                                <td><x-lote-badge :lote="$lote" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $vencidos->links() }}</div>
        @endif
    </div>

    <div class="tab-pane fade" id="panel-stock" role="tabpanel">
        @if ($stockBajo->isEmpty())
            <x-empty-state icono="bi-check2-circle" titulo="Stock saludable" mensaje="Ningún medicamento está por debajo de su stock mínimo." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Medicamento</th><th>Categoría</th><th>Laboratorio</th><th class="text-center">Stock vigente</th><th class="text-center">Mínimo</th><th class="text-center">Déficit</th></tr></thead>
                    <tbody>
                        @foreach ($stockBajo as $medicamento)
                            @php($stock = (int) ($medicamento->stock_vigente ?? 0))
                            <tr>
                                <td class="fw-semibold">{{ $medicamento->nombre_comercial }}</td>
                                <td>{{ $medicamento->categoria?->nombre ?? '—' }}</td>
                                <td>{{ $medicamento->laboratorio?->nombre ?? '—' }}</td>
                                <td class="text-center"><span class="badge bg-danger">{{ $stock }}</span></td>
                                <td class="text-center">{{ $medicamento->stock_minimo }}</td>
                                <td class="text-center text-danger fw-semibold">{{ $medicamento->stock_minimo - $stock }}</td>
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
