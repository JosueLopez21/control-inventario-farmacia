@extends('layouts.app')

@section('titulo', 'Nueva venta · formulario clásico')
@section('subtitulo', 'Alternativa sin JavaScript · mismo descuento FEFO')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-keyboard me-2 text-farma"></i>Registrar venta</span>
                <a href="{{ route('ventas.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-bag-check me-1"></i>Volver al POS táctil</a>
            </div>
            <div class="card-body">
                <p class="text-muted">Selecciona hasta cinco medicamentos. Las filas vacías se ignoran automáticamente.</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Revisa la venta:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('ventas.store') }}">
                    @csrf
                    <input type="hidden" name="modo" value="clasico">

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr><th style="min-width: 240px;">Medicamento</th><th style="width: 150px;">Cantidad</th></tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 5; $i++)
                                    <tr>
                                        <td>
                                            <select name="items[{{ $i }}][medicamento_id]" class="form-select" aria-label="Medicamento {{ $i + 1 }}">
                                                <option value="">— Sin seleccionar —</option>
                                                @foreach ($medicamentos as $medicamento)
                                                    <option value="{{ $medicamento->id }}" @selected((string) old("items.{$i}.medicamento_id") === (string) $medicamento->id)>{{ $medicamento->nombre_comercial }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" min="1" name="items[{{ $i }}][cantidad]" value="{{ old("items.{$i}.cantidad", 1) }}" class="form-control" aria-label="Cantidad {{ $i + 1 }}">
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info py-2 small mt-3"><i class="bi bi-info-circle me-1"></i>El sistema validará el stock vigente y aplicará FEFO al confirmar. Los lotes vencidos nunca se venden.</div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Registrar venta</button>
                        <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
