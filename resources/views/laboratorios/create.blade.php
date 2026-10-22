@extends('layouts.app')

@section('titulo', 'Nuevo laboratorio')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-building-add me-2"></i>Datos del laboratorio</div>
            <div class="card-body">
                <form method="POST" action="{{ route('laboratorios.store') }}" novalidate>
                    @csrf
                    @include('laboratorios._form')
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i>Guardar</button>
                        <a href="{{ route('laboratorios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
