@extends('layouts.app')

@section('titulo', 'Reportes')
@section('subtitulo', 'Indicadores de ventas e inventario')

@section('contenido')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="fecha_inicio">Desde</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $desde }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="fecha_fin">Hasta</label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $hasta }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i>Aplicar filtro</button>
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-eraser"></i></a>
            </div>
        </form>
        <div class="form-text mt-2">El rango de fechas se aplica a “Ingresos por categoría” y “Top 10 medicamentos”.</div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <x-metric-card titulo="Ticket promedio" :valor="formato_bs($ticketPromedio)" icono="bi-receipt-cutoff" color="verde" />
    </div>
    <div class="col-md-4">
        <x-metric-card titulo="Medicamentos bajo mínimo" :valor="$stockBajo->count()" icono="bi-arrow-down-circle" color="rojo" />
    </div>
    <div class="col-md-4">
        <x-metric-card titulo="Categorías con ventas" :valor="$ingresosPorCategoria->count()" icono="bi-tags" color="azul" />
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-calendar3 me-2 text-farma"></i>Ventas por mes (últimos 12 meses)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Mes</th><th class="text-center">Ventas</th><th class="text-end">Ingresos</th></tr></thead>
                        <tbody>
                            @foreach ($ventasPorMes as $fila)
                                <tr>
                                    <td>{{ ucfirst($fila->etiqueta) }}</td>
                                    <td class="text-center">{{ $fila->total }}</td>
                                    <td class="text-end">{{ formato_bs($fila->monto) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-tags me-2 text-farma"></i>Ingresos por categoría</div>
            <div class="card-body p-0">
                @if ($ingresosPorCategoria->isEmpty())
                    <x-empty-state icono="bi-tags" titulo="Sin datos" mensaje="No hay ventas en el rango seleccionado." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Categoría</th><th class="text-center">Unidades</th><th class="text-end">Ingresos</th></tr></thead>
                            <tbody>
                                @foreach ($ingresosPorCategoria as $fila)
                                    <tr>
                                        <td>{{ $fila->nombre }}</td>
                                        <td class="text-center">{{ (int) $fila->unidades }}</td>
                                        <td class="text-end">{{ formato_bs($fila->ingresos) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100" data-reporte-pagos>
            <div class="card-header"><i class="bi bi-wallet2 me-2 text-farma"></i>Ventas por método de pago</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Método</th><th class="text-center">Ventas</th><th class="text-end">Ingresos</th></tr></thead>
                        <tbody>
                            @foreach ($ventasPorMetodoPago as $fila)
                                <tr>
                                    <td><x-metodo-pago :metodo="$fila->metodo" /></td>
                                    <td class="text-center">{{ $fila->ventas }}</td>
                                    <td class="text-end">{{ formato_bs($fila->ingresos) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy me-2 text-farma"></i>Top 10 medicamentos más vendidos</div>
            <div class="card-body p-0">
                @if ($topMedicamentos->isEmpty())
                    <x-empty-state icono="bi-trophy" titulo="Sin datos" mensaje="No hay ventas en el rango seleccionado." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>#</th><th>Medicamento</th><th class="text-center">Unidades</th><th class="text-end">Ingresos</th></tr></thead>
                            <tbody>
                                @foreach ($topMedicamentos as $i => $fila)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $fila->nombre_comercial }}</td>
                                        <td class="text-center">{{ (int) $fila->unidades }}</td>
                                        <td class="text-end">{{ formato_bs($fila->ingresos) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-arrow-down-circle me-2 text-danger"></i>Medicamentos con stock bajo</div>
            <div class="card-body p-0">
                @if ($stockBajo->isEmpty())
                    <x-empty-state icono="bi-check2-circle" titulo="Todo en orden" mensaje="Ningún medicamento está bajo el mínimo." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Medicamento</th><th>Categoría</th><th class="text-center">Stock</th><th class="text-center">Mínimo</th></tr></thead>
                            <tbody>
                                @foreach ($stockBajo->take(10) as $medicamento)
                                    <tr>
                                        <td class="fw-semibold">{{ $medicamento->nombre_comercial }}</td>
                                        <td>{{ $medicamento->categoria?->nombre ?? '—' }}</td>
                                        <td class="text-center"><span class="badge bg-danger">{{ (int) ($medicamento->stock_vigente ?? 0) }}</span></td>
                                        <td class="text-center">{{ $medicamento->stock_minimo }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($stockBajo->count() > 10)
                        <div class="p-2 text-center small text-muted">Mostrando 10 de {{ $stockBajo->count() }} medicamentos.</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
