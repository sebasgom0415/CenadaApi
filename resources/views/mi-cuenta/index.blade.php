<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi cuenta — SIMM CENADA</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/publico.css') }}">
    <style>
        .cuenta-nav { background:#1e2130; color:#a0aec0; padding:14px 24px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .cuenta-brand { display:flex; align-items:center; gap:10px; color:white; font-weight:700; font-size:1rem; text-decoration:none; }
        .cuenta-brand-icon { width:32px; height:32px; background:#4f6ef7; border-radius:8px; display:flex; align-items:center; justify-content:center; color:white; font-size:1rem; }
        code { background:#f0f2f5; padding:2px 6px; border-radius:4px; font-size:.85rem; }
    </style>
</head>
<body>

<nav class="cuenta-nav">
    <a href="{{ route('publico.index') }}" class="cuenta-brand">
        <div class="cuenta-brand-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
        SIMM CENADA
    </a>
    <div class="d-flex align-items-center gap-3">
        <span class="small">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Salir
            </button>
        </form>
    </div>
</nav>

<div class="pub-content" style="max-width:900px; margin:0 auto;">

    <div class="page-header mt-2">
        <div>
            <h1 class="page-header-title">Mi cuenta API</h1>
            <p class="page-header-sub">Gestiona tu token de acceso y consulta tu historial de uso</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="pub-stat">
                <div class="pub-stat-icon blue"><i class="bi bi-activity"></i></div>
                <div>
                    <div class="pub-stat-value">{{ number_format($totalConsultas) }}</div>
                    <div class="pub-stat-label">Consultas totales</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="pub-stat">
                <div class="pub-stat-icon green"><i class="bi bi-calendar-day"></i></div>
                <div>
                    <div class="pub-stat-value">{{ $hoy }}</div>
                    <div class="pub-stat-label">Consultas hoy</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="pub-stat">
                <div class="pub-stat-icon orange"><i class="bi bi-person-check"></i></div>
                <div>
                    <div class="pub-stat-value">Activa</div>
                    <div class="pub-stat-label">Estado de cuenta</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Token -->
        <div class="col-12 col-md-5">
            <div class="pub-card h-100">
                <div class="pub-card-header"><i class="bi bi-key me-2 text-primary"></i>Tu API Token</div>
                <div class="pub-card-body">

                    @if(session('token'))
                        <div class="alert alert-success border-0 mb-3" style="background:#f0fff4;">
                            <div class="fw-semibold mb-1"><i class="bi bi-check-circle me-1"></i>Token generado — cópialo ahora</div>
                            <p class="small text-muted mb-2">Solo se muestra una vez.</p>
                            <div class="input-group">
                                <input type="text" id="tokenVal" class="form-control form-control-sm font-monospace" value="{{ session('token') }}" readonly>
                                <button class="btn btn-outline-secondary btn-sm" onclick="copyToken()"><i class="bi bi-clipboard"></i></button>
                            </div>
                        </div>
                    @endif

                    <p class="text-muted small mb-3">Tu token da acceso a todos los endpoints de la API. Trátalo como una contraseña.</p>

                    <form method="POST" action="{{ route('mi-cuenta.regenerar') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100"
                            onclick="return confirm('¿Regenerar token? El token anterior dejará de funcionar.')">
                            <i class="bi bi-arrow-clockwise me-1"></i>Regenerar token
                        </button>
                    </form>

                    <hr class="my-3">
                    <p class="small text-muted mb-1 fw-semibold">Cómo usarlo:</p>
                    <pre class="small mb-1" style="background:#f7fafc;padding:8px;border-radius:6px;">Authorization: Bearer {token}</pre>
                    <pre class="small mb-0" style="background:#f7fafc;padding:8px;border-radius:6px;">?api_token={token}</pre>
                </div>
            </div>
        </div>

        <!-- Endpoints -->
        <div class="col-12 col-md-7">
            <div class="pub-card h-100">
                <div class="pub-card-header"><i class="bi bi-book me-2 text-primary"></i>Endpoints disponibles</div>
                <div class="table-responsive">
                    <table class="pub-table">
                        <thead>
                            <tr>
                                <th>Endpoint</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><code>GET /api/boletines</code></td><td class="small text-muted">Fechas disponibles</td></tr>
                            <tr><td><code>GET /api/boletines/latest</code></td><td class="small text-muted">Último boletín completo</td></tr>
                            <tr><td><code>GET /api/boletines/{fecha}</code></td><td class="small text-muted">Por fecha YYYY-MM-DD</td></tr>
                            <tr><td><code>GET /api/boletines/{fecha}/producto/{nombre}</code></td><td class="small text-muted">Producto en una fecha</td></tr>
                            <tr><td><code>GET /api/productos</code></td><td class="small text-muted">Catálogo de productos</td></tr>
                            <tr><td><code>GET /api/productos/{id}/historial</code></td><td class="small text-muted">Historial de precios</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de consultas -->
    <div class="pub-card">
        <div class="pub-card-header">
            <span><i class="bi bi-clock-history me-2 text-primary"></i>Historial de consultas</span>
            <span class="small text-muted">{{ number_format($totalConsultas) }} total</span>
        </div>
        <div class="table-responsive">
            <table class="pub-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Método</th>
                        <th>Endpoint</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="small text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td><span class="pub-badge">{{ $log->method }}</span></td>
                        <td><code>{{ $log->endpoint }}</code></td>
                        <td class="small text-muted">{{ $log->ip }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Sin consultas registradas aún</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="pub-card-body py-2 d-flex justify-content-end border-top">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>

<br>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>
<script>
function copyToken() {
    navigator.clipboard.writeText(document.getElementById('tokenVal').value).then(() => {
        Swal.fire({ icon: 'success', title: 'Copiado', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
    });
}
</script>
</body>
</html>
