(function () {
    'use strict';

    const TOTAL_GIROS = 5;
    const CLAVE_MEJOR = 'farmaSaludJuegoMejor';
    const reduceMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function iniciarFefo() {
        const juego = document.querySelector('[data-juego-fefo]');
        const feedback = document.getElementById('fefoFeedback');
        const reiniciar = document.getElementById('fefoReiniciar');

        if (!juego || !feedback || !reiniciar) {
            return;
        }

        const botones = Array.from(juego.querySelectorAll('.fefo-lote'));
        let resuelto = false;

        function responder(boton) {
            if (resuelto) {
                return;
            }

            const correcta = boton.dataset.correcta === '1';
            boton.classList.add(correcta ? 'fefo-lote--ok' : 'fefo-lote--error');

            if (!correcta) {
                feedback.className = 'fefo-feedback is-error';
                feedback.innerHTML = '<i class="bi bi-x-circle me-1"></i>Ese no es. El lote vencido se descarta y entre los vigentes sale primero el que vence antes.';
                return;
            }

            resuelto = true;
            feedback.className = 'fefo-feedback is-ok';
            feedback.innerHTML = '<i class="bi bi-check-circle me-1"></i>¡Correcto! El lote B vence antes que el A y sigue vigente. El lote C ya está vencido.';

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
                boton.classList.remove('fefo-lote--ok', 'fefo-lote--error');
            });

            feedback.className = 'fefo-feedback';
            feedback.innerHTML = '<i class="bi bi-question-circle me-1"></i>Toca un lote para responder.';
            reiniciar.classList.add('d-none');
        });
    }

    function leerCategorias() {
        const datos = document.getElementById('juegoCategorias');

        if (!datos) {
            return [];
        }

        try {
            const categorias = JSON.parse(datos.textContent);

            return Array.isArray(categorias) ? categorias : [];
        } catch (error) {
            return [];
        }
    }

    function leerMejor() {
        try {
            const valor = parseInt(window.localStorage.getItem(CLAVE_MEJOR), 10);

            return Number.isFinite(valor) && valor > 0 ? valor : 0;
        } catch (error) {
            return 0;
        }
    }

    function guardarMejor(valor) {
        try {
            window.localStorage.setItem(CLAVE_MEJOR, String(valor));
        } catch (error) {
            return;
        }
    }

    function iniciarRuleta() {
        const raiz = document.querySelector('[data-juego-ruleta]');

        if (!raiz) {
            return;
        }

        const ruleta = raiz.querySelector('[data-ruleta]');
        const botonGirar = raiz.querySelector('[data-girar]');
        const giroActual = raiz.querySelector('[data-giro-actual]');
        const puntaje = raiz.querySelector('[data-puntaje]');
        const estado = raiz.querySelector('[data-estado]');
        const panelPregunta = raiz.querySelector('[data-pregunta]');
        const chipCategoria = raiz.querySelector('[data-pregunta-categoria]');
        const textoPregunta = raiz.querySelector('[data-pregunta-texto]');
        const opciones = raiz.querySelector('[data-opciones]');
        const feedback = raiz.querySelector('[data-feedback]');
        const panelResultado = raiz.querySelector('[data-resultado]');
        const resultadoTitulo = raiz.querySelector('[data-resultado-titulo]');
        const resultadoTexto = raiz.querySelector('[data-resultado-texto]');
        const resultadoPuntaje = raiz.querySelector('[data-resultado-puntaje]');
        const recordNuevo = raiz.querySelector('[data-record]');
        const botonReiniciar = raiz.querySelector('[data-reiniciar]');

        if (!ruleta || !botonGirar || !giroActual || !puntaje || !estado
            || !panelPregunta || !chipCategoria || !textoPregunta
            || !opciones || !feedback || !panelResultado || !resultadoTitulo
            || !resultadoTexto || !resultadoPuntaje || !recordNuevo || !botonReiniciar) {
            return;
        }

        const categorias = leerCategorias();
        let giros = 0;
        let aciertos = 0;
        let rotacion = 0;
        let girando = false;
        let preguntaActiva = false;
        let mejor = leerMejor();

        function actualizarMejor() {
            document.querySelectorAll('[data-mejor]').forEach(function (nodo) {
                nodo.textContent = String(mejor);
            });
        }

        function actualizarTurno() {
            giroActual.textContent = String(giros);
        }

        function pintarRuleta() {
            if (categorias.length === 0) {
                ruleta.style.background = '#cbd5e1';
                botonGirar.disabled = true;
                botonGirar.textContent = 'Sin categorías disponibles';
                estado.textContent = 'Agrega medicamentos al catálogo para habilitar la ruleta.';
                return;
            }

            const paso = 360 / categorias.length;
            const inicio = -paso / 2;
            const paradas = categorias.map(function (categoria, indice) {
                const desde = Math.round(indice * paso * 100) / 100;
                const hasta = Math.round((indice + 1) * paso * 100) / 100;

                return (categoria.color || '#94a3b8') + ' ' + desde + 'deg ' + hasta + 'deg';
            }).join(', ');

            ruleta.style.background = 'conic-gradient(from ' + inicio + 'deg, ' + paradas + ')';
        }

        function ocultarResultado() {
            panelResultado.hidden = true;
            recordNuevo.hidden = true;
        }

        function mostrarPregunta(datos) {
            chipCategoria.textContent = datos.categoria.nombre;
            chipCategoria.style.background = datos.categoria.suave || '#e6f4f1';
            chipCategoria.style.color = datos.categoria.color || '#0f766e';
            chipCategoria.style.borderColor = datos.categoria.color || '#0f766e';
            textoPregunta.textContent = datos.pregunta;
            opciones.innerHTML = '';
            feedback.className = 'juego-feedback';
            feedback.textContent = '';
            panelPregunta.hidden = false;

            datos.opciones.forEach(function (opcion, indice) {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'juego-opcion';
                boton.textContent = opcion;
                boton.addEventListener('click', function () {
                    responder(boton, indice, datos);
                });
                opciones.appendChild(boton);
            });
        }

        function responder(boton, indice, datos) {
            if (!preguntaActiva) {
                return;
            }

            preguntaActiva = false;
            const acierto = indice === Number(datos.respuesta);
            const botones = Array.from(opciones.querySelectorAll('.juego-opcion'));

            botones.forEach(function (opcion, posicion) {
                opcion.disabled = true;

                if (posicion === Number(datos.respuesta)) {
                    opcion.classList.add('juego-opcion--correcta');
                }

                if (posicion === indice && !acierto) {
                    opcion.classList.add('juego-opcion--incorrecta');
                }
            });

            if (acierto) {
                aciertos += 1;
                puntaje.textContent = String(aciertos);
                feedback.className = 'juego-feedback is-ok';
                feedback.textContent = '¡Correcto! ' + datos.explicacion;
            } else {
                feedback.className = 'juego-feedback is-error';
                feedback.textContent = 'No era esa. ' + datos.explicacion;
            }

            if (giros >= TOTAL_GIROS) {
                estado.textContent = 'Partida terminada.';
                setTimeout(finalizar, reduceMotion ? 0 : 650);
                return;
            }

            botonGirar.disabled = false;
            estado.textContent = acierto
                ? '¡Bien! Puedes girar otra vez.'
                : 'Sigue intentando. Puedes girar otra vez.';
        }

        function finalizar() {
            const nuevoRecord = aciertos > mejor;

            if (nuevoRecord) {
                mejor = aciertos;
                guardarMejor(mejor);
                actualizarMejor();
            }

            resultadoPuntaje.textContent = String(aciertos);
            recordNuevo.hidden = !nuevoRecord || aciertos === 0;
            panelResultado.hidden = false;
            botonGirar.disabled = true;

            if (aciertos === TOTAL_GIROS) {
                resultadoTitulo.textContent = '¡Perfecto!';
                resultadoTexto.textContent = 'Conoces muy bien el catálogo de la farmacia.';
            } else if (aciertos >= 3) {
                resultadoTitulo.textContent = '¡Muy bien!';
                resultadoTexto.textContent = 'Ya dominas gran parte del catálogo. Un giro más y lo logras.';
            } else if (aciertos >= 1) {
                resultadoTitulo.textContent = 'Buen inicio';
                resultadoTexto.textContent = 'Cada giro enseña algo nuevo. Explora el catálogo y vuelve a intentarlo.';
            } else {
                resultadoTitulo.textContent = 'A seguir practicando';
                resultadoTexto.textContent = 'No te preocupes: revisa el catálogo y vuelve a jugar.';
            }
        }

        function reiniciarPartida() {
            giros = 0;
            aciertos = 0;
            girando = false;
            preguntaActiva = false;
            actualizarTurno();
            puntaje.textContent = '0';
            panelPregunta.hidden = true;
            ocultarResultado();
            feedback.className = 'juego-feedback';
            feedback.textContent = '';
            botonGirar.disabled = categorias.length === 0;
            botonGirar.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>Girar la ruleta';
            estado.textContent = 'Toca el botón para comenzar la partida.';
        }

        function cargarPregunta(categoria) {
            estado.textContent = 'Buscando una pregunta para ' + categoria.nombre + '...';

            const url = raiz.dataset.url + '?categoria_id=' + encodeURIComponent(categoria.id);

            fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin'
            })
                .then(function (respuesta) {
                    if (!respuesta.ok) {
                        throw new Error('No se pudo cargar la pregunta');
                    }

                    return respuesta.json();
                })
                .then(function (datos) {
                    giros += 1;
                    girando = false;
                    preguntaActiva = true;
                    actualizarTurno();
                    estado.textContent = 'La ruleta se detuvo en ' + categoria.nombre + '.';
                    mostrarPregunta(datos);
                })
                .catch(function () {
                    girando = false;
                    botonGirar.disabled = false;
                    actualizarTurno();
                    estado.textContent = 'No se pudo cargar la pregunta. Intenta girar otra vez.';
                });
        }

        function girarRuleta() {
            if (girando || preguntaActiva || giros >= TOTAL_GIROS || categorias.length === 0) {
                return;
            }

            girando = true;
            botonGirar.disabled = true;
            panelPregunta.hidden = true;
            ocultarResultado();
            feedback.className = 'juego-feedback';
            feedback.textContent = '';

            const indice = Math.floor(Math.random() * categorias.length);
            const paso = 360 / categorias.length;
            const centro = indice * paso;
            const actual = ((rotacion % 360) + 360) % 360;
            const deseado = ((-centro % 360) + 360) % 360;
            const jitter = (Math.random() - 0.5) * paso * 0.56;
            let delta = ((deseado - actual + 360) % 360) + (360 * 5) + jitter;

            rotacion += delta;
            ruleta.style.transform = 'rotate(' + rotacion + 'deg)';
            giroActual.textContent = String(giros + 1);
            estado.textContent = 'La ruleta está girando...';

            const duracion = reduceMotion ? 0 : 4150;

            setTimeout(function () {
                cargarPregunta(categorias[indice]);
            }, duracion + 80);
        }

        actualizarMejor();
        actualizarTurno();
        pintarRuleta();
        botonGirar.addEventListener('click', girarRuleta);
        botonReiniciar.addEventListener('click', reiniciarPartida);
    }

    iniciarFefo();
    iniciarRuleta();
})();
