@extends('layouts.app')

@section('titulo', 'Nueva venta')
@section('subtitulo', 'Punto de venta · descuento FEFO automático')

@section('contenido')
<form method="POST" action="{{ route('ventas.store') }}" id="formVenta" novalidate>
    @csrf
    <input type="hidden" name="items" id="items" value="">
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-search me-2"></i>Buscar medicamento</div>
                <div class="card-body">
                    <label class="form-label" for="buscar">Nombre comercial o principio activo</label>
                    <input type="text" id="buscar" class="form-control" placeholder="Escribe al menos 2 letras..." autocomplete="off">
                    <div id="resultados" class="list-group mt-2"></div>
                    <div class="form-text">Solo se muestran medicamentos con stock vigente disponible.</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-basket me-2"></i>Carrito</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr><th>Medicamento</th><th class="text-end">Precio</th><th class="text-center" style="width: 130px;">Cantidad</th><th class="text-end">Subtotal</th><th></th></tr>
                            </thead>
                            <tbody id="carritoCuerpo"></tbody>
                        </table>
                    </div>
                    <div id="carritoVacio" class="empty-state">
                        <i class="bi bi-basket d-block mb-2"></i>
                        <h3 class="h6 fw-semibold text-secondary">El carrito está vacío</h3>
                        <p class="small mb-0">Busca un medicamento y agrégalo para comenzar la venta.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card" style="position: sticky; top: 90px;">
                <div class="card-header"><i class="bi bi-calculator me-2"></i>Resumen de la venta</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ítems</span>
                        <span id="resumenItems" class="fw-semibold">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Unidades</span>
                        <span id="resumenUnidades" class="fw-semibold">0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-semibold">Total a pagar</span>
                        <span id="resumenTotal" class="fs-4 fw-bold text-farma">Bs 0,00</span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" id="btnConfirmar">
                        <i class="bi bi-check2-circle me-1"></i>Confirmar venta
                    </button>
                    <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancelar</a>
                    <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>El sistema descuenta automáticamente del lote que vence primero (FEFO) y bloquea lotes vencidos.</p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const endpointBusqueda = @json(route('medicamentos.buscar'));
    let carrito = @json($itemsIniciales);

    const entradaBusqueda = document.getElementById('buscar');
    const contenedorResultados = document.getElementById('resultados');
    const cuerpo = document.getElementById('carritoCuerpo');
    const vacio = document.getElementById('carritoVacio');

    const formatoBs = (valor) => 'Bs ' + Number(valor).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function dibujarCarrito() {
        cuerpo.innerHTML = '';
        vacio.style.display = carrito.length ? 'none' : 'block';

        carrito.forEach((item, indice) => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>
                    <div class="fw-semibold">${item.nombre_comercial}</div>
                    <div class="small text-muted">Stock: ${item.stock}</div>
                </td>
                <td class="text-end">${formatoBs(item.precio_venta)}</td>
                <td class="text-center">
                    <input type="number" min="1" max="${item.stock}" value="${item.cantidad}" class="form-control form-control-sm text-center cantidad" data-indice="${indice}">
                </td>
                <td class="text-end fw-semibold">${formatoBs(item.precio_venta * item.cantidad)}</td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger quitar" data-indice="${indice}" title="Quitar"><i class="bi bi-x-lg"></i></button></td>
            `;
            cuerpo.appendChild(fila);
        });

        const unidades = carrito.reduce((total, item) => total + item.cantidad, 0);
        const total = carrito.reduce((suma, item) => suma + (item.precio_venta * item.cantidad), 0);

        document.getElementById('resumenItems').textContent = carrito.length;
        document.getElementById('resumenUnidades').textContent = unidades;
        document.getElementById('resumenTotal').textContent = formatoBs(total);
        document.getElementById('btnConfirmar').disabled = carrito.length === 0;
    }

    function agregar(datos) {
        const existente = carrito.find((item) => item.id === datos.id);
        if (existente) {
            existente.cantidad = Math.min(existente.cantidad + 1, existente.stock);
        } else {
            if (datos.stock <= 0) return;
            carrito.push({ ...datos, cantidad: 1 });
        }
        dibujarCarrito();
    }

    let temporizador = null;
    entradaBusqueda.addEventListener('input', function () {
        clearTimeout(temporizador);
        const termino = this.value.trim();
        if (termino.length < 2) {
            contenedorResultados.innerHTML = '';
            return;
        }
        temporizador = setTimeout(async () => {
            try {
                const respuesta = await fetch(endpointBusqueda + '?q=' + encodeURIComponent(termino), { headers: { 'Accept': 'application/json' } });
                const datos = await respuesta.json();
                contenedorResultados.innerHTML = '';
                if (!datos.resultados.length) {
                    contenedorResultados.innerHTML = '<div class="list-group-item text-muted small">Sin resultados.</div>';
                    return;
                }
                datos.resultados.forEach((medicamento) => {
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                    boton.innerHTML = `
                        <span>
                            <span class="fw-semibold d-block">${medicamento.nombre_comercial}</span>
                            <span class="small text-muted">${medicamento.presentacion} · Stock: ${medicamento.stock}</span>
                        </span>
                        <span class="badge ${medicamento.stock > 0 ? 'bg-success' : 'bg-secondary'}">${formatoBs(medicamento.precio_venta)}</span>
                    `;
                    boton.disabled = medicamento.stock <= 0;
                    boton.addEventListener('click', () => agregar(medicamento));
                    contenedorResultados.appendChild(boton);
                });
            } catch (error) {
                contenedorResultados.innerHTML = '<div class="list-group-item text-danger small">Error al buscar. Intenta de nuevo.</div>';
            }
        }, 250);
    });

    cuerpo.addEventListener('input', function (evento) {
        if (!evento.target.classList.contains('cantidad')) return;
        const indice = Number(evento.target.dataset.indice);
        let valor = parseInt(evento.target.value, 10);
        if (isNaN(valor) || valor < 1) valor = 1;
        if (valor > carrito[indice].stock) valor = carrito[indice].stock;
        carrito[indice].cantidad = valor;
        dibujarCarrito();
    });

    cuerpo.addEventListener('click', function (evento) {
        const boton = evento.target.closest('.quitar');
        if (!boton) return;
        carrito.splice(Number(boton.dataset.indice), 1);
        dibujarCarrito();
    });

    document.getElementById('formVenta').addEventListener('submit', function (evento) {
        if (!carrito.length) {
            evento.preventDefault();
            return;
        }
        document.getElementById('items').value = JSON.stringify(carrito.map((item) => ({
            medicamento_id: item.id,
            cantidad: item.cantidad
        })));
    });

    dibujarCarrito();
</script>
@endpush
