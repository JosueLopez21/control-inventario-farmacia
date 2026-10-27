@props(['medicamento'])

@php
    $ui = categoria_ui($medicamento->categoria?->nombre);
    $hoy = now()->startOfDay();
    $lotes = $medicamento->lotes;
    $stock = (int) $lotes
        ->filter(fn ($lote) => $lote->fecha_vencimiento->gte($hoy) && $lote->cantidad_stock > 0)
        ->sum('cantidad_stock');
    $minimo = max(1, (int) $medicamento->stock_minimo);
    $porcentaje = min(100, (int) round(($stock / max($minimo * 2, 1)) * 100));
    $proximo = $lotes->first(fn ($lote) => $lote->fecha_vencimiento->gte($hoy) && $lote->cantidad_stock > 0);
    $dias = $proximo ? dias_restantes($proximo->fecha_vencimiento) : null;

    if ($stock <= 0) {
        $estado = 'agotado';
        $stockColor = '#64748b';
    } elseif ($stock < $minimo) {
        $estado = 'bajo';
        $stockColor = '#dc2626';
    } elseif ($stock < $minimo * 2) {
        $estado = 'medio';
        $stockColor = '#f59e0b';
    } else {
        $estado = 'ok';
        $stockColor = '#16a34a';
    }

    $imagen = imagen_medicamento($medicamento);

    $puedeVender = auth()->user()->puedeOperarVentas();
    $puedeEditar = auth()->user()->puedeGestionarInventario();
    $puedeEliminar = auth()->user()->esAdministrador();

    $datos = [
        'id' => $medicamento->id,
        'nombre' => $medicamento->nombre_comercial,
        'principio' => $medicamento->principio_activo,
        'presentacion' => $medicamento->presentacion,
        'categoria' => $medicamento->categoria?->nombre ?? 'Sin categoría',
        'categoria_color' => $ui['color'],
        'categoria_suave' => $ui['suave'],
        'categoria_icono' => $ui['icono'],
        'laboratorio' => $medicamento->laboratorio?->nombre ?? 'Sin laboratorio',
        'imagen' => $imagen,
        'precio' => (float) $medicamento->precio_venta,
        'precio_texto' => formato_bs($medicamento->precio_venta),
        'stock' => $stock,
        'stock_minimo' => $minimo,
        'estado' => $estado,
        'lotes' => $lotes->map(function ($lote) use ($hoy) {
            $diasLote = dias_restantes($lote->fecha_vencimiento);
            $estadoLote = $lote->fecha_vencimiento->lt($hoy)
                ? 'Vencido'
                : ($lote->cantidad_stock <= 0 ? 'Agotado' : ($diasLote <= 30 ? 'Por vencer' : 'Vigente'));

            return [
                'numero' => $lote->numero_lote,
                'fecha' => formato_fecha($lote->fecha_vencimiento),
                'dias' => $diasLote,
                'stock' => (int) $lote->cantidad_stock,
                'estado' => $estadoLote,
            ];
        })->values()->all(),
        'urls' => [
            'vender' => route('ventas.create', ['medicamento_id' => $medicamento->id]),
            'editar' => route('medicamentos.edit', $medicamento),
            'eliminar' => route('medicamentos.destroy', $medicamento),
            'detalle' => route('medicamentos.show', $medicamento),
        ],
        'permisos' => [
            'vender' => $puedeVender,
            'editar' => $puedeEditar,
            'eliminar' => $puedeEliminar,
        ],
    ];
@endphp

<article
    class="med-card"
    tabindex="0"
    role="button"
    aria-label="Ver detalle de {{ $medicamento->nombre_comercial }}"
    data-abrir-medicamento
    data-medicamento="{{ json_encode($datos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    style="--cat-color: {{ $ui['color'] }}; --cat-soft: {{ $ui['suave'] }}; --stock-color: {{ $stockColor }}; --stock-pct: {{ $porcentaje }}%;"
>
    @if ($imagen)
        <div class="med-card__foto">
            <img src="{{ $imagen }}" alt="{{ $medicamento->nombre_comercial }}" loading="lazy" decoding="async">
        </div>
    @endif

    <div class="med-card__head">
        <span class="cat-icon"><i class="bi {{ $ui['icono'] }}"></i></span>
        <div class="min-w-0">
            <span class="med-card__category">{{ $medicamento->categoria?->nombre ?? 'Sin categoría' }}</span>
            <h3 class="med-card__title">{{ $medicamento->nombre_comercial }}</h3>
            <p class="med-card__active">{{ $medicamento->principio_activo }}</p>
        </div>
    </div>

    <p class="med-card__presentacion"><i class="bi bi-box me-1"></i>{{ $medicamento->presentacion }}</p>

    <div class="med-card__price">
        {{ formato_bs($medicamento->precio_venta) }}
        <small class="ms-1">{{ $medicamento->laboratorio?->nombre }}</small>
    </div>

    <div class="med-card__stock">
        <div class="stock-line">
            <span>Stock vigente</span>
            <span>{{ $stock }} / mín. {{ $minimo }}</span>
        </div>
        <div class="stock-bar" role="img" aria-label="Nivel de stock: {{ $stock }} de {{ $minimo * 2 }}">
            <span class="stock-bar__fill"></span>
        </div>
    </div>

    <div class="med-card__badges">
        @if ($estado === 'agotado')
            <span class="badge-feria bajo"><i class="bi bi-x-circle"></i> Agotado</span>
        @elseif ($estado === 'bajo')
            <span class="badge-feria bajo"><i class="bi bi-arrow-down-circle"></i> Stock bajo</span>
        @elseif ($estado === 'medio')
            <span class="badge-feria medio"><i class="bi bi-exclamation-circle"></i> Reponer pronto</span>
        @else
            <span class="badge-feria ok"><i class="bi bi-check-circle"></i> Disponible</span>
        @endif

        @if ($dias !== null && $dias <= 30)
            <span class="badge-feria medio"><i class="bi bi-hourglass-split"></i> Vence en {{ $dias }} días</span>
        @endif

        @if ($lotes->contains(fn ($lote) => $lote->fecha_vencimiento->lt($hoy)))
            <span class="badge-feria neutro"><i class="bi bi-archive"></i> Con lotes vencidos</span>
        @endif
    </div>

    <div class="med-card__actions">
        <button type="button" class="btn btn-ver flex-grow-1" data-ver-medicamento>
            <i class="bi bi-eye me-1"></i>Ver detalle
        </button>
        @if ($puedeVender && $stock > 0)
            <a href="{{ route('ventas.create', ['medicamento_id' => $medicamento->id]) }}" class="btn btn-primary flex-grow-1">
                <i class="bi bi-cart-plus me-1"></i>Vender
            </a>
        @endif
    </div>
</article>
