@extends('layouts.app')
@section('title', 'Logs de consultas — SIMM CENADA')
@section('page-title', 'Logs de consultas API')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">Logs de consultas</h1>
        <p class="page-header-sub">Historial completo de llamadas a la API</p>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-activity"></i></div>
            <div class="stat-value">{{ number_format($totalTotal) }}</div>
            <div class="stat-label">Consultas totales</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-calendar-day"></i></div>
            <div class="stat-value">{{ number_format($totalHoy) }}</div>
            <div class="stat-label">Consultas hoy</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-people"></i></div>
            <div class="stat-value">{{ $totalUsuarios }}</div>
            <div class="stat-label">Usuarios activos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-link-45deg"></i></div>
            <div class="stat-value">{{ $topEndpoints->count() }}</div>
            <div class="stat-label">Endpoints usados</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Top endpoints -->
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-bar-chart me-2 text-primary"></i>Top endpoints</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Endpoint</th>
                            <th class="text-center">Llamadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topEndpoints as $ep)
                        <tr>
                            <td><code class="small">{{ $ep->endpoint }}</code></td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ number_format($ep->total) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top usuarios -->
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-trophy me-2 text-primary"></i>Usuarios más activos</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th class="text-center">Consultas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topUsuarios as $u)
                        <tr>
                            <td>
                                <a href="{{ route('admin.api-usuarios.show', $u->user_id) }}" class="text-decoration-none">
                                    {{ $u->user->name ?? 'Usuario eliminado' }}
                                </a>
                                <div class="small text-muted">{{ $u->user->email ?? '' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ number_format($u->total) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Full log table -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-journal-text me-2 text-primary"></i>Registro completo</span>
        <span class="small text-muted">{{ number_format($totalTotal) }} entradas</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
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
                    <td>
                        @if($log->user)
                            <a href="{{ route('admin.api-usuarios.show', $log->user_id) }}" class="text-decoration-none small">
                                {{ $log->user->name }}
                            </a>
                        @else
                            <span class="small text-muted">—</span>
                        @endif
                    </td>
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
                <tr><td colspan="6" class="text-center text-muted py-4">Sin consultas registradas aún</td></tr>
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
