(function () {
    'use strict';

    const canvas = document.getElementById('graficoVentas');
    const boton = document.getElementById('btnSimularVenta');
    const resultado = document.getElementById('resultadoSimulacion');

    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const datosIniciales = JSON.parse(canvas.dataset.grafico || '{}');
    const formatoBs = window.FarmaSalud.formatoBs;
    const formatoNumero = window.FarmaSalud.formatoNumero;

    const grafico = new Chart(canvas, {
        type: 'line',
        data: {
            labels: datosIniciales.etiquetas || [],
            datasets: [
                {
                    label: 'Ventas',
                    data: datosIniciales.totales || [],
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, .12)',
                    fill: true,
                    tension: .35,
                    borderWidth: 2,
                    pointBackgroundColor: '#0f766e',
                },
                {
                    label: 'Ingresos (Bs)',
                    data: datosIniciales.montos || [],
                    borderColor: '#1d4ed8',
                    backgroundColor: 'rgba(29, 78, 216, .08)',
                    fill: false,
                    tension: .35,
                    borderWidth: 2,
                    pointBackgroundColor: '#1d4ed8',
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
            },
        },
    });

    function escapar(texto) {
        const contenedor = document.createElement('div');
        contenedor.textContent = texto;
        return contenedor.innerHTML;
    }

    function actualizarMetricas(metricas) {
        Object.keys(metricas).forEach(function (clave) {
            const elemento = document.querySelector('[data-contador="' + clave + '"]');

            if (!elemento) {
                return;
            }

            const anterior = Number(elemento.dataset.valor) || 0;
            const nuevo = Number(metricas[clave]) || 0;
            const formato = elemento.dataset.formato === 'bs' ? formatoBs : formatoNumero;

            elemento.dataset.valor = nuevo;
            window.FarmaSalud.animar(elemento, anterior, nuevo, 650, formato);
        });
    }

    function mostrarResultado(datos) {
        if (!resultado) {
            return;
        }

        const detalles = datos.venta.detalles.map(function (detalle) {
            return '<li><strong>' + detalle.cantidad + ' u.</strong> ' + escapar(detalle.medicamento) +
                ' <span class="text-muted">· lote ' + escapar(detalle.lote) + ', vence ' + escapar(detalle.fecha_vencimiento) + '</span></li>';
        }).join('');

        resultado.innerHTML =
            '<div class="alert alert-success border-0 shadow-sm sim-resultado mb-0">' +
                '<div class="d-flex align-items-start gap-2">' +
                    '<i class="bi bi-stars fs-4"></i>' +
                    '<div class="flex-grow-1">' +
                        '<div class="d-flex flex-wrap justify-content-between gap-2">' +
                            '<strong>' + datos.message + '</strong>' +
                            '<span class="badge bg-success">' + formatoBs(datos.venta.monto_total) + '</span>' +
                        '</div>' +
                        '<ul class="small mb-1 mt-2 ps-3">' + detalles + '</ul>' +
                        '<div class="small text-success-emphasis"><i class="bi bi-check2-circle me-1"></i>Stock descontado del lote vigente más próximo a vencer.</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        resultado.scrollIntoView({ behavior: window.FarmaSalud.movimientoReducido ? 'auto' : 'smooth', block: 'nearest' });
    }

    if (boton) {
        boton.addEventListener('click', async function () {
            const contenido = boton.innerHTML;
            boton.disabled = true;
            boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Simulando...';

            try {
                const respuesta = await fetch(boton.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const datos = await respuesta.json();

                if (!respuesta.ok || !datos.ok) {
                    throw new Error(datos.message || 'No se pudo simular la venta.');
                }

                grafico.data.labels = datos.grafico.etiquetas;
                grafico.data.datasets[0].data = datos.grafico.totales;
                grafico.data.datasets[1].data = datos.grafico.montos;
                grafico.update();

                actualizarMetricas(datos.metricas);
                mostrarResultado(datos);
            } catch (error) {
                if (resultado) {
                    resultado.innerHTML = '<div class="alert alert-danger border-0 shadow-sm mb-0"><i class="bi bi-exclamation-triangle me-1"></i>' + escapar(error.message) + '</div>';
                }
            } finally {
                boton.disabled = false;
                boton.innerHTML = contenido;
            }
        });
    }
})();
