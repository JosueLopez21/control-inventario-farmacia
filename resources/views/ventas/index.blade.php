@extends('layouts.app')

@section('titulo', 'Ventas')
@section('subtitulo', 'Historial de ventas registradas')

@section('contenido')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('ventas.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="fecha_inicio">Desde</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $desde }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="fecha_fin">Hasta</label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $hasta }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-eraser"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cart3 me-2 text-farma"></i>Ventas ({{ $ventas->total() }})</span>
        <a href="{{ route('ventas.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nueva venta</a>
    </div>
    <div class="card-body p-0">
        @if ($ventas->isEmpty())
            <x-empty-state icono="bi-cart3" titulo="Aún no hay ventas" mensaje="Registra la primera venta desde el punto de venta." />
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>N.º</th><th>Fecha y hora</th><th class="text-center">Ítems</th><th class="text-end">Total</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @foreach ($ventas as $venta)
                            <tr>
                                <td class="fw-semibold">#{{ $venta->id }}</td>
                                <td>{{ formato_fecha_hora($venta->fecha_venta) }}</td>
                                <td class="text-center"><span class="badge bg-light text-dark border">{{ $venta->detalles_count }}</span></td>
                                <td class="text-end fw-semibold">{{ formato_bs($venta->monto_total) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('ventas.show', $venta) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>Ver detalle</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if ($ventas->hasPages())
        <div class="card-footer bg-white">{{ $ventas->links() }}</div>
    @endif
</div>
@endsection
