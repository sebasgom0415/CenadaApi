@extends('layouts.app')
@section('title', 'Usuarios API — SIMM CENADA')
@section('page-title', 'Usuarios API')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">Usuarios API</h1>
        <p class="page-header-sub">Cuentas registradas para acceso a la API</p>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
            <div class="stat-value">{{ $total }}</div>
            <div class="stat-label">Total usuarios</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-value">{{ $activos }}</div>
            <div class="stat-label">Activos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-person-x-fill"></i></div>
            <div class="stat-value">{{ $inactivos }}</div>
            <div class="stat-label">Inactivos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-activity"></i></div>
            <div class="stat-value">{{ number_format($totalConsultas) }}</div>
            <div class="stat-label">Consultas totales</div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-people me-2 text-primary"></i>Listado de usuarios</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Usuario</th>
                    <th>Registrado</th>
                    <th class="text-center">Consultas</th>
                    <th class="text-center">Último acceso</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $u->name }}</div>
                        <div class="small text-muted">{{ $u->email }}</div>
                    </td>
                    <td class="small text-muted">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                            {{ number_format($u->api_logs_count) }}
                        </span>
                    </td>
                    <td class="text-center small text-muted">
                        @if($u->api_logs_count > 0 && $u->lastLog)
                            {{ $u->lastLog->created_at->diffForHumans() }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($u->is_active)
                            <span class="badge bg-success-subtle text-success">Activo</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('admin.api-usuarios.show', $u) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-sm {{ $u->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                onclick="toggleUsuario({{ $u->id }}, {{ $u->is_active ? 'true' : 'false' }})"
                                title="{{ $u->is_active ? 'Desactivar' : 'Activar' }}">
                                <i class="bi {{ $u->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                onclick="eliminarUsuario({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No hay usuarios API registrados aún</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($usuarios->hasPages())
    <div class="card-footer d-flex justify-content-end py-2">
        {{ $usuarios->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function toggleUsuario(id, esActivo) {
    const accion = esActivo ? 'desactivar' : 'activar';
    Swal.fire({
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} usuario?`,
        text: esActivo ? 'No podrá realizar consultas a la API.' : 'Podrá volver a usar la API.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: esActivo ? '#e53e3e' : '#38a169',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.post(`/admin/api-usuarios/${id}/toggle`, { _token: $('meta[name=csrf-token]').attr('content') })
            .done(() => location.reload())
            .fail(() => Swal.fire('Error', 'No se pudo actualizar el estado.', 'error'));
    });
}

function eliminarUsuario(id, nombre) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        html: `Se eliminará <strong>${nombre}</strong> y todo su historial de consultas.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e53e3e',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: `/admin/api-usuarios/${id}`,
            method: 'DELETE',
            data: { _token: $('meta[name=csrf-token]').attr('content') },
        }).done(() => location.reload())
          .fail(() => Swal.fire('Error', 'No se pudo eliminar el usuario.', 'error'));
    });
}
</script>
@endpush
