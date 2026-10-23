(function () {
    'use strict';

    const movimientoReducido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const formatoBs = function (valor) {
        return 'Bs ' + Number(valor || 0).toLocaleString('es-BO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    window.FarmaSalud = {
        movimientoReducido: movimientoReducido,
        formatoBs: formatoBs,
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
})();
