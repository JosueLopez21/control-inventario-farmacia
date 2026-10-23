@php
    $user = auth()->user();
    $items = [];

    $items[] = ['ruta' => 'dashboard', 'activo' => 'dashboard', 'icono' => 'bi-speedometer2', 'texto' => 'Dashboard', 'visible' => true];

    $items[] = ['ruta' => 'ventas.index', 'activo' => 'ventas.*', 'icono' => 'bi-cart3', 'texto' => 'Ventas', 'visible' => $user->puedeOperarVentas()];
    $items[] = ['ruta' => 'medicamentos.index', 'activo' => 'medicamentos.*', 'icono' => 'bi-capsule', 'texto' => 'Medicamentos', 'visible' => true];
    $items[] = ['ruta' => 'lotes.index', 'activo' => 'lotes.*', 'icono' => 'bi-box-seam', 'texto' => 'Lotes', 'visible' => $user->puedeGestionarInventario()];
    $items[] = ['ruta' => 'alertas.index', 'activo' => 'alertas.*', 'icono' => 'bi-exclamation-triangle', 'texto' => 'Alertas', 'visible' => true];
    $items[] = ['ruta' => 'juego.index', 'activo' => 'juego.*', 'icono' => 'bi-dice-5', 'texto' => 'Juego', 'visible' => true];
    $items[] = ['ruta' => 'asesor.index', 'activo' => 'asesor.*', 'icono' => 'bi-clipboard2-pulse', 'texto' => 'Asesor', 'visible' => true];
    $items[] = ['ruta' => 'categorias.index', 'activo' => 'categorias.*', 'icono' => 'bi-tags', 'texto' => 'Categorías', 'visible' => $user->puedeGestionarInventario()];
    $items[] = ['ruta' => 'laboratorios.index', 'activo' => 'laboratorios.*', 'icono' => 'bi-building', 'texto' => 'Laboratorios', 'visible' => $user->puedeGestionarInventario()];
    $items[] = ['ruta' => 'reportes.index', 'activo' => 'reportes.*', 'icono' => 'bi-bar-chart-line', 'texto' => 'Reportes', 'visible' => $user->puedeOperarVentas()];
    $items[] = ['ruta' => 'usuarios.index', 'activo' => 'usuarios.*', 'icono' => 'bi-people', 'texto' => 'Usuarios', 'visible' => $user->puedeGestionarUsuarios()];
@endphp

<div class="sidebar-brand">
    <span class="brand-icon"><i class="bi bi-heart-pulse-fill"></i></span>
    <span>
        <strong>FarmaSalud</strong>
        <small>Control de inventario</small>
    </span>
</div>

<nav class="sidebar-nav">
    <p class="nav-heading">Menú principal</p>
    @foreach ($items as $item)
        @if ($item['visible'])
            <a href="{{ route($item['ruta']) }}"
               class="nav-link {{ request()->routeIs($item['activo']) ? 'active' : '' }}">
                <i class="bi {{ $item['icono'] }}"></i>
                <span>{{ $item['texto'] }}</span>
                @if ($item['ruta'] === 'alertas.index')
                    @php
                        $nAlertas = \App\Models\Lote::porVencer(30)->count() + \App\Models\Lote::vencidos()->count();
                    @endphp
                    @if ($nAlertas > 0)
                        <span class="badge rounded-pill bg-danger ms-auto">{{ $nAlertas }}</span>
                    @endif
                @endif
            </a>
        @endif
    @endforeach
</nav>

<div class="sidebar-footer">
    <div class="d-flex align-items-center gap-2">
        <div class="avatar-inicial">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div class="small">
            <div class="fw-semibold text-truncate" style="max-width: 150px;">{{ $user->name }}</div>
            <div class="text-white-50">{{ $user->rol_etiqueta }}</div>
        </div>
    </div>
</div>
