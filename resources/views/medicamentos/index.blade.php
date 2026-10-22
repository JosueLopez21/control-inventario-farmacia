@extends('layouts.app')

@section('titulo', 'Medicamentos')
@section('subtitulo', 'Catálogo de medicamentos y stock vigente')

@section('contenido')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('medicamentos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="q">Buscar</label>
                <input type="text" name="q" id="q" value="{{ $q }}" class="form-control" placeholder="Nombre o principio activo">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="categoria_id">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected((string) $categoriaId === (string) $categoria->id)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="laboratorio_id">Laboratorio</label>
                <select name="laboratorio_id" id="laboratorio_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($laboratorios as $laboratorio)
                        <option value="{{ $laboratorio->id }}" @selected((string) $laboratorioId === (string) $laboratorio->id)>{{ $laboratorio->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Filtrar</button>
                <a href="{{ route('medicamentos.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-eraser"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-capsule me-2 text-farma"></i>Medicamentos ({{ $medicamentos->total() }})</span>
        @if (auth()->user()->puedeGestionarInventario())
            <a href="{{ route('medicamentos.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nuevo medicamento</a>
        @endif
    </div>
    <div class="card-body p-0">
        @if ($medicamentos->isEmpty())
            <x-empty-state icono="bi-capsule" titulo="No se encontraron medicamentos" mensaje="Prueba con otro término de búsqueda o cambia los filtros." />
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Principio activo</th>
                            <th>Categoría</th>
                            <th>Laboratorio</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Stock vigente</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($medicamentos as $medicamento)
                            @php($stock = (int) ($medicamento->stock_vigente ?? 0))
                            <tr>
                                <td class="fw-semibold">{{ $medicamento->nombre_comercial }}</td>
                                <td>{{ $medicamento->principio_activo }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $medicamento->categoria?->nombre ?? '—' }}</span></td>
                                <td>{{ $medicamento->laboratorio?->nombre ?? '—' }}</td>
                                <td class="text-end">{{ formato_bs($medicamento->precio_venta) }}</td>
                                <td class="text-center">
                                    @if ($stock < $medicamento->stock_minimo)
                                        <span class="badge bg-danger" title="Bajo el mínimo ({{ $medicamento->stock_minimo }})">{{ $stock }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success-emphasis">{{ $stock }}</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('medicamentos.show', $medicamento) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle"><i class="bi bi-eye"></i></a>
                                    @if (auth()->user()->puedeGestionarInventario())
                                        <a href="{{ route('medicamentos.edit', $medicamento) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    @endif
                                    @if (auth()->user()->esAdministrador())
                                        <x-btn-eliminar :accion="route('medicamentos.destroy', $medicamento)" :nombre="$medicamento->nombre_comercial" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if ($medicamentos->hasPages())
        <div class="card-footer bg-white">{{ $medicamentos->links() }}</div>
    @endif
</div>
@endsection
