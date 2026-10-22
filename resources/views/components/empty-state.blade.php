@props(['icono' => 'bi-inbox', 'titulo' => 'Sin registros', 'mensaje' => 'Aún no hay información para mostrar.'])

<div class="empty-state">
    <i class="bi {{ $icono }} d-block mb-2"></i>
    <h3 class="h6 fw-semibold text-secondary">{{ $titulo }}</h3>
    <p class="small mb-0">{{ $mensaje }}</p>
</div>
