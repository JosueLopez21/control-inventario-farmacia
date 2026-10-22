@props(['lote'])

@php
    if ($lote->fecha_vencimiento->lt(now()->startOfDay())) {
        $clase = 'badge-vencido';
        $texto = 'Vencido';
        $icono = 'bi-x-octagon';
    } elseif ($lote->cantidad_stock <= 0) {
        $clase = 'badge-agotado';
        $texto = 'Agotado';
        $icono = 'bi-dash-circle';
    } elseif ($lote->fecha_vencimiento->lte(now()->addDays(30)->startOfDay())) {
        $clase = 'badge-porvencer';
        $texto = 'Por vencer';
        $icono = 'bi-exclamation-triangle';
    } else {
        $clase = 'badge-vigente';
        $texto = 'Vigente';
        $icono = 'bi-check-circle';
    }
@endphp

<span class="badge-estado {{ $clase }}"><i class="bi {{ $icono }} me-1"></i>{{ $texto }}</span>
