@extends('layouts.app')

@section('titulo', 'Usuarios')
@section('subtitulo', 'Gestión de usuarios y roles del sistema')

@section('contenido')
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <span><i class="bi bi-people me-2 text-farma"></i>Usuarios ({{ $usuarios->total() }})</span>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('usuarios.index') }}" class="d-flex gap-2">
                <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar usuario">
                <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <a href="{{ route('usuarios.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-person-plus me-1"></i>Nuevo usuario</a>
        </div>
    </div>
    <div class="card-body p-0">
        @if ($usuarios->isEmpty())
            <x-empty-state icono="bi-people" titulo="No hay usuarios" mensaje="Crea el primer usuario del sistema." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th class="text-center">Estado</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $usuario->name }}
                                    @if ($usuario->id === auth()->id())<span class="badge bg-farma-soft text-farma ms-1">Tú</span>@endif
                                </td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    <span class="badge
                                        @if ($usuario->esAdministrador()) bg-primary
                                        @elseif ($usuario->esFarmaceutico()) bg-success
                                        @else bg-info text-dark @endif">
                                        {{ $usuario->rol_etiqueta }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($usuario->activo)
                                        <span class="badge bg-success-subtle text-success-emphasis">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-warning" title="Restablecer contraseña"
                                            data-reset-url="{{ route('usuarios.password', $usuario) }}"
                                            data-reset-nombre="{{ $usuario->name }}">
                                        <i class="bi bi-key"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if ($usuarios->hasPages())
        <div class="card-footer bg-white">{{ $usuarios->links() }}</div>
    @endif
</div>

<div class="modal fade" id="modalReset" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formReset" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-key text-warning me-2"></i>Restablecer contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Nueva contraseña para <strong id="resetNombre"></strong>.</p>
                    <div class="mb-3">
                        <label class="form-label" for="resetPassword">Nueva contraseña</label>
                        <input type="password" name="password" id="resetPassword" class="form-control" required>
                        <div class="form-text">Mínimo 8 caracteres.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="resetPasswordConfirm">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="resetPasswordConfirm" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-key me-1"></i>Restablecer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (evento) {
        const boton = evento.target.closest('[data-reset-url]');
        if (!boton) return;
        document.getElementById('formReset').setAttribute('action', boton.dataset.resetUrl);
        document.getElementById('resetNombre').textContent = boton.dataset.resetNombre;
        document.getElementById('resetPassword').value = '';
        document.getElementById('resetPasswordConfirm').value = '';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalReset')).show();
    });
</script>
@endpush
