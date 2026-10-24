@extends('layouts.app')

@section('titulo', 'Movimientos de inventario')
@section('subtitulo', 'Qué lote y qué medicamento subió o bajó de stock, y por qué')

@section('contenido')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('movimientos.index') }}" class="row g-2 align-items-end" data-filtros-movimientos>
            <div class="col-md-4">
                <label class="form-label" for="q">Medicamento o número de lote</label>
                <input type="search" name="q" id="q" value="{{ $q }}" class="form-control" placeholder="Ej. paracetamol, omega, LOT-000123">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Models\MovimientoInventario::TIPOS as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($tipo === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="desde">Desde</label>
                <input type="date" name="desde" id="desde" value="{{ $desde }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="hasta">Hasta</label>
                <input type="date" name="hasta" id="hasta" value="{{ $hasta }}" class="form-control">
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Buscar</button>
                <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-eraser"></i></a>
            </div>
            @if ($ventaId)
                <input type="hidden" name="venta" value="{{ $ventaId }}">
            @endif
        </form>
        @if ($ventaId)
            <div class="mt-3 small">
                <span class="badge bg-primary-subtle text-primary-emphasis border">Solo la venta #{{ $ventaId }}</span>
                <a href="{{ route('movimientos.index', array_filter(['q' => $q, 'tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta])) }}" class="ms-2">Quitar este filtro</a>
            </div>
        @endif
    </div>
</div>

<div class="row g-3 mb-4" data-totales-movimientos>
    <div class="col-12 col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Movimientos encontrados</div>
            <div class="h4 fw-bold mb-0">{{ number_format($totales->movimientos, 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Unidades que salieron</div>
            <div class="h4 fw-bold text-danger mb-0">−{{ number_format($totales->salidas, 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Unidades que entraron</div>
            <div class="h4 fw-bold text-success mb-0">+{{ number_format($totales->entradas, 0, ',', '.') }}</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-arrow-left-right me-2 text-farma"></i>Historial ({{ $movimientos->total() }})</div>
    <div class="card-body p-0">
        @if ($movimientos->isEmpty())
            <x-empty-state icono="bi-arrow-left-right" titulo="No hay movimientos" mensaje="Cuando se venda o se registre un lote, aparecerá aquí con el lote exacto." />
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" data-tabla-movimientos>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Medicamento</th>
                            <th>Lote</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Stock del lote</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movimientos as $mov)
                            <tr>
                                <td class="text-nowrap">{{ formato_fecha_hora($mov->created_at) }}</td>
                                <td>
                                    @if ($mov->tipo === 'venta')
                                        <span class="badge bg-danger-subtle text-danger-emphasis border"><i class="bi bi-cart3 me-1"></i>Venta</span>
                                    @elseif ($mov->tipo === 'ingreso')
                                        <span class="badge bg-success-subtle text-success-emphasis border"><i class="bi bi-box-arrow-in-down me-1"></i>Ingreso</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border"><i class="bi bi-sliders me-1"></i>Ajuste</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $mov->medicamento_nombre }}</td>
                                <td class="text-nowrap">{{ $mov->numero_lote }}</td>
                                <td class="text-center fw-bold {{ $mov->cantidad < 0 ? 'text-danger' : 'text-success' }}">{{ $mov->cantidad > 0 ? '+' : '−' }}{{ abs($mov->cantidad) }}</td>
                                <td class="text-center text-nowrap">{{ $mov->stock_antes }} → <strong>{{ $mov->stock_despues }}</strong></td>
                                <td class="small">
                                    @if ($mov->venta_id)
                                        <a href="{{ route('ventas.show', $mov->venta_id) }}">Venta #{{ $mov->venta_id }}</a>
                                    @endif
                                    @if ($mov->motivo)
                                        <span class="d-block text-muted">{{ $mov->motivo }}</span>
                                    @endif
                                    @if ($mov->usuario)
                                        <span class="d-block text-muted">Por {{ $mov->usuario->name }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $movimientos->links() }}</div>
        @endif
    </div>
</div>
@endsection
