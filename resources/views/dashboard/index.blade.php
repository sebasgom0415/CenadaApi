@extends('layouts.app')

@section('title', 'Dashboard - SIMM CENADA')
@section('page-title', 'Dashboard')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">Resumen General</h1>
        <p class="page-header-sub">Precios de mayorista a minorista - CENADA, Heredia</p>
    </div>
    <a href="{{ route('boletines.create') }}" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i> Importar Boletín PDF
    </a>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-file-earmark-pdf"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalBoletines }}</div>
                <div class="stat-label">Boletines importados</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-box-seam"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalProductos }}</div>
                <div class="stat-label">Productos distintos</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-calendar-check"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $ultimaFecha ?? '—' }}</div>
                <div class="stat-label">Último boletín</div>
            </div>
        </div>
    </div>
</div>

@if($totalBoletines > 0)
<div class="row g-3 mb-4">
    <!-- Chart: Evolución de precio promedio -->
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-graph-up me-2 text-primary"></i>Evolución de Precio Promedio</span>
                <select id="selectProductoChart" class="form-select form-select-sm w-auto">
                    @foreach($productosChart as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-body">
                <canvas id="chartEvolucion" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart: Distribución por unidad -->
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Productos por Unidad
            </div>
            <div class="card-body">
                <canvas id="chartUnidades" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Selector de fecha + Tabla de precios -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span><i class="bi bi-table me-2 text-primary"></i>Precios por Fecha de Plaza</span>
        <div class="d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 mb-0">
                <label class="form-label mb-0 small text-muted fw-semibold">Fecha:</label>
                <select name="boletin_id" class="form-select form-select-sm" style="min-width:160px;" onchange="this.form.submit()">
                    @foreach($fechasDisponibles as $id => $fecha)
                        <option value="{{ $id }}" {{ $boletinActivo?->id == $id ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('boletines.index') }}" class="btn btn-sm btn-outline-secondary">Ver todos</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                    @forelse($ultimosPrecios as $precio)
                    <tr>
                        <td class="fw-500">{{ $precio->producto->nombre }}</td>
                        <td><span class="badge bg-light text-secondary">{{ $precio->producto->unidad_comercializacion }}</span></td>
                        <td class="text-end">₡{{ number_format($precio->precio_minimo, 2) }}</td>
                        <td class="text-end">₡{{ number_format($precio->precio_maximo, 2) }}</td>
                        <td class="text-end">₡{{ number_format($precio->moda, 2) }}</td>
                        <td class="text-end fw-semibold text-primary">₡{{ number_format($precio->promedio, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox" style="font-size:3rem;color:#cbd5e0;"></i>
        <h5 class="mt-3 text-muted">Sin boletines importados</h5>
        <p class="text-secondary">Importa tu primer boletín PDF para comenzar a ver datos.</p>
        <a href="{{ route('boletines.create') }}" class="btn btn-primary">
            <i class="bi bi-upload me-1"></i> Importar Boletín PDF
        </a>
    </div>
</div>
@endif

@endsection

@push('scripts')
@if($totalBoletines > 0)
<script>
const chartData = @json($chartData);

// Chart: Evolución
const ctxEv = document.getElementById('chartEvolucion').getContext('2d');
let chartEvolucion = new Chart(ctxEv, {
    type: 'line',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Promedio (₡)',
            data: chartData.data,
            borderColor: '#4f6ef7',
            backgroundColor: 'rgba(79,110,247,0.08)',
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: '#4f6ef7',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                grid: { color: '#f0f2f5' },
                ticks: { callback: v => '₡' + v.toLocaleString('es-CR') }
            },
            x: { grid: { display: false } }
        }
    }
});

// Cambiar producto del chart
$('#selectProductoChart').on('change', function () {
    $.get('{{ route("dashboard.chart") }}', { producto_id: $(this).val() }, function (res) {
        chartEvolucion.data.labels = res.labels;
        chartEvolucion.data.datasets[0].data = res.data;
        chartEvolucion.update();
    });
});

// Chart: Unidades
const ctxPie = document.getElementById('chartUnidades').getContext('2d');
new Chart(ctxPie, {
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
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } }
        }
    }
});
</script>
@endif
@endpush
