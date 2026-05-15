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
<!-- Filtros simples para celular -->
<div class="filter-bar farmer-filter">
    <form method="GET" action="{{ route('publico.index') }}" id="formFiltros">
        <div class="row g-3">

            <div class="col-12">
                <label class="farmer-label">Fecha de precios</label>
                <select name="boletin_id" class="form-select farmer-input"
                        onchange="document.getElementById('formFiltros').submit()">
                    @foreach($fechasDisponibles as $id => $fecha)
                        <option value="{{ $id }}" {{ $boletinActivo?->id == $id ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="farmer-label">Buscar producto</label>
                <input type="text"
                       name="buscar"
                       value="{{ request('buscar') }}"
                       class="form-control farmer-input"
                       placeholder="Ejemplo: tomate, papa, cebolla">
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary farmer-btn w-100">
                    <i class="bi bi-search me-1"></i> Buscar
                </button>
            </div>

            @if(request('buscar') || request('unidad'))
                <div class="col-12">
                    <a href="{{ route('publico.index', ['boletin_id' => $boletinActivo?->id]) }}"
                       class="btn btn-outline-secondary farmer-btn w-100">
                        Limpiar búsqueda
                    </a>
                </div>
            @endif

        </div>
    </form>
</div>

{{-- Acceso rápido al historial en celular --}}
<div class="d-md-none mb-3">
    <button class="btn btn-historial w-100" onclick="activarTab('historial')">
        <i class="bi bi-clock-history me-2"></i>
        Ver Historial de Boletines
        <span class="hist-count-badge">{{ $totalBoletines }}</span>
    </button>
</div>

<!-- Tabs: Tabla / Gráficos / Historial -->
<div class="pub-tabs">
    <button class="pub-tab active" data-tab="tabla"><i class="bi bi-table me-1"></i>Precios</button>
    <button class="pub-tab" data-tab="graficos"><i class="bi bi-graph-up me-1"></i>Gráficos</button>
    <button class="pub-tab" data-tab="historial">
        <i class="bi bi-clock-history me-1"></i>Historial
        <span class="pub-tab-badge">{{ $totalBoletines }}</span>
    </button>
</div>

<!-- Tab: Tabla -->
<div class="pub-tab-pane active" id="tab-tabla">

    {{-- Tarjetas en celular --}}
    <div class="d-md-none">
        <div class="d-flex flex-column gap-2">
        @forelse($precios as $precio)
        @php
            $nombre   = $precio->producto->nombre;
            $n        = mb_strtolower(str_replace(['á','é','í','ó','ú','ñ'],['a','e','i','o','u','n'], $nombre));
            $emojiMap = [
                'tomate'=>'🍅','papa'=>'🥔','cebolla'=>'🧅','zanahoria'=>'🥕',
                'lechuga'=>'🥬','chile'=>'🌶️','platano'=>'🍌','banano'=>'🍌',
                'mango'=>'🥭','pina'=>'🍍','aguacate'=>'🥑','naranja'=>'🍊',
                'limon'=>'🍋','manzana'=>'🍎','maiz'=>'🌽','elote'=>'🌽',
                'brocoli'=>'🥦','pepino'=>'🥒','yuca'=>'🍠','camote'=>'🍠',
                'repollo'=>'🥬','chayote'=>'🫛','mora'=>'🫐','ajo'=>'🧄',
                'guayaba'=>'🍐','sandia'=>'🍉','fresa'=>'🍓','melon'=>'🍈',
                'espinaca'=>'🥬','coliflor'=>'🥦','loroco'=>'🌸','apio'=>'🌿',
                'remolacha'=>'🫀','rabano'=>'🌶️','jengibre'=>'🫚',
            ];
            $icono = '🌱';
            foreach ($emojiMap as $k => $e) {
                if (str_contains($n, $k)) { $icono = $e; break; }
            }
            $paleta      = ['blue','green','orange','teal','indigo','rose','violet'];
            $avatarColor = $paleta[abs(crc32($nombre)) % count($paleta)];
        @endphp
        <div class="d-flex align-items-start gap-3 p-3 bg-white border rounded-3 shadow-sm">
            <div class="mprod-avatar mprod-avatar--{{ $avatarColor }}">{{ $icono }}</div>
            <div class="flex-grow-1" style="min-width:0">
                <div class="fw-bold lh-sm mb-1">{{ $nombre }}</div>
                <div class="text-muted small mb-2">{{ $precio->producto->unidad_comercializacion }}</div>
                <div class="d-flex flex-wrap gap-2">
                    <div class="mprod-price-item">
                        <span class="mprod-price-label">Mín</span>
                        <span class="mprod-price-val mprod-min">₡{{ number_format($precio->precio_minimo, 0) }}</span>
                    </div>
                    <div class="mprod-price-item">
                        <span class="mprod-price-label">Máx</span>
                        <span class="mprod-price-val mprod-max">₡{{ number_format($precio->precio_maximo, 0) }}</span>
                    </div>
                    <div class="mprod-price-item mprod-price-item--prom">
                        <span class="mprod-price-label">Promedio</span>
                        <span class="mprod-price-val mprod-prom">₡{{ number_format($precio->promedio, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-search d-block mb-2" style="font-size:2rem;"></i>
            Sin resultados para los filtros aplicados
        </div>
        @endforelse
        </div>
    </div>

    {{-- Tabla en escritorio --}}
    <div class="d-none d-md-block table-responsive">
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
                    <td class="text-end fw-semibold" style="color:#4f6ef7;">
                        ₡{{ number_format($precio->promedio, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        Sin resultados para los filtros aplicados
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
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

<!-- Tab: Historial -->
<div class="pub-tab-pane" id="tab-historial">
    <div class="row g-3">
        @foreach($boletinesHistorial as $b)
        @php
            $mes = $b->fecha_plaza->month;
            $gradientes = [
                1  => 'hist-grad-blue',
                2  => 'hist-grad-indigo',
                3  => 'hist-grad-green',
                4  => 'hist-grad-teal',
                5  => 'hist-grad-green',
                6  => 'hist-grad-orange',
                7  => 'hist-grad-orange',
                8  => 'hist-grad-red',
                9  => 'hist-grad-teal',
                10 => 'hist-grad-indigo',
                11 => 'hist-grad-orange',
                12 => 'hist-grad-blue',
            ];
            $gradClass = $gradientes[$mes] ?? 'hist-grad-blue';
            $activo = $boletinActivo?->id === $b->id;
        @endphp
        <div class="col-6 col-sm-4 col-lg-3">
            <a href="{{ route('publico.index', ['boletin_id' => $b->id]) }}"
               class="hist-card h-100 {{ $activo ? 'hist-card-active' : '' }}">
                <div class="hist-card-top {{ $gradClass }}">
                    @if($activo)
                        <span class="hist-badge-activo"><i class="bi bi-check-circle-fill me-1"></i>Activo</span>
                    @endif
                    <div class="hist-icon-wrap">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                    </div>
                    <div class="hist-mes">{{ $b->fecha_plaza->locale('es')->isoFormat('MMMM') }}</div>
                </div>
                <div class="hist-card-body">
                    <div class="hist-fecha">{{ $b->fecha_plaza->format('d/m/Y') }}</div>
                    <div class="hist-plaza">{{ $b->plaza->nombre ?? 'CENADA' }}</div>
                    <div class="hist-meta">
                        <span><i class="bi bi-box-seam me-1"></i>{{ $b->precios_count }} productos</span>
                        <span><i class="bi bi-currency-dollar me-1"></i>₡{{ number_format($b->tipo_cambio_usd, 0) }}</span>
                    </div>
                    <div class="hist-ver">Ver precios <i class="bi bi-arrow-right-short"></i></div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>

@endif

@endsection

@push('scripts')
@if($totalBoletines > 0)
<script>
// ── Tabs ────────────────────────────────────────────────────
function activarTab(nombre) {
    $('.pub-tab').removeClass('active');
    $('.pub-tab-pane').removeClass('active');
    $('.pub-tab[data-tab="' + nombre + '"]').addClass('active');
    $('#tab-' + nombre).addClass('active');
    document.querySelector('.pub-tabs').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
$('.pub-tab').on('click', function () {
    activarTab($(this).data('tab'));
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
