@props(['titulo', 'valor', 'icono' => 'bi-graph-up', 'color' => 'verde', 'detalle' => null, 'enlace' => null])

<div class="metric-card metric-{{ $color }}">
    <div class="metric-label">{{ $titulo }}</div>
    <div class="metric-value">{{ $valor }}</div>
    @if ($detalle)
        <div class="small mt-1" style="opacity: .85;">{{ $detalle }}</div>
    @endif
    <i class="bi {{ $icono }} metric-icon"></i>
    @if ($enlace)
        <a href="{{ $enlace }}" class="stretched-link" aria-label="{{ $titulo }}"></a>
    @endif
</div>
