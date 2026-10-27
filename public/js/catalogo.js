(function () {
    'use strict';

    const form = document.getElementById('formCatalogo');
    const resultados = document.getElementById('resultadosCatalogo');

    if (!form || !resultados) {
        return;
    }

    const entrada = document.getElementById('q');
    const laboratorio = document.getElementById('laboratorio_id');
    const categoria = document.getElementById('categoria_id');
    const botonesVista = document.querySelectorAll('[data-vista]');
    const claveVista = 'farmasalud.vistaCatalogo';
    const modalElemento = document.getElementById('modalMedicamento');
    const modal = modalElemento ? bootstrap.Modal.getOrCreateInstance(modalElemento) : null;
    let temporizador = null;
    let controlador = null;

    function vistaGuardada() {
        try {
            return localStorage.getItem(claveVista) === 'tabla' ? 'tabla' : 'tarjetas';
        } catch (error) {
            return 'tarjetas';
        }
    }

    function aplicarVista(vista) {
        const tarjetas = document.getElementById('vistaTarjetas');
        const tabla = document.getElementById('vistaTabla');

        if (tarjetas) {
            tarjetas.classList.toggle('d-none', vista === 'tabla');
        }

        if (tabla) {
            tabla.classList.toggle('d-none', vista !== 'tabla');
        }

        botonesVista.forEach(function (boton) {
            boton.classList.toggle('activo', boton.dataset.vista === vista);
        });
    }

    function guardarVista(vista) {
        try {
            localStorage.setItem(claveVista, vista);
        } catch (error) {
            // El navegador puede bloquear localStorage en modo privado.
        }

        aplicarVista(vista);
    }

    function urlActual() {
        const url = new URL(resultados.dataset.url, window.location.origin);
        const parametros = new URLSearchParams();

        if (entrada.value.trim() !== '') {
            parametros.set('q', entrada.value.trim());
        }

        if (categoria.value) {
            parametros.set('categoria_id', categoria.value);
        }

        if (laboratorio.value) {
            parametros.set('laboratorio_id', laboratorio.value);
        }

        const pagina = new URLSearchParams(window.location.search).get('page');

        if (pagina && pagina !== '1') {
            parametros.set('page', pagina);
        }

        url.search = parametros.toString();

        return url;
    }

    function sincronizarUrl(url) {
        const parametros = new URLSearchParams(url.search);
        parametros.delete('formato');

        const nueva = new URL(window.location.href);
        nueva.search = parametros.toString();
        window.history.replaceState({}, '', nueva);
    }

    async function cargar(url, enfocarCategoria) {
        if (controlador) {
            controlador.abort();
        }

        controlador = new AbortController();
        resultados.classList.add('catalogo-loading');
        resultados.setAttribute('aria-busy', 'true');

        try {
            const respuesta = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controlador.signal,
            });

            if (!respuesta.ok) {
                throw new Error('No se pudo actualizar el catálogo.');
            }

            const datos = await respuesta.json();
            resultados.innerHTML = datos.html;
            aplicarVista(vistaGuardada());
            sincronizarUrl(url);

            if (enfocarCategoria) {
                const activo = resultados.querySelector('.chip.activo');
                if (activo) {
                    activo.focus({ preventScroll: true });
                }
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                resultados.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle me-1"></i>No se pudo cargar el catálogo. Intenta de nuevo.</div>';
            }
        } finally {
            resultados.classList.remove('catalogo-loading');
            resultados.removeAttribute('aria-busy');
        }
    }

    function programarCarga() {
        clearTimeout(temporizador);
        temporizador = setTimeout(function () {
            const url = urlActual();
            const pagina = new URLSearchParams(window.location.search).get('page');
            if (pagina) {
                url.searchParams.delete('page');
            }
            cargar(url);
        }, 320);
    }

    function abrirModal(datos) {
        if (!modal) {
            return;
        }

        document.getElementById('modalMedicamentoCategoria').textContent = datos.categoria;
        document.getElementById('modalMedicamentoTitulo').textContent = datos.nombre;
        document.getElementById('modalMedicamentoPrincipio').textContent = datos.principio;
        document.getElementById('modalMedicamentoPresentacion').textContent = datos.presentacion;
        document.getElementById('modalMedicamentoLaboratorio').textContent = datos.laboratorio;
        document.getElementById('modalMedicamentoPrecio').textContent = datos.precio_texto;
        document.getElementById('modalMedicamentoStock').textContent = datos.stock + ' / ' + datos.stock_minimo;

        const icono = document.getElementById('modalMedicamentoIcono');
        icono.style.background = datos.categoria_suave;
        icono.style.color = datos.categoria_color;
        icono.innerHTML = '';

        if (datos.imagen) {
            const foto = document.createElement('img');
            foto.src = datos.imagen;
            foto.alt = datos.nombre;
            foto.className = 'modal-medicamento-foto';
            icono.appendChild(foto);
        } else {
            icono.innerHTML = '<i class="bi ' + datos.categoria_icono + '"></i>';
        }

        const estados = {
            ok: 'Disponible',
            medio: 'Reponer pronto',
            bajo: 'Stock bajo',
            agotado: 'Agotado',
        };
        document.getElementById('modalMedicamentoEstado').textContent = estados[datos.estado] || '';

        const contenedorLotes = document.getElementById('modalMedicamentoLotes');
        contenedorLotes.innerHTML = '';

        if (!datos.lotes.length) {
            contenedorLotes.innerHTML = '<div class="empty-state py-4"><i class="bi bi-box-seam d-block mb-2"></i><p class="small mb-0">Sin lotes registrados.</p></div>';
        } else {
            datos.lotes.forEach(function (lote) {
                const item = document.createElement('div');
                item.className = 'lote-item';

                const detalle = document.createElement('div');
                const codigo = document.createElement('div');
                codigo.className = 'lote-item__codigo';
                codigo.textContent = lote.numero;

                const meta = document.createElement('div');
                meta.className = 'lote-item__meta';
                meta.textContent = lote.fecha + ' · ' + lote.estado;

                detalle.appendChild(codigo);
                detalle.appendChild(meta);

                const stock = document.createElement('div');
                stock.className = 'lote-item__stock';
                stock.textContent = lote.stock + ' u.';

                item.appendChild(detalle);
                item.appendChild(stock);
                contenedorLotes.appendChild(item);
            });
        }

        const editar = document.getElementById('modalMedicamentoEditar');
        if (editar) {
            editar.href = datos.urls.editar;
        }

        const eliminar = document.getElementById('modalMedicamentoEliminar');
        if (eliminar) {
            eliminar.dataset.eliminar = datos.urls.eliminar;
            eliminar.dataset.nombre = datos.nombre;
        }

        const vender = document.getElementById('modalMedicamentoVender');
        if (vender) {
            vender.href = datos.urls.vender;
            const sinStock = datos.stock <= 0;
            vender.classList.toggle('disabled', sinStock);
            vender.setAttribute('aria-disabled', sinStock ? 'true' : 'false');
        }

        modal.show();
    }

    form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        const url = urlActual();
        url.searchParams.delete('page');
        cargar(url);
    });

    entrada.addEventListener('input', programarCarga);
    laboratorio.addEventListener('change', function () {
        const url = urlActual();
        url.searchParams.delete('page');
        cargar(url);
    });

    botonesVista.forEach(function (boton) {
        boton.addEventListener('click', function () {
            guardarVista(boton.dataset.vista);
        });
    });

    resultados.addEventListener('click', function (evento) {
        const chip = evento.target.closest('[data-categoria]');

        if (chip) {
            categoria.value = chip.dataset.categoria;
            const url = urlActual();
            url.searchParams.delete('page');
            cargar(url, true);
            return;
        }

        const paginacion = evento.target.closest('[data-paginacion] a');

        if (paginacion) {
            evento.preventDefault();
            if (paginacion.closest('.disabled')) {
                return;
            }
            cargar(new URL(paginacion.href), false);
            resultados.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        const tarjeta = evento.target.closest('[data-abrir-medicamento]');

        if (!tarjeta) {
            return;
        }

        const accion = evento.target.closest('a, button');
        if (accion && !accion.hasAttribute('data-ver-medicamento')) {
            return;
        }

        try {
            abrirModal(JSON.parse(tarjeta.dataset.medicamento));
        } catch (error) {
            // Si el JSON se corrompe, se mantiene la navegación normal.
        }
    });

    resultados.addEventListener('keydown', function (evento) {
        if (evento.key !== 'Enter' && evento.key !== ' ') {
            return;
        }

        const tarjeta = evento.target.closest('[data-abrir-medicamento]');
        if (!tarjeta || evento.target !== tarjeta) {
            return;
        }

        evento.preventDefault();

        try {
            abrirModal(JSON.parse(tarjeta.dataset.medicamento));
        } catch (error) {
            // Sin acción.
        }
    });

    document.getElementById('modalMedicamentoVender')?.addEventListener('click', function (evento) {
        if (this.getAttribute('aria-disabled') === 'true') {
            evento.preventDefault();
        }
    });

    aplicarVista(vistaGuardada());
})();
