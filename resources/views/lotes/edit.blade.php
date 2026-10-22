@extends('layouts.app')

@section('titulo', 'Editar lote')
@section('subtitulo', $lote->numero_lote)

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Editar lote</div>
            <div class="card-body">
                <form method="POST" action="{{ route('lotes.update', $lote) }}" novalidate>
                    @csrf
                    @method('PUT')
                    @include('lotes._form')
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i>Actualizar lote</button>
                        <a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
