@php
    $hoy = now()->startOfDay();
    $totalGeneral = $categorias->sum('total');
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="chips flex-grow-1" role="group" aria-label="Filtrar por categoría">
        <button type="button" class="chip {{ blank($categoriaId) ? 'activo' : '' }}" data-categoria="" aria-pressed="{{ blank($categoriaId) ? 'true' : 'false' }}">
            <i class="bi bi-grid"></i>Todas
            <span class="chip-count">{{ $totalGeneral }}</span>
        </button>
        @foreach ($categorias as $categoria)
            <button type="button"
                    class="chip {{ (string) $categoriaId === (string) $categoria->id ? 'activo' : '' }}"
                    data-categoria="{{ $categoria->id }}"
                    aria-pressed="{{ (string) $categoriaId === (string) $categoria->id ? 'true' : 'false' }}">
                <i class="bi {{ categoria_ui($categoria->nombre)['icono'] }}"></i>{{ $categoria->nombre }}
                <span class="chip-count">{{ $categoria->total }}</span>
            </button>
        @endforeach
    </div>
    <div class="small text-muted text-nowrap" aria-live="polite">
        <strong class="text-farma">{{ $medicamentos->total() }}</strong> resultado(s)
    </div>
</div>

@if ($medicamentos->isEmpty())
    <div class="card">
        <x-empty-state icono="bi-search" titulo="No se encontraron medicamentos" mensaje="Prueba con otro término de búsqueda o cambia los filtros." />
    </div>
@else
    <div id="vistaTarjetas">
        <div class="med-grid">
            @foreach ($medicamentos as $medicamento)
                <x-medicamento-card :medicamento="$medicamento" />
            @endforeach
        </div>
    </div>

    <div id="vistaTabla" class="d-none">
        <div class="table-responsive tabla-catalogo">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Categoría</th>
                        <th>Laboratorio</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Stock vigente</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($medicamentos as $medicamento)
                        @php
                            $stock = (int) $medicamento->lotes
                                ->filter(fn ($lote) => $lote->fecha_vencimiento->gte($hoy) && $lote->cantidad_stock > 0)
                                ->sum('cantidad_stock');
                            $ui = categoria_ui($medicamento->categoria?->nombre);
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="cat-icon" style="width: 34px; height: 34px; font-size: .92rem; background: {{ $ui['suave'] }}; color: {{ $ui['color'] }};">
                                        <i class="bi {{ $ui['icono'] }}"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $medicamento->nombre_comercial }}</div>
                                        <div class="small text-muted">{{ $medicamento->principio_activo }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $medicamento->categoria?->nombre ?? '—' }}</span></td>
                            <td>{{ $medicamento->laboratorio?->nombre ?? '—' }}</td>
                            <td class="text-end fw-semibold">{{ formato_bs($medicamento->precio_venta) }}</td>
                            <td class="text-center">
                                @if ($stock <= 0)
                                    <span class="badge bg-secondary" title="Sin stock vigente">Agotado</span>
                                @elseif ($stock < $medicamento->stock_minimo)
                                    <span class="badge bg-danger" title="Bajo el mínimo ({{ $medicamento->stock_minimo }})">{{ $stock }}</span>
                                @else
                                    <span class="badge bg-success-subtle text-success-emphasis">{{ $stock }}</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('medicamentos.show', $medicamento) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle"><i class="bi bi-eye"></i></a>
                                @if (auth()->user()->puedeOperarVentas() && $stock > 0)
                                    <a href="{{ route('ventas.create', ['medicamento_id' => $medicamento->id]) }}" class="btn btn-sm btn-outline-primary" title="Vender"><i class="bi bi-cart-plus"></i></a>
                                @endif
                                @if (auth()->user()->puedeGestionarInventario())
                                    <a href="{{ route('medicamentos.edit', $medicamento) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                @endif
                                @if (auth()->user()->esAdministrador())
                                    <x-btn-eliminar :accion="route('medicamentos.destroy', $medicamento)" :nombre="$medicamento->nombre_comercial" />
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($medicamentos->hasPages())
        <div class="d-flex justify-content-center mt-4" data-paginacion>{{ $medicamentos->links() }}</div>
    @endif
@endif
