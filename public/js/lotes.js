(function () {
    'use strict';

    var contenedor = document.querySelector('[data-selector-lote]');
    if (!contenedor) {
        return;
    }

    var nativo = contenedor.querySelector('[data-selector-nativo]');
    var input = contenedor.querySelector('[data-selector-input]');
    var resultados = contenedor.querySelector('[data-selector-resultados]');
    var limpiar = contenedor.querySelector('[data-selector-limpiar]');
    var elegido = contenedor.querySelector('[data-selector-elegido]');
    var elegidoNombre = contenedor.querySelector('[data-selector-elegido-nombre]');
    var elegidoDetalle = contenedor.querySelector('[data-selector-elegido-detalle]');
    var cambiar = contenedor.querySelector('[data-selector-cambiar]');
    var estado = contenedor.querySelector('[data-selector-estado]');
    var etiqueta = document.getElementById('etiquetaMedicamento');

    if (!nativo || !input || !resultados) {
        return;
    }

    var temporizador = null;
    var peticion = null;
    var opciones = [];
    var indiceActivo = -1;
    var evitarApertura = false;

    contenedor.classList.add('selector-lote--activo');

    function enfocarBuscador() {
        evitarApertura = true;
        input.focus();
        evitarApertura = false;
    }

    function detalleMedicamento(medicamento) {
        return [
            medicamento.principio_activo,
            medicamento.presentacion,
            medicamento.laboratorio && medicamento.laboratorio !== 'Sin laboratorio' ? medicamento.laboratorio : null
        ].filter(function (valor) { return Boolean(valor); }).join(' · ');
    }

    function mensaje(texto) {
        var elemento = document.createElement('div');
        elemento.className = 'selector-lote__vacio';
        elemento.textContent = texto;
        return elemento;
    }

    function abrir() {
        resultados.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function cerrar() {
        window.clearTimeout(temporizador);

        if (peticion) {
            peticion.abort();
            peticion = null;
        }

        resultados.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        marcar(-1, false);
    }

    function marcar(indice, desplazar) {
        opciones.forEach(function (opcion, posicion) {
            var activa = posicion === indice;
            opcion.boton.classList.toggle('selector-lote__opcion--activa', activa);
            opcion.boton.setAttribute('aria-selected', activa ? 'true' : 'false');
        });

        indiceActivo = indice;

        if (indice >= 0 && opciones[indice]) {
            input.setAttribute('aria-activedescendant', opciones[indice].boton.id);
            if (desplazar) {
                opciones[indice].boton.scrollIntoView({ block: 'nearest' });
            }
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    }

    function crearOpcion(medicamento, indice) {
        var boton = document.createElement('button');
        boton.type = 'button';
        boton.id = 'selector-lote-opcion-' + medicamento.id;
        boton.className = 'selector-lote__opcion';
        boton.setAttribute('role', 'option');
        boton.setAttribute('aria-selected', 'false');
        boton.tabIndex = -1;

        var icono = document.createElement('span');
        icono.className = 'selector-lote__opcion-icono';
        var iconoBi = document.createElement('i');
        iconoBi.className = 'bi bi-capsule';
        iconoBi.setAttribute('aria-hidden', 'true');
        icono.appendChild(iconoBi);

        var texto = document.createElement('span');
        texto.className = 'selector-lote__opcion-texto';

        var titulo = document.createElement('span');
        titulo.className = 'selector-lote__opcion-titulo';
        var nombre = document.createElement('span');
        nombre.textContent = medicamento.nombre_comercial;
        titulo.appendChild(nombre);

        if ((medicamento.stock || 0) <= 0) {
            var agotado = document.createElement('span');
            agotado.className = 'selector-lote__opcion-badge';
            agotado.textContent = 'Agotado';
            titulo.appendChild(agotado);
        }

        var metaTexto = detalleMedicamento(medicamento);
        var meta = document.createElement('span');
        meta.className = 'selector-lote__opcion-meta';
        meta.textContent = metaTexto;
        meta.hidden = metaTexto === '';

        texto.appendChild(titulo);
        texto.appendChild(meta);
        boton.appendChild(icono);
        boton.appendChild(texto);

        boton.addEventListener('click', function () {
            seleccionar(medicamento);
        });

        boton.addEventListener('mouseenter', function () {
            marcar(indice, false);
        });

        return boton;
    }

    function actualizarElegido(medicamento) {
        if (!elegido || !elegidoNombre || !elegidoDetalle) {
            return;
        }

        elegidoNombre.textContent = medicamento.nombre_comercial;
        elegidoDetalle.textContent = detalleMedicamento(medicamento);
        elegido.hidden = false;
    }

    function seleccionar(medicamento) {
        window.clearTimeout(temporizador);
        nativo.value = String(medicamento.id);
        nativo.dispatchEvent(new Event('change', { bubbles: true }));

        actualizarElegido(medicamento);
        input.value = '';
        if (limpiar) {
            limpiar.hidden = true;
        }
        cerrar();

        if (estado) {
            estado.textContent = 'Medicamento seleccionado: ' + medicamento.nombre_comercial;
        }

        enfocarBuscador();
    }

    function mostrarResultados(medicamentos) {
        opciones = [];
        indiceActivo = -1;

        if (medicamentos.length === 0) {
            resultados.replaceChildren(mensaje('No encontramos medicamentos con esa búsqueda.'));
            abrir();
            return;
        }

        var fragmento = document.createDocumentFragment();

        medicamentos.forEach(function (medicamento, indice) {
            var boton = crearOpcion(medicamento, indice);
            opciones.push({ medicamento: medicamento, boton: boton });
            fragmento.appendChild(boton);
        });

        resultados.replaceChildren(fragmento);
        abrir();
    }

    function buscar(termino) {
        window.clearTimeout(temporizador);

        if (peticion) {
            peticion.abort();
            peticion = null;
        }

        opciones = [];
        marcar(-1, false);

        if (termino.length === 1) {
            resultados.replaceChildren(mensaje('Escribe al menos 2 letras para buscar.'));
            abrir();
            return;
        }

        var url = new URL(contenedor.dataset.selectorUrl, window.location.origin);
        url.searchParams.set('incluir_agotados', '1');
        url.searchParams.set('limit', '12');
        if (termino !== '') {
            url.searchParams.set('q', termino);
        }

        resultados.replaceChildren(mensaje('Buscando medicamentos...'));
        abrir();

        var controlador = new AbortController();
        peticion = controlador;

        fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: controlador.signal
        })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error('Error al buscar medicamentos');
                }
                return respuesta.json();
            })
            .then(function (datos) {
                if (peticion !== controlador) {
                    return;
                }
                mostrarResultados(Array.isArray(datos.resultados) ? datos.resultados : []);
            })
            .catch(function (error) {
                if (error.name === 'AbortError' || peticion !== controlador) {
                    return;
                }
                opciones = [];
                resultados.replaceChildren(mensaje('No se pudo buscar. Intenta de nuevo.'));
                abrir();
            });
    }

    input.addEventListener('focus', function () {
        if (!evitarApertura && resultados.hidden) {
            buscar(input.value.trim());
        }
    });

    input.addEventListener('input', function () {
        var termino = input.value.trim();
        if (limpiar) {
            limpiar.hidden = termino === '';
        }

        window.clearTimeout(temporizador);
        temporizador = window.setTimeout(function () {
            buscar(termino);
        }, 180);
    });

    input.addEventListener('keydown', function (evento) {
        if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
            evento.preventDefault();

            if (resultados.hidden) {
                buscar(input.value.trim());
                return;
            }

            if (opciones.length === 0) {
                return;
            }

            if (evento.key === 'ArrowDown') {
                marcar(indiceActivo < 0 ? 0 : (indiceActivo + 1) % opciones.length, true);
            } else {
                marcar(indiceActivo <= 0 ? opciones.length - 1 : indiceActivo - 1, true);
            }

            return;
        }

        if (evento.key === 'Enter') {
            if (resultados.hidden) {
                return;
            }

            evento.preventDefault();

            if (opciones.length === 0) {
                return;
            }

            var indice = indiceActivo >= 0 ? indiceActivo : 0;
            seleccionar(opciones[indice].medicamento);
            return;
        }

        if (evento.key === 'Escape') {
            cerrar();
        }
    });

    if (limpiar) {
        limpiar.addEventListener('click', function () {
            input.value = '';
            limpiar.hidden = true;
            enfocarBuscador();
            buscar('');
        });
    }

    if (cambiar) {
        cambiar.addEventListener('click', function () {
            enfocarBuscador();
            input.select();
            buscar(input.value.trim());
        });
    }

    if (etiqueta) {
        etiqueta.addEventListener('click', function (evento) {
            evento.preventDefault();
            enfocarBuscador();
            if (resultados.hidden) {
                buscar(input.value.trim());
            }
        });
    }

    contenedor.addEventListener('focusout', function () {
        window.setTimeout(function () {
            if (!contenedor.contains(document.activeElement)) {
                cerrar();
            }
        }, 0);
    });

    document.addEventListener('click', function (evento) {
        if (!contenedor.contains(evento.target)) {
            cerrar();
        }
    });
})();
