@props(['accion', 'nombre' => 'este registro', 'texto' => null, 'icono' => 'bi-trash3'])

<button type="button"
        class="btn btn-sm btn-outline-danger"
        data-eliminar="{{ $accion }}"
        data-nombre="{{ $nombre }}"
        title="Eliminar">
    <i class="bi {{ $icono }}"></i>@if ($texto) {{ $texto }}@endif
</button>
