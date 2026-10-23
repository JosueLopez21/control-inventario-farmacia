(function () {
    'use strict';

    var overlay = document.querySelector('[data-animacion-exito]');
    if (!overlay) {
        return;
    }

    var panel = overlay.querySelector('.animacion-exito__panel');
    var principal = overlay.querySelector('[data-animacion-principal]');
    var cerrarBoton = overlay.querySelector('[data-cerrar-animacion]');
    var focoAnterior = document.activeElement;
    var selectorFoco = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    document.body.classList.add('sin-scroll');

    function enfocarPrincipal() {
        if (principal && typeof principal.focus === 'function') {
            principal.focus();
        }
    }

    function cerrar() {
        document.removeEventListener('keydown', manejarTeclado);
        overlay.remove();
        document.body.classList.remove('sin-scroll');

        if (focoAnterior && typeof focoAnterior.focus === 'function') {
            focoAnterior.focus();
        }
    }

    function manejarTeclado(evento) {
        if (evento.key === 'Escape') {
            evento.preventDefault();
            cerrar();
            return;
        }

        if (evento.key !== 'Tab' || !panel) {
            return;
        }

        var enfocables = Array.prototype.slice.call(panel.querySelectorAll(selectorFoco)).filter(function (elemento) {
            return elemento.offsetParent !== null;
        });

        if (enfocables.length === 0) {
            return;
        }

        var primero = enfocables[0];
        var ultimo = enfocables[enfocables.length - 1];

        if (evento.shiftKey && document.activeElement === primero) {
            evento.preventDefault();
            ultimo.focus();
        } else if (!evento.shiftKey && document.activeElement === ultimo) {
            evento.preventDefault();
            primero.focus();
        }
    }

    overlay.addEventListener('click', function (evento) {
        if (!evento.target.closest('.animacion-exito__panel')) {
            cerrar();
        }
    });

    if (cerrarBoton) {
        cerrarBoton.addEventListener('click', cerrar);
    }

    document.addEventListener('keydown', manejarTeclado);
    window.requestAnimationFrame(enfocarPrincipal);
})();
