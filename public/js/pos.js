(function () {
    'use strict';

    const app = document.getElementById('posApp');

    if (!app) {
        return;
    }

    const endpoint = app.dataset.endpoint;
    const form = document.getElementById('formVenta');
    const entrada = document.getElementById('buscar');
    const sugerencias = document.getElementById('sugerencias');
    const grid = document.getElementById('posGrid');
    const gridEstado = document.getElementById('posGridEstado');
    const conteo = document.getElementById('posConteo');
    const categorias = document.getElementById('posCategorias');
    const verMas = document.getElementById('posVerMas');
    const lista = document.getElementById('carritoLista');
    const vacio = document.getElementById('carritoVacio');
    const contador = document.getElementById('posContador');
    const resumenItems = document.getElementById('resumenItems');
    const resumenUnidades = document.getElementById('resumenUnidades');
    const resumenTotal = document.getElementById('resumenTotal');
    const btnConfirmar = document.getElementById('btnConfirmar');
    const btnVaciar = document.getElementById('posVaciar');
    const formatoBs = window.FarmaSalud.formatoBs;
    const tamanoPagina = 12;

    let carrito = [];
    let temporizador = null;
    let controlador = null;
    let terminoActual = '';
    let categoriaActual = '';
    let offsetActual = 0;
    let totalActual = 0;
    let indiceSugerencia = -1;
    let sugerenciasActuales = [];
    let totalAnterior = 0;

    try {
        carrito = JSON.parse(app.dataset.items || '[]');
    } catch (error) {
        carrito = [];
    }

    carrito = carrito.map(function (item) {
        return {
            id: item.id,
            nombre_comercial: item.nombre_comercial,
            presentacion: item.presentacion || '',
            laboratorio: item.laboratorio || '',
            precio_venta: Number(item.precio_venta) || 0,
            stock: Number(item.stock) || 0,
            cantidad: Math.max(1, Math.min(Number(item.cantidad) || 1, Number(item.stock) || 1)),
            categoria: item.categoria || 'General',
            categoria_icono: item.categoria_icono || 'bi-capsule',
            categoria_color: item.categoria_color || '#0f766e',
            categoria_suave: item.categoria_suave || '#e6f4f1',
        };
    });

    function crearIcono(datos, clase) {
        const span = document.createElement('span');
        span.className = clase;
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
        principal.appendChild(crearIcono(item, 'pos-item__icono'));

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
        btnConfirmar.disabled = carrito.length === 0;
        vacio.style.display = carrito.length ? 'none' : 'block';

        if (total === totalAnterior && resumenTotal.textContent.trim() !== '') {
            resumenTotal.textContent = formatoBs(total);
        } else {
            window.FarmaSalud.animar(resumenTotal, totalAnterior, total, 320, formatoBs);
        }

        totalAnterior = total;
    }

    function dibujarCarrito() {
        lista.innerHTML = '';

        carrito.forEach(function (item, indice) {
            lista.appendChild(filaCarrito(item, indice));
        });

        actualizarResumen();
    }

    function agregar(datos) {
        const stock = Number(datos.stock) || 0;

        if (stock <= 0) {
            return;
        }

        const existente = carrito.find(function (item) {
            return item.id === datos.id;
        });

        if (existente) {
            if (existente.cantidad < existente.stock) {
                existente.cantidad += 1;
            }
        } else {
            carrito.push({
                id: datos.id,
                nombre_comercial: datos.nombre_comercial,
                presentacion: datos.presentacion || '',
                laboratorio: datos.laboratorio || '',
                precio_venta: Number(datos.precio_venta) || 0,
                stock: stock,
                cantidad: 1,
                categoria: datos.categoria || 'General',
                categoria_icono: datos.categoria_icono || 'bi-capsule',
                categoria_color: datos.categoria_color || '#0f766e',
                categoria_suave: datos.categoria_suave || '#e6f4f1',
            });
        }

        dibujarCarrito();
    }

    function cerrarSugerencias() {
        sugerencias.hidden = true;
        sugerencias.innerHTML = '';
        entrada.setAttribute('aria-expanded', 'false');
        entrada.removeAttribute('aria-activedescendant');
        indiceSugerencia = -1;
        sugerenciasActuales = [];
    }

    function enfocarSugerencia(indice) {
        const botones = Array.from(sugerencias.querySelectorAll('.pos-sugerencia'));

        if (!botones.length) {
            return;
        }

        indiceSugerencia = Math.max(0, Math.min(indice, botones.length - 1));

        botones.forEach(function (boton, posicion) {
            const activo = posicion === indiceSugerencia;
            boton.classList.toggle('is-activo', activo);
            boton.setAttribute('aria-selected', activo ? 'true' : 'false');
        });

        entrada.setAttribute('aria-activedescendant', botones[indiceSugerencia].id);
        botones[indiceSugerencia].scrollIntoView({ block: 'nearest' });
    }

    function agregarSugerenciaActiva() {
        if (indiceSugerencia < 0) {
            return;
        }

        const medicamento = sugerenciasActuales[indiceSugerencia];

        if (medicamento) {
            agregar(medicamento);
            cerrarSugerencias();
        }
    }

    function crearSugerencia(medicamento, indice) {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'pos-sugerencia';
        boton.id = 'sugerencia-' + indice;
        boton.dataset.indice = indice;
        boton.setAttribute('role', 'option');
        boton.setAttribute('aria-selected', 'false');

        boton.appendChild(crearIcono(medicamento, 'pos-sugerencia__icono'));

        const texto = document.createElement('span');
        texto.className = 'pos-sugerencia__texto';

        const nombre = document.createElement('strong');
        nombre.textContent = medicamento.nombre_comercial;

        const detalle = document.createElement('small');
        detalle.textContent = [medicamento.presentacion, medicamento.laboratorio].filter(Boolean).join(' · ');

        texto.appendChild(nombre);
        texto.appendChild(detalle);

        if (indice === 0) {
            const recomendado = document.createElement('span');
            recomendado.className = 'pos-sugerencia__recomendado';
            recomendado.textContent = 'Recomendado';
            texto.appendChild(recomendado);
        }

        const stock = document.createElement('span');
        stock.className = 'pos-sugerencia__stock';
        stock.textContent = 'Stock ' + medicamento.stock;

        const precio = document.createElement('span');
        precio.className = 'pos-sugerencia__precio';
        precio.textContent = formatoBs(medicamento.precio_venta);

        boton.appendChild(texto);
        boton.appendChild(stock);
        boton.appendChild(precio);

        boton.addEventListener('mouseenter', function () {
            enfocarSugerencia(indice);
        });

        return boton;
    }

    function renderizarSugerencias(resultados) {
        cerrarSugerencias();

        if (terminoActual.length < 3 || !resultados.length) {
            return;
        }

        sugerenciasActuales = resultados.slice(0, 8);
        sugerenciasActuales.forEach(function (medicamento, indice) {
            sugerencias.appendChild(crearSugerencia(medicamento, indice));
        });

        sugerencias.hidden = false;
        entrada.setAttribute('aria-expanded', 'true');
        enfocarSugerencia(0);
    }

    function crearTarjeta(medicamento) {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'pos-card';
        boton.disabled = Number(medicamento.stock) <= 0;
        boton.dataset.producto = String(medicamento.id);
        boton.setAttribute('aria-label', 'Agregar ' + medicamento.nombre_comercial + ' al carrito');
        boton.style.setProperty('--cat-color', medicamento.categoria_color || '#0f766e');
        boton.style.setProperty('--cat-soft', medicamento.categoria_suave || '#e6f4f1');

        const cabecera = document.createElement('span');
        cabecera.className = 'pos-card__cabecera';
        cabecera.appendChild(crearIcono(medicamento, 'pos-card__icono'));

        const stock = Number(medicamento.stock) || 0;
        const minimo = Math.max(1, Number(medicamento.stock_minimo) || 1);
        const estadoStock = document.createElement('span');
        estadoStock.className = 'pos-card__stock ' + (stock <= 0 ? 'es-agotado' : (stock < minimo ? 'es-bajo' : 'es-ok'));
        estadoStock.innerHTML = '<span class="pos-card__punto" aria-hidden="true"></span>' + (stock > 0 ? 'Stock ' + stock : 'Agotado');
        cabecera.appendChild(estadoStock);

        const nombre = document.createElement('strong');
        nombre.className = 'pos-card__nombre';
        nombre.textContent = medicamento.nombre_comercial;

        const presentacion = document.createElement('span');
        presentacion.className = 'pos-card__presentacion';
        presentacion.textContent = medicamento.presentacion;

        const laboratorio = document.createElement('span');
        laboratorio.className = 'pos-card__laboratorio';
        laboratorio.textContent = medicamento.laboratorio || 'Sin laboratorio';

        const pie = document.createElement('span');
        pie.className = 'pos-card__pie';

        const precio = document.createElement('span');
        precio.className = 'pos-card__precio';
        precio.textContent = formatoBs(medicamento.precio_venta);

        const agregarIcono = document.createElement('span');
        agregarIcono.className = 'pos-card__agregar';
        agregarIcono.innerHTML = '<i class="bi bi-plus-lg" aria-hidden="true"></i>';

        pie.appendChild(precio);
        pie.appendChild(agregarIcono);

        boton.appendChild(cabecera);
        boton.appendChild(nombre);
        boton.appendChild(presentacion);
        boton.appendChild(laboratorio);
        boton.appendChild(pie);

        boton.addEventListener('click', function () {
            agregar(medicamento);
        });

        return boton;
    }

    function actualizarEstadoGrid(cantidad, total) {
        conteo.textContent = total;

        if (total === 0) {
            gridEstado.textContent = terminoActual.length >= 3
                ? 'No encontramos coincidencias. Prueba con otro nombre o categoría.'
                : 'No hay productos con stock vigente en esta categoría.';
            return;
        }

        gridEstado.textContent = cantidad < total
            ? 'Mostrando ' + cantidad + ' de ' + total + ' productos'
            : total + (total === 1 ? ' producto' : ' productos') + ' con stock vigente';
    }

    function renderizarCatalogo(datos, reiniciar) {
        const resultados = datos.resultados || [];

        if (reiniciar) {
            grid.innerHTML = '';
        }

        resultados.forEach(function (medicamento) {
            grid.appendChild(crearTarjeta(medicamento));
        });

        totalActual = Number(datos.total) || 0;
        offsetActual = (reiniciar ? 0 : offsetActual) + resultados.length;
        verMas.hidden = !datos.hay_mas;
        actualizarEstadoGrid(offsetActual, totalActual);

        if (!offsetActual) {
            const aviso = document.createElement('div');
            aviso.className = 'pos-pista pos-grid__vacio';
            aviso.innerHTML = '<i class="bi bi-search"></i><span>No encontramos productos para mostrar.</span>';
            grid.appendChild(aviso);
        }

        if (reiniciar) {
            renderizarSugerencias(resultados);
        }
    }

    async function cargarCatalogo(reiniciar) {
        if (controlador) {
            controlador.abort();
        }

        controlador = new AbortController();

        const terminoSolicitado = terminoActual;
        const categoriaSolicitada = categoriaActual;
        const desplazamientoSolicitado = reiniciar ? 0 : offsetActual;
        const parametros = new URLSearchParams({
            limit: String(tamanoPagina),
            offset: String(desplazamientoSolicitado),
        });

        if (terminoSolicitado.length >= 3) {
            parametros.set('q', terminoSolicitado);
        }

        if (categoriaSolicitada) {
            parametros.set('categoria_id', categoriaSolicitada);
        }

        grid.setAttribute('aria-busy', 'true');

        try {
            const respuesta = await fetch(endpoint + '?' + parametros.toString(), {
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

            if (terminoSolicitado !== terminoActual || categoriaSolicitada !== categoriaActual) {
                return;
            }

            renderizarCatalogo(datos, reiniciar);
        } catch (error) {
            if (error.name !== 'AbortError') {
                gridEstado.textContent = 'No se pudo cargar el catálogo. Intenta de nuevo.';
                if (reiniciar) {
                    grid.innerHTML = '<div class="pos-pista text-danger"><i class="bi bi-exclamation-triangle"></i><span>No se pudo buscar. Intenta de nuevo.</span></div>';
                }
            }
        } finally {
            grid.removeAttribute('aria-busy');
        }
    }

    entrada.addEventListener('input', function () {
        clearTimeout(temporizador);
        terminoActual = this.value.trim();

        if (terminoActual.length < 3) {
            cerrarSugerencias();

            if (controlador) {
                controlador.abort();
            }

            if (terminoActual.length === 0) {
                cargarCatalogo(true);
            } else {
                gridEstado.textContent = 'Escribe al menos 3 letras para buscar o elige una categoría.';
            }

            return;
        }

        temporizador = setTimeout(function () {
            cargarCatalogo(true);
        }, 150);
    });

    entrada.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarSugerencias();
            return;
        }

        if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
            if (sugerencias.hidden || !sugerenciasActuales.length) {
                return;
            }

            evento.preventDefault();
            enfocarSugerencia(indiceSugerencia + (evento.key === 'ArrowDown' ? 1 : -1));
            return;
        }

        if (evento.key === 'Enter') {
            if (!sugerencias.hidden && indiceSugerencia >= 0) {
                evento.preventDefault();
                agregarSugerenciaActiva();
            }

            return;
        }
    });

    sugerencias.addEventListener('click', function (evento) {
        const boton = evento.target.closest('.pos-sugerencia');

        if (!boton) {
            return;
        }

        const medicamento = sugerenciasActuales[Number(boton.dataset.indice)];

        if (medicamento) {
            agregar(medicamento);
            cerrarSugerencias();
            entrada.focus();
        }
    });

    document.addEventListener('click', function (evento) {
        if (!evento.target.closest('.pos-buscador__campo')) {
            cerrarSugerencias();
        }
    });

    categorias.addEventListener('click', function (evento) {
        const chip = evento.target.closest('[data-categoria]');

        if (!chip) {
            return;
        }

        categoriaActual = chip.dataset.categoria || '';

        categorias.querySelectorAll('[data-categoria]').forEach(function (boton) {
            const activo = (boton.dataset.categoria || '') === categoriaActual;
            boton.classList.toggle('activo', activo);
            boton.setAttribute('aria-pressed', activo ? 'true' : 'false');
        });

        terminoActual = entrada.value.trim();
        cerrarSugerencias();
        cargarCatalogo(true);
    });

    verMas.addEventListener('click', function () {
        cargarCatalogo(false);
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
        totalAnterior = 0;
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
    cargarCatalogo(true);
})();
