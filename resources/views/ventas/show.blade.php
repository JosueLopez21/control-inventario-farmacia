@extends('layouts.app')

@section('titulo', 'Venta #' . $venta->id)
@section('subtitulo', 'Detalle de la venta')

@section('contenido')
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    <a href="{{ route('ventas.print', $venta) }}" target="_blank" class="btn btn-primary"><i class="bi bi-printer me-1"></i>Imprimir</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Número de venta</div>
            <div class="h4 fw-bold mb-0">#{{ $venta->id }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Fecha y hora</div>
            <div class="fw-semibold">{{ formato_fecha_hora($venta->fecha_venta) }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Total</div>
            <div class="h4 fw-bold text-farma mb-0">{{ formato_bs($venta->monto_total) }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body" data-venta-pago>
            <div class="text-muted small">Método de pago</div>
            <div class="fw-semibold"><x-metodo-pago :metodo="$venta->metodo_pago" /></div>
            @if ($venta->metodo_pago === 'efectivo')
                <div class="small text-muted mt-1">
                    Recibido: <strong>{{ formato_bs($venta->monto_recibido ?? $venta->monto_total) }}</strong>
                    · Cambio: <strong class="text-success">{{ formato_bs($venta->cambio ?? 0) }}</strong>
                </div>
            @endif
        </div></div>
    </div>
</div>

@if (session('resumen_stock'))
    <div class="card mb-3 border-success" data-resumen-stock>
        <div class="card-header bg-success-subtle"><i class="bi bi-boxes me-2"></i>Así quedó el stock tras esta venta</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead><tr><th>Medicamento</th><th class="text-center">Vendidas</th><th class="text-center">Stock antes</th><th class="text-center">Stock después</th></tr></thead>
                    <tbody>
                        @foreach (session('resumen_stock') as $fila)
                            <tr>
                                <td class="fw-semibold">{{ $fila['medicamento'] }}</td>
                                <td class="text-center">−{{ $fila['cantidad'] }}</td>
                                <td class="text-center">{{ $fila['antes'] }}</td>
                                <td class="text-center fw-bold text-success">{{ $fila['despues'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header"><i class="bi bi-list-check me-2"></i>Medicamentos vendidos ({{ $venta->detalles->count() }})</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Lote utilizado</th>
                        <th>Vencimiento</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio unit.</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-center">Stock actual</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venta->detalles as $detalle)
                        <tr>
                            <td class="fw-semibold">{{ $detalle->lote?->medicamento?->nombre_comercial ?? '—' }}</td>
                            <td>{{ $detalle->lote?->numero_lote ?? '—' }}</td>
                            <td>{{ $detalle->lote ? formato_fecha($detalle->lote->fecha_vencimiento) : '—' }}</td>
                            <td class="text-center">{{ $detalle->cantidad }}</td>
                            <td class="text-end">{{ formato_bs($detalle->precio_unitario) }}</td>
                            <td class="text-end fw-semibold">{{ formato_bs($detalle->subtotal) }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $stockActual[$detalle->lote?->medicamento_id] ?? 0 }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="5" class="text-end">Total</th>
                        <th class="text-end">{{ formato_bs($venta->monto_total) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
