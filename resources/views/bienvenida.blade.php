@extends('layouts.landing')

@section('titulo', 'Bienvenida')

@section('contenido')
<section class="landing-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="landing-badge"><i class="bi bi-stars"></i>Demo interactiva de feria</span>
                <h1>La farmacia que se siente <span>viva</span></h1>
                <p class="landing-lead">Catálogo visual, alertas con semáforo, punto de venta táctil y ventas que respetan el lote que vence primero. Todo en un solo panel.</p>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Entrar al sistema
                    </a>
                    @if ($demoAccess)
                        <a href="#demo" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-play-circle me-1"></i>Probar demo
                        </a>
                    @endif
                </div>

                <div class="landing-sellos">
                    <span><i class="bi bi-shield-check"></i>FEFO automático</span>
                    <span><i class="bi bi-lock"></i>Roles protegidos</span>
                    <span><i class="bi bi-phone"></i>Desde 360 px</span>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="landing-mockup" aria-hidden="true">
                    <div class="landing-mockup__barra">
                        <span class="landing-puntos"><i></i><i></i><i></i></span>
                        <span>Panel FarmaSalud</span>
                    </div>
                    <div class="landing-mockup__cuerpo">
                        <div class="mock-alerta">
                            <span class="mock-alerta__icono"><i class="bi bi-exclamation-triangle"></i></span>
                            <div>
                                <strong>12 lotes por vencer</strong>
                                <span>Prioriza su salida con FEFO</span>
                            </div>
                            <span class="mock-pulso"></span>
                        </div>

                        <div class="mock-fila">
                            <div class="mock-mini">
                                <i class="bi bi-capsule"></i>
                                <strong>{{ number_format($totalMedicamentos, 0, ',', '.') }}</strong>
                                <span>medicamentos</span>
                            </div>
                            <div class="mock-mini mock-mini--azul">
                                <i class="bi bi-graph-up-arrow"></i>
                                <strong>7</strong>
                                <span>días de ventas</span>
                            </div>
                        </div>

                        <div class="mock-pos">
                            <div class="mock-pos__linea">
                                <span class="mock-icono"><i class="bi bi-capsule-pill"></i></span>
                                <span><strong>Panadol</strong><small>Stock 24 · Bs 12,50</small></span>
                                <span class="mock-mas"><i class="bi bi-plus-lg"></i></span>
                            </div>
                            <div class="mock-pos__total">
                                <span>Total a pagar</span>
                                <strong>Bs 37,50</strong>
                            </div>
                        </div>
                    </div>
                    <span class="landing-flotante landing-flotante--uno"><i class="bi bi-check2-circle"></i>Venta segura</span>
                    <span class="landing-flotante landing-flotante--dos"><i class="bi bi-stoplights"></i>Semáforo activo</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="landing-stats">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="landing-stat">
                    <span class="landing-stat__icono"><i class="bi bi-capsule"></i></span>
                    <strong data-contador="landingMedicamentos" data-valor="{{ $totalMedicamentos }}" data-formato="numero">{{ $totalMedicamentos }}</strong>
                    <span>Medicamentos</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="landing-stat">
                    <span class="landing-stat__icono landing-stat__icono--azul"><i class="bi bi-tags"></i></span>
                    <strong data-contador="landingCategorias" data-valor="{{ $totalCategorias }}" data-formato="numero">{{ $totalCategorias }}</strong>
                    <span>Categorías</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="landing-stat">
                    <span class="landing-stat__icono landing-stat__icono--ambar"><i class="bi bi-building"></i></span>
                    <strong data-contador="landingLaboratorios" data-valor="{{ $totalLaboratorios }}" data-formato="numero">{{ $totalLaboratorios }}</strong>
                    <span>Laboratorios</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="landing-stat">
                    <span class="landing-stat__icono landing-stat__icono--rojo"><i class="bi bi-box-seam"></i></span>
                    <strong data-contador="landingLotes" data-valor="{{ $totalLotes }}" data-formato="numero">{{ $totalLotes }}</strong>
                    <span>Lotes registrados</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="landing-reto" id="reto">
    <div class="container">
        <div class="landing-reto__caja">
            <div class="row align-items-center g-4">
                <div class="col-lg-5">
                    <span class="landing-badge landing-badge--oscuro"><i class="bi bi-controller"></i>Mini reto</span>
                    <h2>¿Qué lote sale primero?</h2>
                    <p>Un cliente pide una caja de Panadol. Elige el lote correcto según FEFO y descubre si la farmacia está segura.</p>
                    <div class="reto-feedback" id="retoFeedback" aria-live="polite">
                        <i class="bi bi-question-circle"></i>
                        <span>Toca un lote para responder.</span>
                    </div>
                    <button type="button" class="btn btn-outline-primary mt-3 d-none" id="retoReiniciar">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reintentar
                    </button>
                </div>
                <div class="col-lg-7">
                    <div class="row g-3" data-juego>
                        <div class="col-sm-4">
                            <button type="button" class="reto-lote" data-correcta="0">
                                <span class="reto-lote__etiqueta">Lote A</span>
                                <strong>Vence en 20 días</strong>
                                <span class="reto-lote__stock">Stock: 8</span>
                            </button>
                        </div>
                        <div class="col-sm-4">
                            <button type="button" class="reto-lote" data-correcta="1">
                                <span class="reto-lote__etiqueta">Lote B</span>
                                <strong>Vence en 13 días</strong>
                                <span class="reto-lote__stock">Stock: 3</span>
                            </button>
                        </div>
                        <div class="col-sm-4">
                            <button type="button" class="reto-lote reto-lote--vencido" data-correcta="0">
                                <span class="reto-lote__etiqueta">Lote C</span>
                                <strong>Venció hace 2 días</strong>
                                <span class="reto-lote__stock">Stock: 15</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="landing-seccion" id="modulos">
    <div class="container">
        <div class="landing-titulo">
            <span class="landing-badge landing-badge--oscuro"><i class="bi bi-grid-3x3-gap"></i>Módulos</span>
            <h2>Menos planillas, más farmacia</h2>
            <p>Cada pantalla está pensada para explicar el sistema en segundos: tocar, filtrar y entender.</p>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-xl-4">
                <div class="landing-modulo">
                    <span class="landing-modulo__icono"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                    <h3>Catálogo visual</h3>
                    <p>Tarjetas con categoría, stock vigente, vencimiento próximo y detalle FEFO sin salir de la página.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="landing-modulo">
                    <span class="landing-modulo__icono landing-modulo__icono--ambar"><i class="bi bi-stoplights"></i></span>
                    <h3>Alertas con semáforo</h3>
                    <p>Rojo para lotes vencidos, ámbar para lo que vence pronto y gris para reponer stock.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="landing-modulo">
                    <span class="landing-modulo__icono landing-modulo__icono--azul"><i class="bi bi-bag-check"></i></span>
                    <h3>POS táctil</h3>
                    <p>Busca, toca y suma. Botones grandes, resumen claro y alternativa clásica sin JavaScript.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="landing-modulo">
                    <span class="landing-modulo__icono landing-modulo__icono--morado"><i class="bi bi-arrow-repeat"></i></span>
                    <h3>FEFO automático</h3>
                    <p>La venta descuenta del lote vigente con vencimiento más cercano y bloquea los caducados.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="landing-modulo">
                    <span class="landing-modulo__icono landing-modulo__icono--verde"><i class="bi bi-bar-chart-line"></i></span>
                    <h3>Reportes claros</h3>
                    <p>Ventas del día, ingresos, ticket promedio y ranking de medicamentos en un vistazo.</p>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="landing-modulo">
                    <span class="landing-modulo__icono landing-modulo__icono--rojo"><i class="bi bi-people"></i></span>
                    <h3>Tres roles</h3>
                    <p>Administrador, farmacéutico y doctor con permisos separados y acciones visibles según el rol.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@if ($demoAccess)
    <section class="landing-demo" id="demo">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="landing-badge"><i class="bi bi-lightning-charge"></i>Acceso rápido</span>
                    <h2 class="mt-3">Entra a la demo sin escribir contraseñas</h2>
                    <p>Ideal para recorrer la feria. Elige un rol y el sistema te lleva directo al panel con datos reales del inventario.</p>
                    <p class="small text-white-50 mb-0"><i class="bi bi-info-circle me-1"></i>La demo está habilitada temporalmente. Al terminar, cierra sesión desde el menú superior.</p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <form method="POST" action="{{ route('demo.entrar', ['rol' => 'farmaceutico']) }}">
                                @csrf
                                <button type="submit" class="landing-demo-card w-100">
                                    <span class="landing-demo-card__icono"><i class="bi bi-capsule"></i></span>
                                    <strong>Farmacéutico</strong>
                                    <span>Ventas, inventario y reportes</span>
                                    <span class="landing-demo-card__accion">Entrar <i class="bi bi-arrow-right"></i></span>
                                </button>
                            </form>
                        </div>
                        <div class="col-sm-6">
                            <form method="POST" action="{{ route('demo.entrar', ['rol' => 'doctor']) }}">
                                @csrf
                                <button type="submit" class="landing-demo-card w-100">
                                    <span class="landing-demo-card__icono landing-demo-card__icono--azul"><i class="bi bi-heart-pulse"></i></span>
                                    <strong>Doctor</strong>
                                    <span>Catálogo y alertas de consulta</span>
                                    <span class="landing-demo-card__accion">Entrar <i class="bi bi-arrow-right"></i></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

<section class="landing-cierre">
    <div class="container text-center">
        <span class="landing-badge landing-badge--oscuro"><i class="bi bi-box-arrow-in-right"></i>Acceso</span>
        <h2>¿Listo para recorrer la farmacia?</h2>
        <p>Inicia sesión con tu cuenta o usa la demo para ver el flujo completo.</p>
        <div class="d-flex justify-content-center flex-wrap gap-2">
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg"><i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión</a>
            @if ($demoAccess)
                <a href="#demo" class="btn btn-outline-primary btn-lg"><i class="bi bi-play-circle me-1"></i>Probar demo</a>
            @endif
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/juego.js') }}?v={{ filemtime(public_path('js/juego.js')) }}"></script>
@endpush
