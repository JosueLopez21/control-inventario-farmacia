(function () {
    'use strict';

    const resumenes = document.querySelectorAll('[data-alerta-resumen]');

    if (!resumenes.length) {
        return;
    }

    function activar(destino) {
        resumenes.forEach(function (boton) {
            boton.classList.toggle('activo', boton.dataset.bsTarget === destino);
        });
    }

    resumenes.forEach(function (boton) {
        boton.addEventListener('click', function () {
            activar(boton.dataset.bsTarget);
        });
    });

    document.addEventListener('shown.bs.tab', function (evento) {
        if (evento.target.dataset.bsTarget) {
            activar(evento.target.dataset.bsTarget);
        }
    });
})();
