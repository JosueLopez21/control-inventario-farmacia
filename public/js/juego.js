(function () {
    'use strict';

    const juego = document.querySelector('[data-juego]');
    const feedback = document.getElementById('retoFeedback');
    const reiniciar = document.getElementById('retoReiniciar');

    if (!juego || !feedback || !reiniciar) {
        return;
    }

    const botones = Array.from(juego.querySelectorAll('.reto-lote'));
    let resuelto = false;

    function responder(boton) {
        if (resuelto) {
            return;
        }

        const correcta = boton.dataset.correcta === '1';
        boton.classList.add(correcta ? 'reto-lote--correcto' : 'reto-lote--incorrecto');

        if (!correcta) {
            feedback.className = 'reto-feedback reto-feedback--error';
            feedback.innerHTML = '<i class="bi bi-x-circle"></i><span>Ese no es. Recuerda: FEFO elige el lote vigente que vence primero, nunca uno vencido.</span>';
            return;
        }

        resuelto = true;
        feedback.className = 'reto-feedback reto-feedback--ok';
        feedback.innerHTML = '<i class="bi bi-check-circle"></i><span>¡Correcto! El lote B vence antes y está vigente. El lote C está bloqueado por vencido.</span>';

        botones.forEach(function (otro) {
            otro.disabled = true;
        });

        reiniciar.classList.remove('d-none');
    }

    botones.forEach(function (boton) {
        boton.addEventListener('click', function () {
            responder(boton);
        });
    });

    reiniciar.addEventListener('click', function () {
        resuelto = false;
        botones.forEach(function (boton) {
            boton.disabled = false;
            boton.classList.remove('reto-lote--correcto', 'reto-lote--incorrecto');
        });

        feedback.className = 'reto-feedback';
        feedback.innerHTML = '<i class="bi bi-question-circle"></i><span>Toca un lote para responder.</span>';
        reiniciar.classList.add('d-none');
    });
})();
