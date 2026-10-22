@extends('layouts.app')

@section('titulo', 'Laboratorios')
@section('subtitulo', 'Laboratorios fabricantes')

@section('contenido')
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <span><i class="bi bi-building me-2 text-farma"></i>Laboratorios ({{ $laboratorios->total() }})</span>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('laboratorios.index') }}" class="d-flex gap-2">
                <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar laboratorio">
                <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <a href="{{ route('laboratorios.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nuevo</a>
        </div>
    </div>
    <div class="card-body p-0">
        @if ($laboratorios->isEmpty())
            <x-empty-state icono="bi-building" titulo="Aún no hay laboratorios" mensaje="Registra el primer laboratorio fabricante." />
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Nombre</th><th>País</th><th>Contacto</th><th class="text-center">Medicamentos</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @foreach ($laboratorios as $laboratorio)
                            <tr>
                                <td class="fw-semibold">{{ $laboratorio->nombre }}</td>
                                <td>{{ $laboratorio->pais }}</td>
                                <td class="small text-muted">
                                    @if ($laboratorio->email)<div><i class="bi bi-envelope me-1"></i>{{ $laboratorio->email }}</div>@endif
                                    @if ($laboratorio->telefono)<div><i class="bi bi-telephone me-1"></i>{{ $laboratorio->telefono }}</div>@endif
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark border">{{ $laboratorio->medicamentos_count }}</span></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('laboratorios.edit', $laboratorio) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    @if (auth()->user()->esAdministrador())
                                        <x-btn-eliminar :accion="route('laboratorios.destroy', $laboratorio)" :nombre="$laboratorio->nombre" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if ($laboratorios->hasPages())
        <div class="card-footer bg-white">{{ $laboratorios->links() }}</div>
    @endif
</div>
@endsection
