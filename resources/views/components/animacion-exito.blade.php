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
                <strong>{{ $datos['total'] ?? 'Bs 0,00' }}</strong>@if (! empty($datos['metodo'])) · {{ $datos['metodo'] }}@endif.
            @else
                Lote <strong>{{ $datos['numero_lote'] ?? '' }}</strong> de
                <strong>{{ $datos['medicamento'] ?? 'medicamento' }}</strong> registrado correctamente.
            @endif
        </p>

        @if ($esVenta && ! empty($datos['efectivo']))
            <div class="animacion-exito__cambio" data-cambio-venta aria-live="polite">
                <span>Recibido {{ $datos['recibido'] ?? '' }}</span>
                @if (! empty($datos['hay_cambio']))
                    <strong>Cambio a devolver: {{ $datos['cambio'] ?? '' }}</strong>
                @else
                    <strong>Pago exacto · sin cambio</strong>
                @endif
            </div>
        @endif

        @if ($esVenta && ! empty($datos['stock']))
            <div class="animacion-exito__stock" data-stock-venta>
                @foreach ($datos['stock'] as $fila)
                    <div class="animacion-exito__fila">
                        <span class="nombre">{{ $fila['medicamento'] }}</span>
                        <span class="valores">−{{ $fila['cantidad'] }} · stock {{ $fila['antes'] }} → <strong>{{ $fila['despues'] }}</strong></span>
                        @foreach ($fila['lotes'] ?? [] as $loteFila)
                            <span class="lote">Lote {{ $loteFila['numero_lote'] }}: −{{ $loteFila['cantidad'] }} ({{ $loteFila['antes'] }} → {{ $loteFila['despues'] }})</span>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        @if ($esVenta && ! empty($datos['movimientos']))
            <a href="{{ $datos['movimientos'] }}" class="animacion-exito__enlace" data-ver-movimientos>
                <i class="bi bi-arrow-left-right me-1"></i>Ver qué lotes bajaron en Movimientos
            </a>
        @endif

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
