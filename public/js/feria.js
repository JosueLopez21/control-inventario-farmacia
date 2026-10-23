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

    function establecerAyuda(boton, abierto) {
        const panel = document.getElementById(boton.getAttribute('aria-controls'));

        if (!panel) {
            return;
        }

        boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        panel.hidden = !abierto;

        if (!abierto) {
            return;
        }

        const margen = 12;
        const ancho = Math.min(320, window.innerWidth - margen * 2);
        panel.style.width = ancho + 'px';

        const posicionBoton = boton.getBoundingClientRect();
        const altoPanel = panel.offsetHeight;
        let izquierda = posicionBoton.left + (posicionBoton.width / 2) - (ancho / 2);
        let arriba = posicionBoton.bottom + 8;

        izquierda = Math.max(margen, Math.min(izquierda, window.innerWidth - ancho - margen));

        if (arriba + altoPanel > window.innerHeight - margen) {
            arriba = posicionBoton.top - altoPanel - 8;
        }

        arriba = Math.max(margen, arriba);
        panel.style.left = izquierda + 'px';
        panel.style.top = arriba + 'px';
    }

    function cerrarAyudas() {
        document.querySelectorAll('[data-ayuda-fefo]').forEach(function (boton) {
            boton.dataset.fijado = '0';
            establecerAyuda(boton, false);
        });
    }

    document.querySelectorAll('[data-ayuda-fefo]').forEach(function (boton) {
        const contenedor = boton.closest('.ayuda-fefo');

        boton.addEventListener('click', function (evento) {
            evento.preventDefault();
            evento.stopPropagation();

            const abierto = boton.getAttribute('aria-expanded') === 'true';
            cerrarAyudas();
            boton.dataset.fijado = abierto ? '0' : '1';

            if (!abierto) {
                establecerAyuda(boton, true);
            }
        });

        if (contenedor && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            contenedor.addEventListener('mouseenter', function () {
                establecerAyuda(boton, true);
            });

            contenedor.addEventListener('mouseleave', function () {
                if (boton.dataset.fijado !== '1') {
                    establecerAyuda(boton, false);
                }
            });
        }
    });

    document.addEventListener('click', function (evento) {
        if (!evento.target.closest('.ayuda-fefo')) {
            cerrarAyudas();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarAyudas();
        }
    });

    window.addEventListener('resize', cerrarAyudas);
    window.addEventListener('scroll', cerrarAyudas, true);

    animarContadores();
})();
