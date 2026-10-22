@extends('layouts.app')

@section('titulo', 'Editar categoría')
@section('subtitulo', $categoria->nombre)

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Editar categoría</div>
            <div class="card-body">
                <form method="POST" action="{{ route('categorias.update', $categoria) }}" novalidate>
                    @csrf
                    @method('PUT')
                    @include('categorias._form')
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i>Actualizar</button>
                        <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
