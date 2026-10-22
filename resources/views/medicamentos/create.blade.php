@extends('layouts.app')

@section('titulo', 'Nuevo medicamento')
@section('subtitulo', 'Registrar un medicamento en el catálogo')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Datos del medicamento</div>
            <div class="card-body">
                <form method="POST" action="{{ route('medicamentos.store') }}" novalidate>
                    @csrf
                    @include('medicamentos._form')
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Guardar medicamento</button>
                        <a href="{{ route('medicamentos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
