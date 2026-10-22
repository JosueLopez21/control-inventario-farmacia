@extends('layouts.app')

@section('titulo', 'Nueva categoría')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-tag me-2"></i>Datos de la categoría</div>
            <div class="card-body">
                <form method="POST" action="{{ route('categorias.store') }}" novalidate>
                    @csrf
                    @include('categorias._form')
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i>Guardar</button>
                        <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
