@extends('layouts.app')

@section('titulo', 'Venta #' . $venta->id)
@section('subtitulo', 'Detalle de la venta')

@section('contenido')
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    <a href="{{ route('ventas.print', $venta) }}" target="_blank" class="btn btn-primary"><i class="bi bi-printer me-1"></i>Imprimir</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Número de venta</div>
            <div class="h4 fw-bold mb-0">#{{ $venta->id }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Fecha y hora</div>
            <div class="fw-semibold">{{ formato_fecha_hora($venta->fecha_venta) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Total</div>
            <div class="h4 fw-bold text-farma mb-0">{{ formato_bs($venta->monto_total) }}</div>
        </div></div>
    </div>
</div>

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
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="5" class="text-end">Total</th>
                        <th class="text-end">{{ formato_bs($venta->monto_total) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
