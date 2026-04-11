@extends('layouts.app')
@section('title', $usuario->name . ' — Usuarios API')
@section('page-title', 'Detalle de usuario')

@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.api-usuarios.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
        <div>
            <h1 class="page-header-title mb-0">{{ $usuario->name }}</h1>
            <p class="page-header-sub mb-0">{{ $usuario->email }}</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm {{ $usuario->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
            onclick="toggleUsuario({{ $usuario->id }}, {{ $usuario->is_active ? 'true' : 'false' }})">
            <i class="bi {{ $usuario->is_active ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>
            {{ $usuario->is_active ? 'Desactivar' : 'Activar' }}
        </button>
        <button class="btn btn-sm btn-outline-danger"
            onclick="eliminarUsuario({{ $usuario->id }}, '{{ addslashes($usuario->name) }}')">
            <i class="bi bi-trash me-1"></i>Eliminar
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Info -->
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-person-circle me-2 text-primary"></i>Información</div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        @if($usuario->is_active)
                            <span class="badge bg-success-subtle text-success">Activo</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                        @endif
                    </dd>
                    <dt class="col-5 text-muted">Registrado</dt>
                    <dd class="col-7">{{ $usuario->created_at->format('d/m/Y H:i') }}</dd>
                    <dt class="col-5 text-muted">Token</dt>
                    <dd class="col-7">
                        @if($usuario->api_token)
                            <span class="badge bg-success-subtle text-success">Generado</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Sin token</span>
                        @endif
                    </dd>
                    <dt class="col-5 text-muted">Consultas</dt>
                    <dd class="col-7 fw-semibold">{{ number_format($totalConsultas) }}</dd>
                    <dt class="col-5 text-muted">Hoy</dt>
                    <dd class="col-7">{{ $hoy }}</dd>
                    <dt class="col-5 text-muted">Esta semana</dt>
                    <dd class="col-7">{{ $semana }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Top endpoints -->
    <div class="col-12 col-md-8">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-bar-chart me-2 text-primary"></i>Endpoints más usados</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Endpoint</th>
                            <th class="text-center">Consultas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topEndpoints as $ep)
                        <tr>
                            <td><code class="small">{{ $ep->endpoint }}</code></td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $ep->total }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3 small">Sin actividad</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Query log -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-clock-history me-2 text-primary"></i>Historial de consultas</span>
        <span class="small text-muted">{{ number_format($totalConsultas) }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th>Endpoint</th>
                    <th>IP</th>
                    <th class="text-center">Código</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="small text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $log->method }}</span></td>
                    <td><code class="small">{{ $log->endpoint }}</code></td>
                    <td class="small text-muted">{{ $log->ip }}</td>
                    <td class="text-center">
                        <span class="badge {{ $log->response_code >= 400 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                            {{ $log->response_code ?? '—' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Sin consultas registradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer d-flex justify-content-end py-2">
        {{ $logs->links() }}
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
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: esActivo ? '#e53e3e' : '#38a169',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.post(`/admin/api-usuarios/${id}/toggle`, { _token: $('meta[name=csrf-token]').attr('content') })
            .done(() => location.reload())
            .fail(() => Swal.fire('Error', 'No se pudo actualizar.', 'error'));
    });
}

function eliminarUsuario(id, nombre) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        html: `Se eliminará <strong>${nombre}</strong> y todo su historial.`,
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
        }).done(() => window.location = '/admin/api-usuarios')
          .fail(() => Swal.fire('Error', 'No se pudo eliminar.', 'error'));
    });
}
</script>
@endpush
