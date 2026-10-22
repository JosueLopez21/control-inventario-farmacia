@extends('layouts.app')

@section('titulo', 'Acceso denegado')
@section('subtitulo', 'No tienes permiso para ver esta sección')

@section('contenido')
<div class="card">
    <div class="card-body m-4 text-center">
        <i class="bi bi-shield-lock text-danger" style="font-size: 4rem;"></i>
        <h2 class="h4 mt-3">Acceso denegado</h2>
        <p class="text-muted">Tu rol no tiene permisos para acceder a esta sección.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-house me-1"></i>Volver al dashboard</a>
    </div>
</div>
@endsection
