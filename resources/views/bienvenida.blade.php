@extends('layouts.landing')

@section('titulo', 'Bienvenida')

@section('contenido')
<section class="landing-hero">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <span class="landing-badge"><i class="bi bi-heart-pulse"></i>FarmaSalud</span>
                <h1>Control de inventario <span>claro y seguro</span></h1>
                <p class="landing-lead">
                    FarmaSalud reúne el catálogo de medicamentos, los lotes con fecha de vencimiento,
                    las ventas y las alertas en un solo panel. Al vender, descuenta primero el lote
                    que vence antes para evitar pérdidas y entregar siempre productos vigentes.
                </p>
                <p class="landing-lead landing-lead--breve">
                    También avisa cuando un lote está por vencer o cuando el stock cae por debajo del mínimo.
                </p>

                <ul class="landing-puntos-clave">
                    <li><i class="bi bi-box-seam"></i>Inventario y lotes bajo control</li>
                    <li><i class="bi bi-bag-check"></i>Ventas seguras, primero lo que vence antes</li>
                    <li><i class="bi bi-bell"></i>Alertas de vencimiento y stock bajo</li>
                </ul>

                <div class="landing-acciones">
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg fw-semibold landing-btn-principal">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Entrar al sistema
                    </a>

                    @if ($demoAccess)
                        <div class="landing-demo-rapida">
                            <span class="landing-demo-rapida__titulo">Acceso rápido demo</span>
                            <div class="d-flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('demo.entrar', ['rol' => 'farmaceutico']) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-light">
                                        <i class="bi bi-capsule me-1"></i>Farmacéutico
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('demo.entrar', ['rol' => 'doctor']) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-light">
                                        <i class="bi bi-heart-pulse me-1"></i>Doctor
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-6">
                <figure class="landing-foto">
                    <img src="{{ asset('img/doctora.webp') }}"
                         alt="Profesional de la salud"
                         width="740"
                         height="592"
                         loading="lazy">
                </figure>
            </div>
        </div>
    </div>
</section>
@endsection
