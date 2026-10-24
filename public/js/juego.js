(function () {
    'use strict';

    const TOTAL_GIROS = 5;
    const SEGUNDOS_POR_PREGUNTA = 20;
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

    function esperar(ms) {
        return new Promise(function (resolver) {
            setTimeout(resolver, reduceMotion ? 0 : ms);
        });
    }

    function animar(elemento, cuadros, opciones) {
        if (reduceMotion || !elemento.animate) {
            return Promise.resolve();
        }

        return elemento.animate(cuadros, opciones).finished.catch(function () {});
    }

    function iniciarRuleta() {
        const raiz = document.querySelector('[data-juego-ruleta]');

        if (!raiz) {
            return;
        }

        const marco = raiz.querySelector('.ruleta-marco');
        const ruleta = raiz.querySelector('[data-ruleta]');
        const botonGirar = raiz.querySelector('[data-girar]');
        const giroActual = raiz.querySelector('[data-giro-actual]');
        const puntaje = raiz.querySelector('[data-puntaje]');
        const estado = raiz.querySelector('[data-estado]');
        const telon = raiz.querySelector('[data-telon]');
        const banner = raiz.querySelector('[data-banner]');
        const bannerIcono = raiz.querySelector('[data-banner-icono]');
        const bannerTexto = raiz.querySelector('[data-banner-texto]');
        const panelPregunta = raiz.querySelector('[data-pregunta]');
        const cabecera = raiz.querySelector('[data-pregunta-cabecera]');
        const numeroPregunta = raiz.querySelector('[data-pregunta-numero]');
        const barraTiempo = raiz.querySelector('[data-tiempo-barra]');
        const chipCategoria = raiz.querySelector('[data-pregunta-categoria]');
        const textoPregunta = raiz.querySelector('[data-pregunta-texto]');
        const opciones = raiz.querySelector('[data-opciones]');
        const feedback = raiz.querySelector('[data-feedback]');
        const botonSiguiente = raiz.querySelector('[data-siguiente]');
        const panelResultado = raiz.querySelector('[data-resultado]');
        const resultadoTitulo = raiz.querySelector('[data-resultado-titulo]');
        const resultadoTexto = raiz.querySelector('[data-resultado-texto]');
        const resultadoPuntaje = raiz.querySelector('[data-resultado-puntaje]');
        const recordNuevo = raiz.querySelector('[data-record]');
        const botonReiniciar = raiz.querySelector('[data-reiniciar]');

        if (!marco || !ruleta || !botonGirar || !giroActual || !puntaje || !estado
            || !telon || !banner || !bannerIcono || !bannerTexto || !panelPregunta
            || !cabecera || !numeroPregunta || !barraTiempo || !chipCategoria
            || !textoPregunta || !opciones || !feedback || !botonSiguiente
            || !panelResultado || !resultadoTitulo || !resultadoTexto
            || !resultadoPuntaje || !recordNuevo || !botonReiniciar) {
            return;
        }

        // El telón (fondo oscuro con la ruleta y la pregunta) vive a nivel de página
        // para poder cubrir toda la pantalla.
        document.body.appendChild(telon);

        const categorias = leerCategorias();
        let giros = 0;
        let aciertos = 0;
        let rotacion = 0;
        let girando = false;
        let preguntaActiva = false;
        let temporizador = null;
        let mejor = leerMejor();
        let vistas = [];
        let marcador = null;
        let escala = 1;

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

        /* ---------- Escena: la ruleta viaja al centro de la pantalla ---------- */

        function entrarEscena() {
            const origen = marco.getBoundingClientRect();
            const ancho = origen.width;
            const alto = origen.height;

            marcador = document.createElement('div');
            marcador.className = 'ruleta-marcador';
            marcador.style.width = ancho + 'px';
            marcador.style.height = alto + 'px';
            marco.parentNode.insertBefore(marcador, marco);

            const disponible = Math.min(window.innerWidth * 0.9, window.innerHeight * 0.72);
            escala = Math.max(1, Math.min(1.7, disponible / Math.max(ancho, alto)));

            marco.classList.add('is-escena');
            marco.style.width = ancho + 'px';
            marco.style.height = alto + 'px';
            marco.style.marginLeft = (-ancho / 2) + 'px';
            marco.style.marginTop = (-alto / 2) + 'px';
            telon.appendChild(marco);

            telon.hidden = false;
            // fuerza el reflow para que el fondo oscuro aparezca con transición
            void telon.offsetWidth;
            telon.classList.add('is-visible');

            const dx = origen.left + ancho / 2 - window.innerWidth / 2;
            const dy = origen.top + alto / 2 - window.innerHeight / 2;

            marco.style.transform = 'scale(' + escala + ')';

            return animar(marco, [
                { transform: 'translate(' + dx + 'px, ' + dy + 'px) scale(1)' },
                { transform: 'scale(' + escala + ')' }
            ], { duration: 650, easing: 'cubic-bezier(.2,.9,.25,1.1)' });
        }

        function salirEscena() {
            if (!marcador) {
                return Promise.resolve();
            }

            const destino = marcador.getBoundingClientRect();
            const dx = destino.left + destino.width / 2 - window.innerWidth / 2;
            const dy = destino.top + destino.height / 2 - window.innerHeight / 2;

            return animar(marco, [
                { transform: 'scale(' + escala + ')' },
                { transform: 'translate(' + dx + 'px, ' + dy + 'px) scale(1)' }
            ], { duration: 480, easing: 'cubic-bezier(.4,0,.2,1)' }).then(function () {
                marco.classList.remove('is-escena');
                marco.removeAttribute('style');
                marcador.parentNode.insertBefore(marco, marcador);
                marcador.remove();
                marcador = null;
            });
        }

        function cerrarTelon() {
            telon.classList.remove('is-visible');
            banner.hidden = true;
            panelPregunta.hidden = true;

            return esperar(320).then(function () {
                telon.hidden = true;
            });
        }

        function mostrarBanner(categoria) {
            bannerTexto.textContent = categoria.nombre;
            bannerIcono.className = 'bi ' + (categoria.icono || 'bi-capsule');
            banner.style.setProperty('--banner-color', categoria.color || '#0f766e');
            banner.hidden = false;

            return animar(banner, [
                { transform: 'translate(-50%, 24px) scale(.5)', opacity: 0 },
                { transform: 'translate(-50%, 0) scale(1.08)', opacity: 1, offset: .65 },
                { transform: 'translate(-50%, 0) scale(1)', opacity: 1 }
            ], { duration: 520, easing: 'ease-out', fill: 'forwards' });
        }

        /* ---------- Pregunta ---------- */

        function detenerTiempo() {
            if (temporizador) {
                clearTimeout(temporizador);
                temporizador = null;
            }
        }

        function iniciarTiempo(datos) {
            detenerTiempo();
            barraTiempo.classList.remove('is-urgente');
            barraTiempo.style.transition = 'none';
            barraTiempo.style.width = '100%';
            void barraTiempo.offsetWidth;
            barraTiempo.style.transition = 'width ' + SEGUNDOS_POR_PREGUNTA + 's linear, background-color .3s';
            barraTiempo.style.width = '0%';

            setTimeout(function () {
                if (preguntaActiva) {
                    barraTiempo.classList.add('is-urgente');
                }
            }, (SEGUNDOS_POR_PREGUNTA - 6) * 1000);

            temporizador = setTimeout(function () {
                responder(null, -1, datos);
            }, SEGUNDOS_POR_PREGUNTA * 1000);
        }

        function mostrarPregunta(datos) {
            const color = datos.categoria.color || '#0f766e';

            chipCategoria.innerHTML = '';
            const icono = document.createElement('i');
            icono.className = 'bi ' + (datos.categoria.icono || 'bi-capsule');
            chipCategoria.appendChild(icono);
            chipCategoria.appendChild(document.createTextNode(' ' + datos.categoria.nombre));
            cabecera.style.background = color;
            panelPregunta.style.setProperty('--pregunta-color', color);
            numeroPregunta.textContent = String(giros);
            textoPregunta.textContent = datos.pregunta;
            opciones.innerHTML = '';
            feedback.className = 'juego-feedback';
            feedback.textContent = '';
            botonSiguiente.hidden = true;
            banner.hidden = true;
            panelPregunta.hidden = false;

            const letras = ['A', 'B', 'C', 'D'];

            datos.opciones.forEach(function (opcion, indice) {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'juego-opcion';
                boton.dataset.letra = letras[indice] || String(indice + 1);
                boton.style.animationDelay = (120 + indice * 70) + 'ms';
                boton.textContent = opcion;
                boton.addEventListener('click', function () {
                    responder(boton, indice, datos);
                });
                opciones.appendChild(boton);
            });

            preguntaActiva = true;
            iniciarTiempo(datos);

            const primero = opciones.querySelector('.juego-opcion');
            if (primero) {
                primero.focus({ preventScroll: true });
            }
        }

        function responder(boton, indice, datos) {
            if (!preguntaActiva) {
                return;
            }

            preguntaActiva = false;
            detenerTiempo();
            const acierto = indice === Number(datos.respuesta);
            const botones = Array.from(opciones.querySelectorAll('.juego-opcion'));

            // congela la barra de tiempo donde estaba
            const ancho = barraTiempo.getBoundingClientRect().width;
            barraTiempo.style.transition = 'none';
            barraTiempo.style.width = ancho + 'px';

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
            } else if (indice === -1) {
                feedback.className = 'juego-feedback is-error';
                feedback.textContent = '¡Se acabó el tiempo! ' + datos.explicacion;
            } else {
                feedback.className = 'juego-feedback is-error';
                feedback.textContent = 'No era esa. ' + datos.explicacion;
            }

            botonSiguiente.hidden = false;
            botonSiguiente.innerHTML = giros >= TOTAL_GIROS
                ? 'Ver mi resultado <i class="bi bi-trophy ms-1"></i>'
                : 'Continuar <i class="bi bi-arrow-right ms-1"></i>';
            botonSiguiente.focus({ preventScroll: true });
        }

        function continuar() {
            if (preguntaActiva) {
                return;
            }

            cerrarTelon().then(function () {
                if (giros >= TOTAL_GIROS) {
                    estado.textContent = 'Partida terminada.';
                    finalizar();
                    return;
                }

                botonGirar.disabled = false;
                estado.textContent = 'Puedes girar otra vez.';
                botonGirar.focus({ preventScroll: true });
            });
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
                resultadoTexto.textContent = 'Sabes mucho sobre salud y medicamentos. ¡Felicitaciones!';
            } else if (aciertos >= 3) {
                resultadoTitulo.textContent = '¡Muy bien!';
                resultadoTexto.textContent = 'Ya sabes bastante. Un giro más y lo logras.';
            } else if (aciertos >= 1) {
                resultadoTitulo.textContent = 'Buen inicio';
                resultadoTexto.textContent = 'Cada giro enseña algo nuevo. ¡Vuelve a intentarlo!';
            } else {
                resultadoTitulo.textContent = 'A seguir practicando';
                resultadoTexto.textContent = 'No te preocupes: se aprende jugando. ¡Inténtalo otra vez!';
            }
        }

        function reiniciarPartida() {
            giros = 0;
            aciertos = 0;
            girando = false;
            preguntaActiva = false;
            vistas = [];
            detenerTiempo();
            actualizarTurno();
            puntaje.textContent = '0';
            ocultarResultado();
            botonGirar.disabled = categorias.length === 0;
            botonGirar.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>Girar la ruleta';
            estado.textContent = 'Toca el botón para comenzar la partida.';
        }

        function cargarPregunta(categoria) {
            estado.textContent = 'Buscando una pregunta de ' + categoria.nombre + '...';

            const url = raiz.dataset.url
                + '?categoria_id=' + encodeURIComponent(categoria.id)
                + '&vistas=' + encodeURIComponent(vistas.join(','));

            return fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin'
            }).then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error('No se pudo cargar la pregunta');
                }

                return respuesta.json();
            });
        }

        function girarRuleta() {
            if (girando || preguntaActiva || giros >= TOTAL_GIROS || categorias.length === 0) {
                return;
            }

            girando = true;
            botonGirar.disabled = true;
            ocultarResultado();
            feedback.className = 'juego-feedback';
            feedback.textContent = '';

            const indice = Math.floor(Math.random() * categorias.length);
            const categoria = categorias[indice];
            const paso = 360 / categorias.length;
            const centro = indice * paso;
            const actual = ((rotacion % 360) + 360) % 360;
            const deseado = ((-centro % 360) + 360) % 360;
            const jitter = (Math.random() - 0.5) * paso * 0.56;
            const delta = ((deseado - actual + 360) % 360) + (360 * 5) + jitter;

            estado.textContent = 'La ruleta está girando...';

            // Empieza a buscar la pregunta mientras gira para no hacer esperar al jugador.
            const promesaPregunta = cargarPregunta(categoria).then(function (datos) {
                return { datos: datos };
            }, function () {
                return { error: true };
            });

            entrarEscena()
                .then(function () {
                    rotacion += delta;
                    ruleta.style.transform = 'rotate(' + rotacion + 'deg)';
                    giroActual.textContent = String(giros + 1);

                    return esperar(4150 + 80);
                })
                .then(function () {
                    return Promise.all([promesaPregunta, mostrarBanner(categoria)]);
                })
                .then(function (resultado) {
                    return esperar(900).then(function () {
                        return resultado[0];
                    });
                })
                .then(function (resultado) {
                    return salirEscena().then(function () {
                        return resultado;
                    });
                })
                .then(function (resultado) {
                    girando = false;

                    if (resultado.error) {
                        return cerrarTelon().then(function () {
                            botonGirar.disabled = false;
                            actualizarTurno();
                            estado.textContent = 'No se pudo cargar la pregunta. Intenta girar otra vez.';
                        });
                    }

                    giros += 1;
                    actualizarTurno();
                    vistas.push(resultado.datos.id);
                    estado.textContent = 'La ruleta se detuvo en ' + categoria.nombre + '.';
                    mostrarPregunta(resultado.datos);
                });
        }

        document.addEventListener('keydown', function (evento) {
            if (!preguntaActiva || telon.hidden) {
                return;
            }

            const numero = parseInt(evento.key, 10);
            const botones = opciones.querySelectorAll('.juego-opcion');

            if (numero >= 1 && numero <= botones.length) {
                botones[numero - 1].click();
            }
        });

        actualizarMejor();
        actualizarTurno();
        pintarRuleta();
        botonGirar.addEventListener('click', girarRuleta);
        botonSiguiente.addEventListener('click', continuar);
        botonReiniciar.addEventListener('click', reiniciarPartida);
    }

    iniciarFefo();
    iniciarRuleta();
})();
