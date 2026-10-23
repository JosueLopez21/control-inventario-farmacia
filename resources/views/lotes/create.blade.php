@extends('layouts.app')

@section('titulo', 'Registrar ingreso de lote')
@section('subtitulo', 'Nuevo lote de medicamento')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-box-arrow-in-down me-2"></i>Datos del lote</div>
            <div class="card-body">
                <form method="POST" action="{{ route('lotes.store') }}" novalidate>
                    @csrf
                    @include('lotes._form')
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i>Registrar lote</button>
                        <a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/lotes.js') }}?v={{ filemtime(public_path('js/lotes.js')) }}"></script>
@endpush
