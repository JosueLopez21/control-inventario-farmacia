@extends('layouts.app')

@section('titulo', 'Nuevo usuario')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-plus me-2"></i>Datos del usuario</div>
            <div class="card-body">
                <form method="POST" action="{{ route('usuarios.store') }}" novalidate>
                    @csrf
                    @include('usuarios._form')
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i>Crear usuario</button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
