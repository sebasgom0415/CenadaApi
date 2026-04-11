@extends('layouts.publico')

@section('title', 'SIMM CENADA — Precios Mayoristas')

@section('content')

<!-- Hero -->
<div class="pub-hero mb-4">
    <div class="row align-items-center">
        <div class="col-12 col-md-8">
            <h1><i class="bi bi-graph-up-arrow me-2"></i>Precios de Mayorista a Minorista</h1>
            <p>Información oficial del Sistema de Información de Mercados Mayoristas (SIMM) — CENADA, Heredia, Costa Rica</p>
        </div>
        <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
            @if($boletinActivo)
                <div style="color:rgba(255,255,255,0.7); font-size:0.82rem;">Datos del boletín</div>
                <div style="font-size:1.4rem; font-weight:700;">{{ $boletinActivo->fecha_plaza->format('d/m/Y') }}</div>
                <div style="color:rgba(255,255,255,0.6); font-size:0.78rem;">{{ $precios->count() }} productos disponibles</div>
            @endif
        </div>
    </div>
</div>

@if($totalBoletines === 0)
<div class="pub-card">
    <div class="pub-card-body text-center py-5">
        <i class="bi bi-inbox" style="font-size:3rem;color:#cbd5e0;"></i>
        <h5 class="mt-3 text-muted">Sin datos disponibles</h5>
        <p style="color:#718096;">Aún no se han publicado boletines de precios.</p>
    </div>
</div>
@else

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="pub-stat">
            <div class="pub-stat-icon blue"><i class="bi bi-file-earmark-pdf"></i></div>
            <div>
                <div class="pub-stat-value">{{ $totalBoletines }}</div>
                <div class="pub-stat-label">Boletines publicados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="pub-stat">
            <div class="pub-stat-icon green"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="pub-stat-value">{{ $totalProductos }}</div>
                <div class="pub-stat-label">Productos distintos</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="pub-stat">
            <div class="pub-stat-icon orange"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="pub-stat-value">{{ $boletinActivo?->fecha_plaza->format('d/m/Y') ?? '—' }}</div>
                <div class="pub-stat-label">Último boletín</div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filter-bar">
    <form method="GET" action="{{ route('publico.index') }}" id="formFiltros">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.78rem;color:#718096;">FECHA DE PLAZA</label>
                <select name="boletin_id" class="form-select form-select-sm" onchange="document.getElementById('formFiltros').submit()">
                    @foreach($fechasDisponibles as $id => $fecha)
                        <option value="{{ $id }}" {{ $boletinActivo?->id == $id ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.78rem;color:#718096;">UNIDAD</label>
                <select name="unidad" class="form-select form-select-sm" onchange="document.getElementById('formFiltros').submit()">
                    <option value="">Todas las unidades</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u }}" {{ request('unidad') == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold" style="font-size:0.78rem;color:#718096;">BUSCAR PRODUCTO</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0" placeholder="Ej: Tomate, Papa...">
                    <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
                </div>
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end">
                @if(request('buscar') || request('unidad'))
                    <a href="{{ route('publico.index', ['boletin_id' => $boletinActivo?->id]) }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-x me-1"></i>Limpiar
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Tabs: Tabla / Gráficos -->
<div class="pub-tabs">
    <button class="pub-tab active" data-tab="tabla"><i class="bi bi-table me-1"></i>Tabla de precios</button>
    <button class="pub-tab" data-tab="graficos"><i class="bi bi-graph-up me-1"></i>Gráficos</button>
</div>

