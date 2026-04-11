@extends('layouts.app')

@section('title', 'API Token — SIMM CENADA')
@section('page-title', 'API REST')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">API REST — SIMM CENADA</h1>
        <p class="page-header-sub">Gestiona tu token de acceso y consulta la documentación de endpoints</p>
    </div>
</div>

<div class="row g-4">
    <!-- Token -->
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-key me-2 text-primary"></i>Tu API Token</div>
            <div class="card-body">

                @if(session('token'))
                    <div class="alert alert-success border-0" style="background:#f0fff4;">
                        <div class="fw-semibold mb-2"><i class="bi bi-check-circle me-1"></i>Token generado — cópialo ahora</div>
                        <p class="small text-muted mb-2">Este token solo se muestra una vez. Guárdalo en un lugar seguro.</p>
                        <div class="input-group">
                            <input type="text" id="tokenValue" class="form-control form-control-sm font-monospace" value="{{ session('token') }}" readonly>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyToken()" title="Copiar">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                @elseif(session('success'))
                    <div class="alert alert-info border-0 small">{{ session('success') }}</div>
                @endif

                <p class="text-muted small mb-3">
                    {{ $tieneToken ? 'Ya tienes un token activo. Puedes regenerarlo o revocarlo.' : 'No tienes un token activo. Genera uno para usar la API.' }}
                </p>

                <form method="POST" action="{{ route('admin.api.token.generate') }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        {{ $tieneToken ? 'Regenerar token' : 'Generar token' }}
                    </button>
                </form>

                @if($tieneToken)
                <form method="POST" action="{{ route('admin.api.token.revoke') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm"
                        onclick="return confirm('¿Revocar el token? Las integraciones dejarán de funcionar.')">
                        <i class="bi bi-x-circle me-1"></i>Revocar token
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Cómo usar -->
        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-info-circle me-2 text-primary"></i>Cómo autenticar</div>
            <div class="card-body">
                <p class="small text-muted mb-2">Opción 1 — Header HTTP:</p>
                <pre class="bg-light rounded p-2 small mb-3">Authorization: Bearer {tu_token}</pre>

                <p class="small text-muted mb-2">Opción 2 — Query string:</p>
                <pre class="bg-light rounded p-2 small mb-0">GET /api/boletines?api_token={tu_token}</pre>
            </div>
        </div>
    </div>

    <!-- Documentación -->
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-book me-2 text-primary"></i>Endpoints disponibles</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:80px;">Método</th>
                                <th>Endpoint</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $base = request()->getSchemeAndHttpHost() . '/cenada/public/api';
                            $endpoints = [
                                ['GET', '/boletines',                            'Lista todas las fechas disponibles'],
                                ['GET', '/boletines/latest',                     'Último boletín con todos sus precios'],
                                ['GET', '/boletines/{fecha}',                    'Boletín por fecha (YYYY-MM-DD)'],
                                ['GET', '/boletines/{fecha}/producto/{nombre}',  'Precio de un producto en una fecha'],
                                ['GET', '/productos',                            'Catálogo de todos los productos'],
                                ['GET', '/productos/{id}/historial',             'Historial de precios de un producto'],
                            ];
                            @endphp
                            @foreach($endpoints as [$method, $path, $desc])
                            <tr>
                                <td><span class="badge bg-primary">{{ $method }}</span></td>
                                <td><code class="small">{{ $path }}</code></td>
                                <td class="small text-muted">{{ $desc }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ejemplos de respuesta -->
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-braces me-2 text-primary"></i>Ejemplos de respuesta</span>
                <div class="d-flex gap-1">
                    <button class="btn btn-xs btn-outline-secondary active" style="font-size:.75rem;padding:2px 8px;" onclick="showExample('boletines')">Boletines</button>
                    <button class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px;" onclick="showExample('producto')">Por fecha</button>
                    <button class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px;" onclick="showExample('historial')">Historial</button>
                </div>
            </div>
            <div class="card-body p-0">
                <pre id="ex-boletines" class="m-0 p-3 small" style="background:#1e2130;color:#a0aec0;border-radius:0 0 10px 10px;overflow-x:auto;">{
  "success": true,
  "total": 2,
  "data": [
    {
      "id": 2,
      "fecha_plaza": "2026-04-11",
      "plaza": "CENADA",
      "total_productos": 79
    },
    {
      "id": 1,
      "fecha_plaza": "2026-04-10",
      "plaza": "CENADA",
      "total_productos": 79
    }
  ]
}</pre>
                <pre id="ex-producto" class="m-0 p-3 small d-none" style="background:#1e2130;color:#a0aec0;border-radius:0 0 10px 10px;overflow-x:auto;">{
  "success": true,
  "data": {
    "id": 1,
    "fecha_plaza": "2026-04-10",
    "plaza": "CENADA",
    "ubicacion": "Heredia, Costa Rica",
    "total_productos": 79,
    "precios": [
      {
        "producto": "Aguacate criollo",
        "unidad_comercializacion": "Unidad",
        "precio_minimo": 450.00,
        "precio_maximo": 500.00,
        "moda": 500.00,
        "promedio": 490.00
      }
    ]
  }
}</pre>
                <pre id="ex-historial" class="m-0 p-3 small d-none" style="background:#1e2130;color:#a0aec0;border-radius:0 0 10px 10px;overflow-x:auto;">{
  "success": true,
  "producto": "Tomate primera",
  "unidad": "Caja plástica (18 kg)",
  "total": 5,
  "data": [
    {
      "fecha_plaza": "2026-04-06",
      "precio_minimo": 28000.00,
      "precio_maximo": 30000.00,
      "moda": 29000.00,
      "promedio": 29200.00
    },
    {
      "fecha_plaza": "2026-04-10",
      "precio_minimo": 31000.00,
      "precio_maximo": 32000.00,
      "moda": 31000.00,
      "promedio": 31444.44
    }
  ]
}</pre>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function copyToken() {
    const val = document.getElementById('tokenValue').value;
    navigator.clipboard.writeText(val).then(() => {
        Toast.fire({ icon: 'success', title: 'Token copiado' });
    });
}

function showExample(key) {
    document.querySelectorAll('[id^="ex-"]').forEach(el => el.classList.add('d-none'));
    document.getElementById('ex-' + key).classList.remove('d-none');
    document.querySelectorAll('.btn-xs').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}
</script>
@endpush
