@extends('layouts.app')

@section('titulo', 'Dashboard')
@section('subtitulo', 'Resumen general de la farmacia · ' . now()->translatedFormat('l, d \d\e F \d\e Y'))

@section('contenido')
<div class="row g-3 mb-4">
    @if ($puedeVerVentas)
        <div class="col-6 col-xl-2">
            <x-metric-card titulo="Ventas de hoy" :valor="$ventasHoy" icono="bi-cart-check" color="verde" />
        </div>
        <div class="col-6 col-xl-2">
            <x-metric-card titulo="Ingresos de hoy" :valor="formato_bs($ingresosHoy)" icono="bi-cash-coin" color="azul" />
        </div>
    @endif
    <div class="col-6 col-xl-2">
        <x-metric-card titulo="Medicamentos" :valor="$totalMedicamentos" icono="bi-capsule" color="azul" />
    </div>
    <div class="col-6 col-xl-2">
        <x-metric-card titulo="Por vencer (30 días)" :valor="$lotesPorVencer" icono="bi-exclamation-triangle" color="ambar" enlace="{{ route('alertas.index') }}" />
    </div>
    <div class="col-6 col-xl-2">
        <x-metric-card titulo="Lotes vencidos" :valor="$lotesVencidos" icono="bi-x-octagon" color="rojo" enlace="{{ route('alertas.index') }}" />
    </div>
    <div class="col-6 col-xl-2">
        <x-metric-card titulo="Stock bajo mínimo" :valor="$stockBajo->count()" icono="bi-arrow-down-circle" color="gris" enlace="{{ route('alertas.index') }}" />
    </div>
</div>

@if ($puedeVerVentas)
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-graph-up-arrow me-2 text-farma"></i>Ventas de los últimos 7 días</span>
                    <span class="small text-muted">Ticket promedio: {{ formato_bs($ticketPromedio) }}</span>
                </div>
                <div class="card-body">
                    <canvas id="graficoVentas" height="110"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-trophy me-2 text-farma"></i>Top 5 medicamentos más vendidos</div>
                <div class="card-body p-0">
                    @if ($topMedicamentos->isEmpty())
                        <x-empty-state icono="bi-trophy" titulo="Sin ventas registradas" mensaje="Aún no hay medicamentos vendidos." />
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($topMedicamentos as $i => $fila)
                                <li class="list-group-item d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill bg-farma-soft text-farma">{{ $i + 1 }}</span>
                                    <span class="flex-grow-1">{{ $fila->nombre_comercial }}</span>
                                    <span class="badge bg-secondary">{{ $fila->unidades }} u.</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-receipt me-2 text-farma"></i>Últimas 5 ventas</span>
                    <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
                </div>
                <div class="card-body p-0">
                    @if ($ultimasVentas->isEmpty())
                        <x-empty-state icono="bi-receipt" titulo="Aún no hay ventas" mensaje="Registra tu primera venta para verla aquí." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>#</th><th>Fecha</th><th>Ítems</th><th class="text-end">Total</th></tr></thead>
                                <tbody>
                                    @foreach ($ultimasVentas as $venta)
                                        <tr>
                                            <td><a href="{{ route('ventas.show', $venta) }}">#{{ $venta->id }}</a></td>
                                            <td>{{ formato_fecha_hora($venta->fecha_venta) }}</td>
                                            <td>{{ $venta->detalles_count }}</td>
                                            <td class="text-end fw-semibold">{{ formato_bs($venta->monto_total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-arrow-down-circle me-2 text-danger"></i>Stock bajo mínimo</span>
                    <a href="{{ route('alertas.index') }}" class="btn btn-sm btn-outline-primary">Ver alertas</a>
                </div>
                <div class="card-body p-0">
                    @if ($stockBajo->isEmpty())
                        <x-empty-state icono="bi-check2-circle" titulo="Todo en orden" mensaje="No hay medicamentos bajo el stock mínimo." />
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($stockBajo->take(5) as $medicamento)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-truncate me-2">{{ $medicamento->nombre_comercial }}</span>
                                    <span class="small text-nowrap">
                                        <span class="text-danger fw-semibold">{{ (int) ($medicamento->stock_vigente ?? 0) }}</span>
                                        / {{ $medicamento->stock_minimo }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-info-circle text-farma fs-3"></i>
                <div>
                    <h2 class="h6 fw-bold mb-1">Vista de consulta médica</h2>
                    <p class="text-muted mb-3">Como doctor puedes consultar el catálogo de medicamentos con stock disponible y las alertas del inventario. Las ventas y reportes están reservados al personal de farmacia.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('medicamentos.index') }}" class="btn btn-primary"><i class="bi bi-capsule me-1"></i>Ver catálogo</a>
                        <a href="{{ route('alertas.index') }}" class="btn btn-outline-primary"><i class="bi bi-exclamation-triangle me-1"></i>Ver alertas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
@if ($puedeVerVentas)
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
    const ctx = document.getElementById('graficoVentas');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($grafico['etiquetas']),
            datasets: [
                {
                    label: 'Ventas',
                    data: @json($grafico['totales']),
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, .12)',
                    fill: true,
                    tension: .35,
                    borderWidth: 2,
                    pointBackgroundColor: '#0f766e'
                },
                {
                    label: 'Ingresos (Bs)',
                    data: @json($grafico['montos']),
                    borderColor: '#1d4ed8',
                    backgroundColor: 'rgba(29, 78, 216, .08)',
                    fill: false,
                    tension: .35,
                    borderWidth: 2,
                    pointBackgroundColor: '#1d4ed8',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
            }
        }
    });
</script>
@endif
@endpush