<!-- Tab: Tabla -->
<div class="pub-tab-pane active" id="tab-tabla">
    <div class="pub-card">
        <div class="pub-card-header">
            <span>Precios — {{ $boletinActivo?->fecha_plaza->format('d/m/Y') }}</span>
            <span style="font-size:0.78rem;color:#718096;" id="contadorResultados">{{ $precios->count() }} productos</span>
        </div>
        <div class="table-responsive">
            <table class="pub-table" id="tablaPublica">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Unidad</th>
                        <th class="text-end">Mínimo</th>
                        <th class="text-end">Máximo</th>
                        <th class="text-end">Moda</th>
                        <th class="text-end">Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($precios as $precio)
                    <tr>
                        <td class="fw-semibold">{{ $precio->producto->nombre }}</td>
                        <td><span class="pub-badge">{{ $precio->producto->unidad_comercializacion }}</span></td>
                        <td class="text-end">₡{{ number_format($precio->precio_minimo, 2) }}</td>
                        <td class="text-end">₡{{ number_format($precio->precio_maximo, 2) }}</td>
                        <td class="text-end">₡{{ number_format($precio->moda, 2) }}</td>
                        <td class="text-end fw-semibold" style="color:#4f6ef7;">₡{{ number_format($precio->promedio, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5" style="color:#718096;">
                            <i class="bi bi-search d-block mb-2" style="font-size:2rem;"></i>
                            Sin resultados para los filtros aplicados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab: Gráficos -->
<div class="pub-tab-pane" id="tab-graficos">
    <div class="row g-3">
        <!-- Evolución de precio -->
        <div class="col-12 col-lg-8">
            <div class="pub-card">
                <div class="pub-card-header">
                    <span><i class="bi bi-graph-up me-2" style="color:#4f6ef7;"></i>Evolución de Precio Promedio</span>
                    <select id="selectProductoPub" class="form-select form-select-sm" style="max-width:220px;">
                        @foreach($productosLista as $p)
                            <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pub-card-body">
                    <canvas id="chartEvolucionPub" height="110"></canvas>
                </div>
            </div>
        </div>

        <!-- Donut unidades -->
        <div class="col-12 col-lg-4">
            <div class="pub-card">
                <div class="pub-card-header">
                    <span><i class="bi bi-pie-chart me-2" style="color:#4f6ef7;"></i>Por Unidad de Medida</span>
                </div>
                <div class="pub-card-body">
                    <canvas id="chartUnidadesPub" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Barras: top 10 más caros (promedio) -->
        <div class="col-12">
            <div class="pub-card">
                <div class="pub-card-header">
                    <span><i class="bi bi-bar-chart me-2" style="color:#4f6ef7;"></i>Top 10 — Precios Más Altos (Promedio)</span>
                    <span style="font-size:0.78rem;color:#718096;">Boletín {{ $boletinActivo?->fecha_plaza->format('d/m/Y') }}</span>
                </div>
                <div class="pub-card-body">
                    <canvas id="chartTop10" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endif

@endsection

@push('scripts')
@if($totalBoletines > 0)
<script>
// ── Tabs ────────────────────────────────────────────────────
$('.pub-tab').on('click', function () {
    $('.pub-tab').removeClass('active');
    $('.pub-tab-pane').removeClass('active');
    $(this).addClass('active');
    $('#tab-' + $(this).data('tab')).addClass('active');
});

// ── Chart evolución ─────────────────────────────────────────
const ctxEv = document.getElementById('chartEvolucionPub').getContext('2d');
let chartEv = new Chart(ctxEv, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [
            {
                label: 'Máximo',
                data: @json($chartData['maximo']),
                borderColor: '#e53e3e',
                backgroundColor: 'transparent',
                borderWidth: 1.5,
                borderDash: [5,3],
                pointRadius: 3,
                tension: 0.3
            },
            {
                label: 'Promedio',
                data: @json($chartData['promedio']),
                borderColor: '#4f6ef7',
                backgroundColor: 'rgba(79,110,247,0.08)',
                borderWidth: 2.5,
                pointRadius: 4,
                pointBackgroundColor: '#4f6ef7',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Mínimo',
                data: @json($chartData['minimo']),
                borderColor: '#38a169',
                backgroundColor: 'transparent',
                borderWidth: 1.5,
                borderDash: [5,3],
                pointRadius: 3,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
        scales: {
            y: { grid: { color: '#f0f2f5' }, ticks: { callback: v => '₡' + v.toLocaleString('es-CR') } },
            x: { grid: { display: false } }
        }
    }
});

$('#selectProductoPub').on('change', function () {
    $.get('{{ route("publico.chart") }}', { producto_id: $(this).val() }, function (res) {
        chartEv.data.labels                  = res.labels;
        chartEv.data.datasets[0].data        = res.maximo;
        chartEv.data.datasets[1].data        = res.promedio;
        chartEv.data.datasets[2].data        = res.minimo;
        chartEv.update();
    });
});

// ── Chart donut unidades ────────────────────────────────────
new Chart(document.getElementById('chartUnidadesPub').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: @json($unidadesChart['labels']),
        datasets: [{
            data: @json($unidadesChart['data']),
            backgroundColor: ['#4f6ef7','#38a169','#d69e2e','#e53e3e','#805ad5','#319795','#dd6b20','#718096'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } } }
    }
});

// ── Chart barras top 10 ─────────────────────────────────────
const top10 = @json($precios->sortByDesc('promedio')->take(10)->values()->map(fn($p) => [
    'nombre'   => $p->producto->nombre,
    'promedio' => $p->promedio
]));

new Chart(document.getElementById('chartTop10').getContext('2d'), {
    type: 'bar',
    data: {
        labels: top10.map(p => p.nombre),
        datasets: [{
            label: 'Promedio (₡)',
            data: top10.map(p => p.promedio),
            backgroundColor: 'rgba(79,110,247,0.75)',
            borderColor: '#4f6ef7',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: '#f0f2f5' }, ticks: { callback: v => '₡' + v.toLocaleString('es-CR') } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
</script>
@endif
@endpush
