@extends('layouts.app')

@section('titulo', 'Juego')
@section('subtitulo', 'Aprende sobre el catálogo y el orden de salida de los lotes.')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/juego.css') }}?v={{ filemtime(public_path('css/juego.css')) }}">
@endpush

@section('contenido')
    @php
        $fechaLoteA = formato_fecha(now()->addMonths(14));
        $fechaLoteB = formato_fecha(now()->addMonths(4));
        $fechaLoteC = formato_fecha(now()->subMonths(2));
    @endphp

    <div class="juego-hero mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <span class="juego-hero__pill"><i class="bi bi-dice-5"></i> Aprende jugando</span>
                <h2 class="mt-2 mb-2">La ruleta del catálogo</h2>
                <p class="mb-0">
                    Cinco giros, cinco preguntas fáciles. La ruleta elige un tema y tú respondes
                    antes de que se acabe el tiempo. No necesitas saber de medicamentos: ¡se aprende jugando!
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="juego-record">
                    <i class="bi bi-trophy"></i>
                    <span>Mejor puntaje</span>
                    <strong data-mejor>0</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card h-100" data-juego-ruleta data-url="{{ route('juego.pregunta') }}">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                        <div>
                            <span class="juego-etiqueta">Ruleta del catálogo</span>
                            <h3 class="h5 mb-0">Gira y responde</h3>
                        </div>
                        <div class="juego-marcador">
                            <i class="bi bi-stars"></i>
                            Puntaje <strong data-puntaje>0</strong>
                        </div>
                    </div>

                    <div class="juego-escenario">
                        <div class="ruleta-marco">
                            <div class="ruleta-flecha" aria-hidden="true"><i class="bi bi-caret-down-fill"></i></div>
                            <div class="ruleta" data-ruleta role="img" aria-label="Ruleta de categorías de medicamentos">
                                <div class="ruleta-centro">
                                    <i class="bi bi-heart-pulse-fill"></i>
                                    <span>GIRA</span>
                                </div>
                            </div>
                        </div>

                        <div class="juego-controles">
                            <div class="juego-turno">
                                Giro <strong data-giro-actual>0</strong> de 5
                            </div>
                            <button type="button" class="btn btn-primary btn-lg w-100" data-girar>
                                <i class="bi bi-arrow-repeat me-2"></i>Girar la ruleta
                            </button>
                            <p class="juego-estado mb-0" data-estado aria-live="polite">
                                Toca el botón para comenzar la partida.
                            </p>

                            @if ($categorias->isNotEmpty())
                                <div class="juego-leyenda" aria-label="Categorías de la ruleta">
                                    @foreach ($categorias as $categoria)
                                        <span class="juego-chip">
                                            <span class="juego-chip__punto" style="background: {{ $categoria['color'] }}"></span>
                                            {{ $categoria['nombre'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="juego-telon" data-telon hidden>
                        <div class="juego-banner" data-banner aria-live="polite" hidden>
                            <i class="bi" data-banner-icono aria-hidden="true"></i>
                            <span class="juego-banner__pre">¡Te tocó!</span>
                            <strong data-banner-texto></strong>
                        </div>

                        <div class="juego-pregunta" data-pregunta role="dialog" aria-modal="true" aria-labelledby="juegoPreguntaTexto" hidden>
                            <div class="juego-pregunta__cabecera" data-pregunta-cabecera>
                                <span class="juego-categoria-chip" data-pregunta-categoria></span>
                                <span class="juego-pregunta__giro">Pregunta <strong data-pregunta-numero>1</strong> de 5</span>
                            </div>
                            <div class="juego-tiempo" aria-hidden="true"><span data-tiempo-barra></span></div>
                            <h4 class="juego-pregunta__texto" id="juegoPreguntaTexto" data-pregunta-texto></h4>
                            <div class="juego-opciones" data-opciones></div>
                            <div class="juego-feedback" data-feedback role="status" aria-live="polite"></div>
                            <button type="button" class="btn btn-primary btn-lg mt-3" data-siguiente hidden>
                                Continuar <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="juego-resultado" data-resultado hidden>
                        <div class="juego-resultado__icono"><i class="bi bi-trophy-fill"></i></div>
                        <h4 class="h5 mb-1" data-resultado-titulo>Partida terminada</h4>
                        <p class="text-muted mb-3" data-resultado-texto></p>
                        <div class="juego-resultado__puntaje">
                            <strong data-resultado-puntaje>0</strong>
                            <span>/ 5 aciertos</span>
                        </div>
                        <p class="juego-record-nuevo mb-0" data-record hidden>
                            <i class="bi bi-stars me-1"></i>Nuevo récord personal
                        </p>
                        <button type="button" class="btn btn-primary mt-3" data-reiniciar>
                            <i class="bi bi-arrow-clockwise me-1"></i>Jugar de nuevo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card mb-4">
                <div class="card-body p-3 p-md-4">
                    <span class="juego-etiqueta">Reto rápido</span>
                    <h3 class="h5 mb-2">¿Qué lote sale primero?</h3>
                    <p class="text-muted small mb-3">
                        Recuerda: primero se despacha el lote vigente que vence antes.
                        Elige el lote correcto.
                    </p>

                    <div class="juego-fefo" data-juego-fefo>
                        <button type="button" class="fefo-lote" data-correcta="0">
                            <span>
                                <strong>Lote A</strong>
                                <small>Vence {{ $fechaLoteA }}</small>
                            </span>
                            <span class="fefo-lote__stock">18 unidades</span>
                        </button>
                        <button type="button" class="fefo-lote" data-correcta="1">
                            <span>
                                <strong>Lote B</strong>
                                <small>Vence {{ $fechaLoteB }}</small>
                            </span>
                            <span class="fefo-lote__stock">9 unidades</span>
                        </button>
                        <button type="button" class="fefo-lote" data-correcta="0">
                            <span>
                                <strong>Lote C</strong>
                                <small>Vence {{ $fechaLoteC }}</small>
                            </span>
                            <span class="fefo-lote__stock">4 unidades</span>
                        </button>
                    </div>

                    <div class="fefo-feedback" id="fefoFeedback" role="status" aria-live="polite">
                        <i class="bi bi-question-circle me-1"></i>Toca un lote para responder.
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 d-none" id="fefoReiniciar">
                        <i class="bi bi-arrow-clockwise me-1"></i>Intentar de nuevo
                    </button>
                </div>
            </div>

            <div class="card juego-nota">
                <div class="card-body">
                    <h3 class="h6"><i class="bi bi-lightbulb text-warning me-1"></i>¿Cómo se juega?</h3>
                    <ul class="mb-0">
                        <li>Gira la ruleta y responde la pregunta del tema que te toque.</li>
                        <li>Tienes 20 segundos por pregunta.</li>
                        <li>Cada respuesta correcta suma un punto.</li>
                        <li>Al terminar los cinco giros verás tu resultado y podrás intentarlo otra vez.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="juegoCategorias">@json($categorias)</script>
    <script src="{{ asset('js/juego.js') }}?v={{ filemtime(public_path('js/juego.js')) }}"></script>
@endpush
