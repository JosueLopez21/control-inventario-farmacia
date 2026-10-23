@php($user = auth()->user())
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Panel') · FarmaSalud</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        :root {
            --farma-verde: #0f766e;
            --farma-verde-oscuro: #0b5d57;
            --farma-azul: #1d4ed8;
            --farma-fondo: #f2f6f7;
            --farma-sidebar: linear-gradient(180deg, #0f766e 0%, #115e59 55%, #0c4a6e 100%);
        }
        body { background: var(--farma-fondo); font-family: "Segoe UI", system-ui, -apple-system, sans-serif; color: #1f2937; }

        .app-sidebar {
            width: 264px; min-height: 100vh; position: fixed; inset: 0 auto 0 0; z-index: 1040;
            background: var(--farma-sidebar); color: #e5f4f2; display: flex; flex-direction: column;
        }
        .sidebar-brand { display: flex; align-items: center; gap: .75rem; padding: 1.25rem 1.15rem; border-bottom: 1px solid rgba(255,255,255,.12); }
        .sidebar-brand .brand-icon { width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,.15); display: grid; place-items: center; font-size: 1.3rem; }
        .sidebar-brand strong { display: block; font-size: 1.05rem; letter-spacing: .3px; }
        .sidebar-brand small { color: rgba(255,255,255,.65); font-size: .72rem; }

        .sidebar-nav { flex: 1; padding: .85rem .6rem; overflow-y: auto; }
        .nav-heading { color: rgba(255,255,255,.45); font-size: .68rem; text-transform: uppercase; letter-spacing: 1px; margin: .4rem .6rem .5rem; }
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,.82); border-radius: 10px; padding: .6rem .75rem; margin-bottom: .18rem;
            display: flex; align-items: center; gap: .7rem; font-size: .92rem; transition: background .15s ease, color .15s ease;
        }
        .sidebar-nav .nav-link i { font-size: 1.08rem; width: 1.25rem; text-align: center; }
        .sidebar-nav .nav-link:hover { background: rgba(255,255,255,.12); color: #fff; }
        .sidebar-nav .nav-link.active { background: #fff; color: var(--farma-verde-oscuro); font-weight: 600; box-shadow: 0 6px 18px rgba(0,0,0,.18); }

        .sidebar-footer { padding: .9rem 1rem; border-top: 1px solid rgba(255,255,255,.12); }
        .avatar-inicial { width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,.2); display: grid; place-items: center; font-weight: 700; color: #fff; }

        .app-main { margin-left: 264px; min-height: 100vh; display: flex; flex-direction: column; }
        .app-topbar { background: #fff; border-bottom: 1px solid #e5ecec; padding: .7rem 1.4rem; position: sticky; top: 0; z-index: 1030; }
        .app-content { padding: 1.5rem; }

        .card { border: 0; border-radius: 14px; box-shadow: 0 4px 18px rgba(15, 118, 110, .08); }
        .card-header { background: #fff; border-bottom: 1px solid #eef2f2; border-radius: 14px 14px 0 0 !important; font-weight: 600; }
        .table > :not(caption) > * > * { padding: .65rem .7rem; vertical-align: middle; }
        .table thead th { background: #f6faf9; color: #47615e; font-size: .78rem; text-transform: uppercase; letter-spacing: .4px; }

        .metric-card { color: #fff; border-radius: 16px; padding: 1.15rem 1.25rem; height: 100%; position: relative; overflow: hidden; }
        .metric-card .metric-icon { position: absolute; right: 12px; bottom: 6px; font-size: 2.6rem; opacity: .18; }
        .metric-card .metric-value { font-size: 1.65rem; font-weight: 700; line-height: 1.1; }
        .metric-card .metric-label { font-size: .82rem; opacity: .9; }
        .metric-verde { background: linear-gradient(135deg, #0f766e, #14b8a6); }
        .metric-azul { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
        .metric-ambar { background: linear-gradient(135deg, #b45309, #f59e0b); }
        .metric-rojo { background: linear-gradient(135deg, #b91c1c, #ef4444); }
        .metric-gris { background: linear-gradient(135deg, #475569, #94a3b8); }

        .btn-primary { background: var(--farma-verde); border-color: var(--farma-verde); }
        .btn-primary:hover, .btn-primary:focus { background: var(--farma-verde-oscuro); border-color: var(--farma-verde-oscuro); }
        .btn-outline-primary { color: var(--farma-verde); border-color: var(--farma-verde); }
        .btn-outline-primary:hover { background: var(--farma-verde); border-color: var(--farma-verde); }
        .text-farma { color: var(--farma-verde) !important; }
        .bg-farma-soft { background: #e6f4f1; }

        .form-label { font-weight: 600; font-size: .86rem; color: #374151; }
        .form-control:focus, .form-select:focus { border-color: #14b8a6; box-shadow: 0 0 0 .2rem rgba(20,184,166,.15); }

        .page-link { color: var(--farma-verde); }
        .page-item.active .page-link { background: var(--farma-verde); border-color: var(--farma-verde); }
        .pagination { margin-bottom: 0; }

        .empty-state { text-align: center; padding: 3rem 1rem; color: #6b7280; }
        .empty-state i { font-size: 3rem; color: #cbd5d3; }

        .flash-alert { animation: fadeIn .35s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }

        .badge-estado { font-size: .74rem; padding: .38rem .6rem; font-weight: 600; border-radius: 999px; }
        .badge-vencido { background: #fee2e2; color: #b91c1c; }
        .badge-porvencer { background: #fef3c7; color: #92400e; }
        .badge-vigente { background: #d1fae5; color: #047857; }
        .badge-agotado { background: #e5e7eb; color: #4b5563; }

        @media (max-width: 991.98px) {
            .app-main { margin-left: 0; }
        }
        @media print {
            .app-sidebar, .app-topbar, .no-print, .btn { display: none !important; }
            .app-main { margin-left: 0; }
            .app-content { padding: 0; }
            body { background: #fff; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/feria.css') }}?v={{ filemtime(public_path('css/feria.css')) }}">
    @if (session('animacion'))
        <link rel="stylesheet" href="{{ asset('css/animaciones.css') }}?v={{ filemtime(public_path('css/animaciones.css')) }}">
    @endif
    @stack('estilos')
</head>
<body>
    <aside class="app-sidebar d-none d-lg-flex">
        @include('layouts.partials.menu')
    </aside>

    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="menuMovil" style="width: 264px; background: var(--farma-sidebar) !important;">
        <div class="offcanvas-header pb-0">
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            @include('layouts.partials.menu')
        </div>
    </div>

    <div class="app-main">
        <header class="app-topbar d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuMovil">
                <i class="bi bi-list"></i>
            </button>
            <div class="me-auto">
                <h1 class="h5 mb-0 fw-bold text-farma">@yield('titulo', 'Panel')</h1>
                @hasSection('subtitulo')
                    <div class="small text-muted">@yield('subtitulo')</div>
                @endif
            </div>
            <button type="button" class="btn btn-light border btn-presentacion d-flex align-items-center gap-2" data-presentacion-toggle aria-pressed="false" title="Activar modo presentación">
                <i class="bi bi-easel"></i>
                <span class="d-none d-md-inline" data-presentacion-texto>Presentación</span>
            </button>
            <div class="dropdown">
                <button class="btn btn-light border d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar-inicial" style="width:30px;height:30px;background:var(--farma-verde);font-size:.85rem;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="d-none d-sm-inline text-start">
                        <span class="d-block small fw-semibold lh-1">{{ $user->name }}</span>
                        <span class="d-block text-muted" style="font-size:.7rem;">{{ $user->rol_etiqueta }}</span>
                    </span>
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="{{ route('perfil.password') }}"><i class="bi bi-key me-2"></i>Cambiar contraseña</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Salir</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="app-content flex-grow-1">
            @if (session('exito') && ! session('animacion'))
                <div class="alert alert-success alert-dismissible fade show flash-alert" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('exito') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show flash-alert" role="alert">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @yield('contenido')
        </main>
    </div>

    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-trash3 text-danger me-2"></i>Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">¿Seguro que deseas eliminar <strong id="modalEliminarNombre">este registro</strong>?</p>
                    <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="modalEliminarForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (session('animacion'))
        <x-animacion-exito :tipo="session('animacion')" :datos="session('animacion_datos', [])" />
    @endif

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/feria.js') }}?v={{ filemtime(public_path('js/feria.js')) }}"></script>
    <script>
        document.querySelectorAll('.flash-alert').forEach(function (el) {
            setTimeout(function () {
                bootstrap.Alert.getOrCreateInstance(el).close();
            }, 5000);
        });

        document.addEventListener('click', function (event) {
            const boton = event.target.closest('[data-eliminar]');
            if (!boton) return;
            document.getElementById('modalEliminarNombre').textContent = boton.dataset.nombre || 'este registro';
            document.getElementById('modalEliminarForm').setAttribute('action', boton.dataset.eliminar);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminar')).show();
        });
    </script>
    @if (session('animacion'))
        <script src="{{ asset('js/animaciones.js') }}?v={{ filemtime(public_path('js/animaciones.js')) }}"></script>
    @endif
    @stack('scripts')
</body>
</html>
