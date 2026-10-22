@extends('layouts.app')

@section('titulo', 'Editar medicamento')
@section('subtitulo', $medicamento->nombre_comercial)

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Editar medicamento</div>
            <div class="card-body">
                <form method="POST" action="{{ route('medicamentos.update', $medicamento) }}" novalidate>
                    @csrf
                    @method('PUT')
                    @include('medicamentos._form')
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Actualizar medicamento</button>
                        <a href="{{ route('medicamentos.show', $medicamento) }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
