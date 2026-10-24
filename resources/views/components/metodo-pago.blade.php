@props(['metodo' => 'efectivo'])
@php
    $config = [
        'efectivo' => ['icono' => 'bi-cash-coin', 'clase' => 'bg-success-subtle text-success-emphasis'],
        'tarjeta' => ['icono' => 'bi-credit-card-2-front', 'clase' => 'bg-primary-subtle text-primary-emphasis'],
        'qr' => ['icono' => 'bi-qr-code', 'clase' => 'bg-warning-subtle text-warning-emphasis'],
    ][$metodo] ?? ['icono' => 'bi-wallet2', 'clase' => 'bg-light text-dark'];
    $etiqueta = \App\Models\Venta::METODOS_PAGO[$metodo] ?? ucfirst((string) $metodo);
@endphp
<span class="badge rounded-pill {{ $config['clase'] }}"><i class="bi {{ $config['icono'] }} me-1"></i>{{ $etiqueta }}</span>
