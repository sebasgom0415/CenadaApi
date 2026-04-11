@extends('layouts.app')

@section('title', $producto->nombre . ' - SIMM CENADA')
@section('page-title', 'Historial de Precios')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">{{ $producto->nombre }}</h1>
        <p class="page-header-sub">Unidad: {{ $producto->unidad_comercializacion }} · {{ $precios->count() }} registros históricos</p>
    </div>
    <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

@if($precios->isNotEmpty())
<!-- Stats rápidos -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value" style="font-size:1.2rem;">₡{{ number_format($precios->min('precio_minimo'), 2) }}</div>
                <div class="stat-label">Precio mínimo histórico</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value" style="font-size:1.2rem;">₡{{ number_format($precios->max('precio_maximo'), 2) }}</div>
                <div class="stat-label">Precio máximo histórico</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-calculator"></i></div>
            <div class="stat-info">
                <div class="stat-value" style="font-size:1.2rem;">₡{{ number_format($precios->avg('promedio'), 2) }}</div>
                <div class="stat-label">Promedio general</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon gray"><i class="bi bi-calendar3"></i></div>
            <div class="stat-info">
                <div class="stat-value" style="font-size:1.2rem;">{{ $precios->count() }}</div>
                <div class="stat-label">Días con datos</div>
            </div>
        </div>
    </div>
</div>

<!-- Gráfica -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-graph-up me-2 text-primary"></i>Evolución de Precios
    </div>
    <div class="card-body">
        <canvas id="chartProducto" height="80"></canvas>
    </div>
</div>
@endif

<!-- Tabla histórico -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table me-2 text-primary"></i>Histórico de Precios
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th class="text-end">Mínimo</th>
                        <th class="text-end">Máximo</th>
                        <th class="text-end">Moda</th>
                        <th class="text-end">Promedio</th>
                        <th class="text-end">Variación</th>
                    </tr>
                </thead>
                <tbody>
                    @php $anterior = null; @endphp
                    @foreach($precios->reverse() as $precio)
                    @php
                        $variacion = null;
                        $clase = '';
                        if ($anterior !== null) {
                            $variacion = $precio->promedio - $anterior;
                            $clase = $variacion > 0 ? 'text-danger' : ($variacion < 0 ? 'text-success' : 'text-muted');
                        }
                        $anterior = $precio->promedio;
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $precio->boletin->fecha_plaza->format('d/m/Y') }}</td>
                        <td class="text-end">₡{{ number_format($precio->precio_minimo, 2) }}</td>
                        <td class="text-end">₡{{ number_format($precio->precio_maximo, 2) }}</td>
                        <td class="text-end">₡{{ number_format($precio->moda, 2) }}</td>
                        <td class="text-end fw-semibold text-primary">₡{{ number_format($precio->promedio, 2) }}</td>
                        <td class="text-end {{ $clase }}">
                            @if($variacion !== null)
                                @if($variacion > 0)<i class="bi bi-arrow-up-short"></i>@elseif($variacion < 0)<i class="bi bi-arrow-down-short"></i>@endif
                                ₡{{ number_format(abs($variacion), 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@if($precios->isNotEmpty())
<script>
const ctx = document.getElementById('chartProducto').getContext('2d');
new Chart(ctx, {
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
        plugins: { legend: { position: 'top', labels: { font: { size: 12 } } } },
        scales: {
            y: {
                grid: { color: '#f0f2f5' },
                ticks: { callback: v => '₡' + v.toLocaleString('es-CR') }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endif
@endpush
