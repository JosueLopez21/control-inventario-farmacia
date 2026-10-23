@props(['tipo' => 'venta', 'datos' => []])
@php
    $esVenta = $tipo === 'venta';
    $idDialogo = 'animacion-exito-'.uniqid();
    $titulo = $esVenta ? '¡Venta exitosa!' : '¡Lote registrado!';
    $principal = $esVenta
        ? ($datos['comprobante'] ?? route('ventas.index'))
        : ($datos['ver_lotes'] ?? route('lotes.index'));
    $secundario = $esVenta
        ? ($datos['nueva'] ?? route('ventas.create'))
        : ($datos['otro'] ?? route('lotes.create'));
    $textoPrincipal = $esVenta ? 'Ver comprobante' : 'Ver lotes';
    $textoSecundario = $esVenta ? 'Nueva venta' : 'Registrar otro';
@endphp

<div class="animacion-exito"
     data-animacion-exito
     data-tipo="{{ $tipo }}"
     role="dialog"
     aria-modal="true"
     aria-labelledby="{{ $idDialogo }}-titulo"
     aria-describedby="{{ $idDialogo }}-detalle">
    <div class="animacion-exito__panel">
        <button type="button" class="animacion-exito__cerrar" data-cerrar-animacion aria-label="Cerrar">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>

        @if ($esVenta)
            <div class="animacion-exito__visual" aria-hidden="true">
                <svg class="animacion-verificacion" viewBox="0 0 140 140">
                    <circle class="animacion-verificacion__halo" cx="70" cy="70" r="64" />
                    <circle class="animacion-verificacion__aro" cx="70" cy="70" r="58" pathLength="1" />
                    <circle class="animacion-verificacion__relleno" cx="70" cy="70" r="48" />
                    <path class="animacion-verificacion__check" d="M43 72 L61 90 L98 49" pathLength="1" />
                </svg>
            </div>
        @else
            <div class="animacion-exito__visual" aria-hidden="true">
                <svg class="animacion-camion" viewBox="0 0 240 130">
                    <line class="animacion-camion__via" x1="8" y1="112" x2="232" y2="112" />
                    <g class="animacion-camion__vehiculo">
                        <path class="animacion-camion__caja" d="M24 42h92v52H24z" />
                        <path class="animacion-camion__cabina" d="M116 58h31l18 22v14h-49z" />
                        <path class="animacion-camion__ventana" d="M124 64h22l12 15h-34z" />
                        <circle class="animacion-camion__rueda" cx="55" cy="98" r="12" />
                        <circle class="animacion-camion__rueda" cx="146" cy="98" r="12" />
                        <circle class="animacion-camion__rin" cx="55" cy="98" r="5" />
                        <circle class="animacion-camion__rin" cx="146" cy="98" r="5" />
                    </g>
                    <g class="animacion-camion__check">
                        <circle cx="205" cy="34" r="20" />
                        <path d="M196 34l7 7 13-14" />
                    </g>
                </svg>
            </div>
        @endif

        <h2 class="animacion-exito__titulo" id="{{ $idDialogo }}-titulo">{{ $titulo }}</h2>

        <p class="animacion-exito__detalle" id="{{ $idDialogo }}-detalle" aria-live="polite">
            @if ($esVenta)
                Venta #{{ $datos['venta_id'] ?? '' }} registrada por un total de
                <strong>{{ $datos['total'] ?? 'Bs 0,00' }}</strong>.
            @else
                Lote <strong>{{ $datos['numero_lote'] ?? '' }}</strong> de
                <strong>{{ $datos['medicamento'] ?? 'medicamento' }}</strong> registrado correctamente.
            @endif
        </p>

        <div class="animacion-exito__acciones">
            <a href="{{ $principal }}" class="btn btn-primary btn-lg" data-animacion-principal>
                <i class="bi {{ $esVenta ? 'bi-receipt' : 'bi-box-seam' }} me-1"></i>{{ $textoPrincipal }}
            </a>
            <a href="{{ $secundario }}" class="btn btn-outline-secondary btn-lg">
                <i class="bi {{ $esVenta ? 'bi-plus-circle' : 'bi-plus-lg' }} me-1"></i>{{ $textoSecundario }}
            </a>
        </div>
    </div>
</div>
