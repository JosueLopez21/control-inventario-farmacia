(function () {
    'use strict';

    const app = document.getElementById('posApp');

    if (!app) {
        return;
    }

    const endpoint = app.dataset.endpoint;
    const form = document.getElementById('formVenta');
    const entrada = document.getElementById('buscar');
    const resultados = document.getElementById('resultados');
    const lista = document.getElementById('carritoLista');
    const vacio = document.getElementById('carritoVacio');
    const contador = document.getElementById('posContador');
    const resumenItems = document.getElementById('resumenItems');
    const resumenUnidades = document.getElementById('resumenUnidades');
    const resumenTotal = document.getElementById('resumenTotal');
    const btnConfirmar = document.getElementById('btnConfirmar');
    const btnVaciar = document.getElementById('posVaciar');
    const formatoBs = window.FarmaSalud.formatoBs;
    let carrito = [];
    let temporizador = null;
    let controlador = null;

    try {
        carrito = JSON.parse(app.dataset.items || '[]');
    } catch (error) {
        carrito = [];
    }

    carrito = carrito.map(function (item) {
        return {
            id: item.id,
            nombre_comercial: item.nombre_comercial,
            precio_venta: Number(item.precio_venta) || 0,
            stock: Number(item.stock) || 0,
            cantidad: Math.max(1, Math.min(Number(item.cantidad) || 1, Number(item.stock) || 1)),
            categoria: item.categoria || 'General',
            categoria_icono: item.categoria_icono || 'bi-capsule',
            categoria_color: item.categoria_color || '#0f766e',
            categoria_suave: item.categoria_suave || '#e6f4f1',
        };
    });

    function icono(datos) {
        const span = document.createElement('span');
        span.className = 'pos-item__icono';
        span.style.background = datos.categoria_suave;
        span.style.color = datos.categoria_color;

        const i = document.createElement('i');
        i.className = 'bi ' + datos.categoria_icono;
        span.appendChild(i);

        return span;
    }

    function filaCarrito(item, indice) {
        const fila = document.createElement('div');
        fila.className = 'pos-item';

        const principal = document.createElement('div');
        principal.className = 'pos-item__principal';
        principal.appendChild(icono(item));

        const texto = document.createElement('div');
        texto.className = 'pos-item__texto';

        const nombre = document.createElement('strong');
        nombre.textContent = item.nombre_comercial;

        const meta = document.createElement('small');
        meta.textContent = formatoBs(item.precio_venta) + ' · Stock ' + item.stock;

        texto.appendChild(nombre);
        texto.appendChild(meta);
        principal.appendChild(texto);

        const controles = document.createElement('div');
        controles.className = 'pos-item__controles';

        const restar = document.createElement('button');
        restar.type = 'button';
        restar.className = 'pos-cantidad-btn';
        restar.dataset.posAccion = 'restar';
        restar.dataset.indice = indice;
        restar.setAttribute('aria-label', 'Restar una unidad de ' + item.nombre_comercial);
        restar.disabled = item.cantidad <= 1;
        restar.innerHTML = '<i class="bi bi-dash-lg"></i>';

        const cantidad = document.createElement('span');
        cantidad.className = 'pos-cantidad';
        cantidad.textContent = item.cantidad;

        const sumar = document.createElement('button');
        sumar.type = 'button';
        sumar.className = 'pos-cantidad-btn';
        sumar.dataset.posAccion = 'sumar';
        sumar.dataset.indice = indice;
        sumar.setAttribute('aria-label', 'Sumar una unidad de ' + item.nombre_comercial);
        sumar.disabled = item.cantidad >= item.stock;
        sumar.innerHTML = '<i class="bi bi-plus-lg"></i>';

        controles.appendChild(restar);
        controles.appendChild(cantidad);
        controles.appendChild(sumar);

        const quitar = document.createElement('button');
        quitar.type = 'button';
        quitar.className = 'btn btn-sm btn-outline-danger pos-quitar';
        quitar.dataset.posAccion = 'quitar';
        quitar.dataset.indice = indice;
        quitar.setAttribute('aria-label', 'Quitar ' + item.nombre_comercial);
        quitar.innerHTML = '<i class="bi bi-x-lg"></i>';

        const subtotal = document.createElement('div');
        subtotal.className = 'pos-item__subtotal';
        subtotal.textContent = formatoBs(item.precio_venta * item.cantidad);

        fila.appendChild(principal);
        fila.appendChild(controles);
        fila.appendChild(subtotal);
        fila.appendChild(quitar);

        return fila;
    }

    function actualizarResumen() {
        const unidades = carrito.reduce(function (total, item) {
            return total + item.cantidad;
        }, 0);

        const total = carrito.reduce(function (suma, item) {
            return suma + item.precio_venta * item.cantidad;
        }, 0);

        contador.textContent = carrito.length;
        resumenItems.textContent = carrito.length;
        resumenUnidades.textContent = unidades;
        resumenTotal.textContent = formatoBs(total);
        btnConfirmar.disabled = carrito.length === 0;
        vacio.style.display = carrito.length ? 'none' : 'block';
    }

    function dibujarCarrito() {
        lista.innerHTML = '';

        carrito.forEach(function (item, indice) {
            lista.appendChild(filaCarrito(item, indice));
        });

        actualizarResumen();
    }

    function agregar(datos) {
        const existente = carrito.find(function (item) {
            return item.id === datos.id;
        });

        if (existente) {
            if (existente.cantidad < existente.stock) {
                existente.cantidad += 1;
            }
        } else if (datos.stock > 0) {
            carrito.push({
                id: datos.id,
                nombre_comercial: datos.nombre_comercial,
                precio_venta: Number(datos.precio_venta) || 0,
                stock: Number(datos.stock) || 0,
                cantidad: 1,
                categoria: datos.categoria || 'General',
                categoria_icono: datos.categoria_icono || 'bi-capsule',
                categoria_color: datos.categoria_color || '#0f766e',
                categoria_suave: datos.categoria_suave || '#e6f4f1',
            });
        }

        dibujarCarrito();
    }

    function botonResultado(medicamento) {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'pos-resultado';
        boton.disabled = medicamento.stock <= 0;

        boton.appendChild(icono(medicamento));

        const texto = document.createElement('span');
        texto.className = 'pos-resultado__texto';

        const nombre = document.createElement('strong');
        nombre.textContent = medicamento.nombre_comercial;

        const detalle = document.createElement('small');
        detalle.textContent = medicamento.presentacion + ' · ' + medicamento.principio_activo;

        const stock = document.createElement('small');
        stock.className = 'pos-resultado__stock';
        stock.textContent = medicamento.stock > 0 ? 'Stock vigente: ' + medicamento.stock : 'Sin stock vigente';

        texto.appendChild(nombre);
        texto.appendChild(detalle);
        texto.appendChild(stock);

        const precio = document.createElement('span');
        precio.className = 'pos-resultado__precio';
        precio.textContent = formatoBs(medicamento.precio_venta);

        const agregarIcono = document.createElement('span');
        agregarIcono.className = 'pos-resultado__agregar';
        agregarIcono.innerHTML = '<i class="bi bi-plus-lg"></i>';

        boton.appendChild(texto);
        boton.appendChild(precio);
        boton.appendChild(agregarIcono);

        boton.addEventListener('click', function () {
            agregar(medicamento);
        });

        return boton;
    }

    function renderizarResultados(datos) {
        resultados.innerHTML = '';

        if (!datos.length) {
            const aviso = document.createElement('div');
            aviso.className = 'pos-pista';
            aviso.innerHTML = '<i class="bi bi-search"></i><span>No encontramos medicamentos con ese nombre.</span>';
            resultados.appendChild(aviso);
            return;
        }

        datos.forEach(function (medicamento) {
            resultados.appendChild(botonResultado(medicamento));
        });
    }

    async function buscar(termino) {
        if (controlador) {
            controlador.abort();
        }

        controlador = new AbortController();
        resultados.setAttribute('aria-busy', 'true');

        try {
            const respuesta = await fetch(endpoint + '?q=' + encodeURIComponent(termino), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controlador.signal,
            });

            if (!respuesta.ok) {
                throw new Error('Error de búsqueda');
            }

            const datos = await respuesta.json();
            renderizarResultados(datos.resultados || []);
        } catch (error) {
            if (error.name !== 'AbortError') {
                resultados.innerHTML = '<div class="pos-pista text-danger"><i class="bi bi-exclamation-triangle"></i><span>No se pudo buscar. Intenta de nuevo.</span></div>';
            }
        } finally {
            resultados.removeAttribute('aria-busy');
        }
    }

    entrada.addEventListener('input', function () {
        clearTimeout(temporizador);
        const termino = this.value.trim();

        if (termino.length < 2) {
            if (controlador) {
                controlador.abort();
            }
            resultados.innerHTML = '<div class="pos-pista"><i class="bi bi-hand-index-thumb"></i><span>Los medicamentos con stock vigente aparecerán aquí.</span></div>';
            return;
        }

        temporizador = setTimeout(function () {
            buscar(termino);
        }, 260);
    });

    entrada.addEventListener('keydown', function (evento) {
        if (evento.key !== 'Enter') {
            return;
        }

        evento.preventDefault();
        const primero = resultados.querySelector('.pos-resultado:not(:disabled)');

        if (primero) {
            primero.click();
        }
    });

    lista.addEventListener('click', function (evento) {
        const boton = evento.target.closest('[data-pos-accion]');

        if (!boton) {
            return;
        }

        const indice = Number(boton.dataset.indice);
        const item = carrito[indice];

        if (!item) {
            return;
        }

        if (boton.dataset.posAccion === 'sumar') {
            item.cantidad = Math.min(item.cantidad + 1, item.stock);
        } else if (boton.dataset.posAccion === 'restar') {
            item.cantidad = Math.max(item.cantidad - 1, 1);
        } else if (boton.dataset.posAccion === 'quitar') {
            carrito.splice(indice, 1);
        }

        dibujarCarrito();
    });

    btnVaciar.addEventListener('click', function () {
        carrito = [];
        dibujarCarrito();
        entrada.focus();
    });

    form.addEventListener('submit', function (evento) {
        if (!carrito.length) {
            evento.preventDefault();
            return;
        }

        document.getElementById('items').value = JSON.stringify(carrito.map(function (item) {
            return {
                medicamento_id: item.id,
                cantidad: item.cantidad,
            };
        }));
    });

    dibujarCarrito();
})();
