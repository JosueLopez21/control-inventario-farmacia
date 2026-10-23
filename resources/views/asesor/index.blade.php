@extends('layouts.app')

@section('titulo', 'Asesor de síntomas')
@section('subtitulo', 'Orientación educativa y medicamentos disponibles con stock vigente.')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/asesor.css') }}?v={{ filemtime(public_path('css/asesor.css')) }}">
@endpush

@section('contenido')
    <div class="asesor-hero mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-9">
                <span class="asesor-hero__pill"><i class="bi bi-clipboard2-pulse"></i> Orientación educativa</span>
                <h2 class="mt-2 mb-2">Asesor de síntomas</h2>
                <p class="mb-0">
                    Elige una molestia leve y consulta qué opciones de venta libre tienen stock vigente
                    en la farmacia. Esta herramienta no reemplaza una consulta médica ni entrega un diagnóstico.
                </p>
            </div>
            <div class="col-lg-3 text-lg-end">
                <span class="asesor-hero__icono" aria-hidden="true"><i class="bi bi-heart-pulse-fill"></i></span>
            </div>
        </div>
    </div>

    <div class="alert alert-warning border-0 shadow-sm d-flex gap-2 mb-4" role="alert">
        <i class="bi bi-shield-exclamation fs-5"></i>
        <div>
            <strong>Importante:</strong> este asesor no recomienda antibióticos ni medicamentos que requieren receta.
            Si presentas señales de alarma, acude a un centro de salud.
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card asesor-selector h-100">
                <div class="card-body p-3 p-md-4">
                    <span class="asesor-etiqueta">Paso 1</span>
                    <h2 class="h5 mb-2">¿Qué molestia tienes?</h2>
                    <p class="text-muted small mb-3">
                        Selecciona un síntoma leve. Si no encuentras tu caso, consulta directamente con el farmacéutico.
                    </p>

                    <form method="GET" action="{{ route('asesor.index') }}">
                        <label for="sintoma" class="form-label">Síntoma</label>
                        <select name="sintoma" id="sintoma" class="form-select form-select-lg mb-3" required>
                            <option value="">Selecciona una opción</option>
                            @foreach ($sintomas as $clave => $item)
                                <option value="{{ $clave }}" @selected($seleccionado === $clave)>
                                    {{ $item['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary w-100 asesor-btn">
                            <i class="bi bi-search me-1"></i>Ver orientación
                        </button>
                    </form>

                    <hr>

                    <div class="asesor-aviso">
                        <i class="bi bi-info-circle me-1"></i>
                        No guardamos esta consulta ni generamos una venta automáticamente.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if ($sintoma)
                <div class="card">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="asesor-sintoma-icono" style="--asesor-color: #0f766e">
                                <i class="bi {{ $sintoma['icono'] }}"></i>
                            </span>
                            <div>
                                <span class="asesor-etiqueta">Paso 2</span>
                                <h2 class="h5 mb-1">{{ $sintoma['nombre'] }}</h2>
                                <p class="text-muted small mb-0">{{ $sintoma['descripcion'] }}</p>
                            </div>
                        </div>

                        <div class="asesor-recomendacion mb-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            <span>{{ $sintoma['recomendacion'] }}</span>
                        </div>

                        <div class="alert alert-danger border-0 mb-4" role="alert">
                            <div class="fw-semibold mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Señales de alarma
                            </div>
                            <div class="small">{{ $sintoma['alerta'] }}</div>
                            <div class="small mt-2">
                                Ante dificultad para respirar, dolor intenso, desmayo o sangrado, busca atención médica inmediata.
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <h3 class="h6 mb-0">
                                <i class="bi bi-capsule text-farma me-1"></i>
                                Opciones con stock vigente
                            </h3>
                            @if ($resultados->isNotEmpty())
                                <span class="badge text-bg-light border">{{ $resultados->count() }} opciones</span>
                            @endif
                        </div>

                        @forelse ($resultados as $resultado)
                            <div class="asesor-resultado">
                                <span class="asesor-resultado__icono"
                                      style="--asesor-color: {{ $resultado['color'] }}; --asesor-suave: {{ $resultado['suave'] }}">
                                    <i class="bi {{ $resultado['icono'] }}"></i>
                                </span>

                                <div class="asesor-resultado__cuerpo">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h4 class="h6 mb-0">{{ $resultado['nombre'] }}</h4>
                                        <span class="asesor-categoria">{{ $resultado['categoria'] }}</span>
                                    </div>
                                    <div class="asesor-resultado__detalle">
                                        {{ $resultado['principio'] }} · {{ $resultado['presentacion'] }}
                                    </div>
                                    <div class="asesor-resultado__lote">
                                        <i class="bi bi-box-seam me-1"></i>
                                        Próximo lote: <strong>{{ $resultado['lote_numero'] }}</strong>
                                        @if ($resultado['lote_vence'])
                                            · vence {{ formato_fecha($resultado['lote_vence']) }}
                                        @endif
                                    </div>
                                </div>

                                <div class="asesor-resultado__acciones">
                                    <div class="asesor-resultado__precio">{{ formato_bs($resultado['precio']) }}</div>
                                    <span class="asesor-resultado__stock">
                                        <i class="bi bi-check-circle me-1"></i>{{ $resultado['stock'] }} disponibles
                                    </span>

                                    @if (auth()->user()->puedeOperarVentas())
                                        <a href="{{ route('ventas.create', ['medicamento_id' => $resultado['id']]) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-cart-plus me-1"></i>Agregar a venta
                                        </a>
                                    @else
                                        <span class="asesor-solo-lectura">
                                            <i class="bi bi-eye me-1"></i>Solo consulta
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="asesor-vacio">
                                <i class="bi bi-inbox"></i>
                                <p class="mb-1 fw-semibold">No hay medicamentos con stock vigente para esta selección.</p>
                                <p class="small mb-0">Consulta al farmacéutico por alternativas o revisa nuevamente más tarde.</p>
                            </div>
                        @endforelse

                        <p class="text-muted small mt-3 mb-0">
                            Se muestran hasta 8 productos. Verifica siempre la dosis y las indicaciones con un profesional.
                        </p>
                    </div>
                </div>
            @else
                <div class="card asesor-inicio h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                        <span class="asesor-inicio__icono"><i class="bi bi-clipboard2-heart"></i></span>
                        <h2 class="h5 mt-3 mb-2">Empieza seleccionando un síntoma</h2>
                        <p class="text-muted mb-4">
                            Verás recomendaciones generales, señales de alarma y productos de venta libre
                            disponibles, ordenados para mostrar primero el lote que vence antes.
                        </p>
                        <div class="asesor-garantias">
                            <span><i class="bi bi-shield-check"></i> Sin antibióticos</span>
                            <span><i class="bi bi-prescription2"></i> Sin recetas</span>
                            <span><i class="bi bi-database-check"></i> Solo stock vigente</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
