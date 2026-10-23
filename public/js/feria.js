(function () {
    'use strict';

    const movimientoReducido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const formatoBs = function (valor) {
        return 'Bs ' + Number(valor || 0).toLocaleString('es-BO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    const formatoNumero = function (valor) {
        return Math.round(Number(valor || 0)).toLocaleString('es-BO');
    };

    window.FarmaSalud = {
        movimientoReducido: movimientoReducido,
        formatoBs: formatoBs,
        formatoNumero: formatoNumero,
        animar: function (elemento, desde, hasta, duracion, formato) {
            if (!elemento) {
                return;
            }

            if (movimientoReducido) {
                elemento.textContent = formato(hasta);
                return;
            }

            const inicio = performance.now();
            const diferencia = hasta - desde;

            function paso(ahora) {
                const avance = Math.min(1, (ahora - inicio) / (duracion || 700));
                const suavizado = 1 - Math.pow(1 - avance, 3);
                elemento.textContent = formato(desde + diferencia * suavizado);

                if (avance < 1) {
                    requestAnimationFrame(paso);
                }
            }

            requestAnimationFrame(paso);
        },
    };

    function formatoSegun(elemento) {
        return elemento.dataset.formato === 'bs' ? formatoBs : formatoNumero;
    }

    function animarContadores(raiz) {
        (raiz || document).querySelectorAll('[data-contador]').forEach(function (elemento) {
            const hasta = Number(elemento.dataset.valor) || 0;
            window.FarmaSalud.animar(elemento, 0, hasta, 850, formatoSegun(elemento));
        });
    }

    window.FarmaSalud.animarContadores = animarContadores;

    const clavePresentacion = 'farmasalud.presentacion';
    let presentacion = false;

    try {
        presentacion = localStorage.getItem(clavePresentacion) === '1';
    } catch (error) {
        presentacion = false;
    }

    function aplicarPresentacion(activo, guardar) {
        presentacion = activo;
        document.body.classList.toggle('modo-presentacion', activo);

        document.querySelectorAll('[data-presentacion-toggle]').forEach(function (boton) {
            boton.classList.toggle('activo', activo);
            boton.setAttribute('aria-pressed', activo ? 'true' : 'false');
            boton.setAttribute('title', activo ? 'Salir del modo presentación' : 'Activar modo presentación');

            const texto = boton.querySelector('[data-presentacion-texto]');
            if (texto) {
                texto.textContent = activo ? 'Salir' : 'Presentación';
            }
        });

        if (guardar) {
            try {
                localStorage.setItem(clavePresentacion, activo ? '1' : '0');
            } catch (error) {
                // Sin persistencia disponible.
            }
        }
    }

    aplicarPresentacion(presentacion, false);

    document.addEventListener('click', function (evento) {
        const boton = evento.target.closest('[data-presentacion-toggle]');

        if (boton) {
            evento.preventDefault();
            aplicarPresentacion(!presentacion, true);
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && presentacion && !document.querySelector('.modal.show')) {
            aplicarPresentacion(false, true);
        }
    });

    animarContadores();
})();
