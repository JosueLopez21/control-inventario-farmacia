@extends('layouts.app')

@section('titulo', 'Detalle del medicamento')
@section('subtitulo', $medicamento->nombre_comercial)

@section('contenido')
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('medicamentos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    @if (auth()->user()->puedeGestionarInventario())
        <a href="{{ route('medicamentos.edit', $medicamento) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Editar</a>
        <a href="{{ route('lotes.create') }}" class="btn btn-outline-primary"><i class="bi bi-box-seam me-1"></i>Registrar lote</a>
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Ficha del medicamento</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Nombre comercial</dt><dd class="col-7 fw-semibold">{{ $medicamento->nombre_comercial }}</dd>
                    <dt class="col-5 text-muted">Principio activo</dt><dd class="col-7">{{ $medicamento->principio_activo }}</dd>
                    <dt class="col-5 text-muted">Presentación</dt><dd class="col-7">{{ $medicamento->presentacion }}</dd>
                    <dt class="col-5 text-muted">Categoría</dt><dd class="col-7">{{ $medicamento->categoria?->nombre ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Laboratorio</dt><dd class="col-7">{{ $medicamento->laboratorio?->nombre ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Precio de venta</dt><dd class="col-7 fw-semibold text-farma">{{ formato_bs($medicamento->precio_venta) }}</dd>
                    <dt class="col-5 text-muted">Stock mínimo</dt><dd class="col-7">{{ $medicamento->stock_minimo }}</dd>
                    <dt class="col-5 text-muted">Stock vigente</dt>
                    <dd class="col-7">
                        @if ($stockVigente < $medicamento->stock_minimo)
                            <span class="badge bg-danger">{{ $stockVigente }} · bajo mínimo</span>
                        @else
                            <span class="badge bg-success">{{ $stockVigente }} disponible(s)</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-box-seam me-2"></i>Lotes ({{ $medicamento->lotes->count() }})</div>
            <div class="card-body p-0">
                @if ($medicamento->lotes->isEmpty())
                    <x-empty-state icono="bi-box-seam" titulo="Sin lotes registrados" mensaje="Este medicamento todavía no tiene lotes de ingreso." />
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Lote</th><th>Vencimiento</th><th class="text-center">Cantidad</th><th>Estado</th></tr></thead>
                            <tbody>
                                @foreach ($medicamento->lotes as $lote)
                                    <tr>
                                        <td class="fw-semibold">{{ $lote->numero_lote }}</td>
                                        <td>{{ formato_fecha($lote->fecha_vencimiento) }}</td>
                                        <td class="text-center">{{ $lote->cantidad_stock }}</td>
                                        <td><x-lote-badge :lote="$lote" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
