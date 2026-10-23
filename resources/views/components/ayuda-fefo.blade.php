@props(['texto' => '', 'sigla' => false, 'soloIcono' => false])
@php($idAyuda = 'ayuda-fefo-'.uniqid())

<span class="ayuda-fefo">
    @if ($texto !== '' && ! $soloIcono)
        <span>{{ $texto }}</span>
    @endif
    @if ($sigla)
        <span class="ayuda-fefo__sigla">(FEFO)</span>
    @endif
    <button type="button"
            class="ayuda-fefo__boton"
            data-ayuda-fefo
            aria-label="Explicación de la sigla FEFO"
            aria-expanded="false"
            aria-controls="{{ $idAyuda }}">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
    </button>
    <span class="ayuda-fefo__panel" id="{{ $idAyuda }}" role="note" hidden>
        <strong>FEFO: primero en vencer, primero en salir.</strong>
        El sistema vende primero las unidades que vencen antes para que no se pierdan.
    </span>
</span>
