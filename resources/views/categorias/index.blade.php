@extends('layouts.app')

@section('titulo', 'Categorías')
@section('subtitulo', 'Familias terapéuticas de medicamentos')

@section('contenido')
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <span><i class="bi bi-tags me-2 text-farma"></i>Categorías ({{ $categorias->total() }})</span>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('categorias.index') }}" class="d-flex gap-2">
                <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar categoría">
                <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <a href="{{ route('categorias.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nueva</a>
        </div>
    </div>
    <div class="card-body p-0">
        @if ($categorias->isEmpty())
            <x-empty-state icono="bi-tags" titulo="Aún no hay categorías" mensaje="Crea la primera categoría para organizar el catálogo." />
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Nombre</th><th>Descripción</th><th class="text-center">Medicamentos</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @foreach ($categorias as $categoria)
                            <tr>
                                <td class="fw-semibold">{{ $categoria->nombre }}</td>
                                <td class="text-muted small">{{ \Illuminate\Support\Str::limit($categoria->descripcion, 80) ?: '—' }}</td>
                                <td class="text-center"><span class="badge bg-light text-dark border">{{ $categoria->medicamentos_count }}</span></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    @if (auth()->user()->esAdministrador())
                                        <x-btn-eliminar :accion="route('categorias.destroy', $categoria)" :nombre="$categoria->nombre" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if ($categorias->hasPages())
        <div class="card-footer bg-white">{{ $categorias->links() }}</div>
    @endif
</div>
@endsection
