@extends('layouts.app')

@section('titulo', 'Lotes')
@section('subtitulo', 'Control de existencias por lote y vencimiento')

@section('contenido')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('lotes.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="estado">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="vigente" @selected($estado === 'vigente')>Vigentes</option>
                    <option value="por_vencer" @selected($estado === 'por_vencer')>Por vencer (30 días)</option>
                    <option value="vencido" @selected($estado === 'vencido')>Vencidos</option>
                    <option value="agotado" @selected($estado === 'agotado')>Agotados</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="medicamento_id">Medicamento</label>
                <select name="medicamento_id" id="medicamento_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($medicamentos as $medicamento)
                        <option value="{{ $medicamento->id }}" @selected((string) $medicamentoId === (string) $medicamento->id)>{{ $medicamento->nombre_comercial }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="q">Número de lote</label>
                <input type="text" name="q" id="q" value="{{ $q }}" class="form-control" placeholder="Ej. LOT-000123">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-eraser"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <span><i class="bi bi-box-seam me-2 text-farma"></i>Lotes ({{ $lotes->total() }})</span>
        @if (auth()->user()->puedeGestionarInventario())
            <a href="{{ route('lotes.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Registrar ingreso de lote</a>
        @endif
    </div>
    <div class="card-body p-0">
        @if ($lotes->isEmpty())
            <x-empty-state icono="bi-box-seam" titulo="No se encontraron lotes" mensaje="Ajusta los filtros o registra un nuevo ingreso de lote." />
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Medicamento</th>
                            <th>Vencimiento</th>
                            <th class="text-center">Stock</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lotes as $lote)
                            <tr>
                                <td class="fw-semibold">{{ $lote->numero_lote }}</td>
                                <td>{{ $lote->medicamento?->nombre_comercial ?? '—' }}</td>
                                <td>
                                    {{ formato_fecha($lote->fecha_vencimiento) }}
                                    @php($dias = dias_restantes($lote->fecha_vencimiento))
                                    @if ($dias >= 0 && $dias <= 30 && $lote->cantidad_stock > 0)
                                        <span class="d-block small text-warning-emphasis">Vence en {{ $dias }} día(s)</span>
                                    @elseif ($dias < 0)
                                        <span class="d-block small text-danger">Vencido hace {{ abs($dias) }} día(s)</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $lote->cantidad_stock }}</td>
                                <td><x-lote-badge :lote="$lote" /></td>
                                <td class="text-end text-nowrap">
                                    @if (auth()->user()->puedeGestionarInventario())
                                        <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    @endif
                                    @if (auth()->user()->esAdministrador())
                                        <x-btn-eliminar :accion="route('lotes.destroy', $lote)" :nombre="$lote->numero_lote" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if ($lotes->hasPages())
        <div class="card-footer bg-white">{{ $lotes->links() }}</div>
    @endif
</div>
@endsection
