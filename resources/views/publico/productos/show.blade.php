@extends('layouts.publico')

@section('title', $producto->nombre . ' — SIMM CENADA')

@section('content')

<!-- Hero -->
<div class="pub-hero mb-4">
    <div class="row align-items-center">
        <div class="col-12 col-md-9">
            <div style="color:rgba(255,255,255,0.65); font-size:0.8rem; margin-bottom:4px;">
                <a href="{{ route('publico.productos') }}" style="color:rgba(255,255,255,0.65);">
                    <i class="bi bi-arrow-left me-1"></i>Catálogo de productos
                </a>
            </div>
            <h1>{{ $producto->nombre }}</h1>
            <p>Unidad: {{ $producto->unidad_comercializacion }} · {{ $precios->count() }} registros históricos</p>
        </div>
        <div class="col-12 col-md-3 text-md-end mt-2 mt-md-0">
            @if($precios->isNotEmpty())
                <div style="color:rgba(255,255,255,0.7); font-size:0.78rem;">Último precio promedio</div>
                <div style="font-size:1.5rem; font-weight:700;">
                    ₡{{ number_format($precios->last()->promedio, 2) }}
                </div>
                <div style="color:rgba(255,255,255,0.6); font-size:0.76rem;">
                    {{ $precios->last()->boletin->fecha_plaza->format('d/m/Y') }}
                </div>
            @endif
        </div>
    </div>
</div>

@if($precios->isNotEmpty())

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="pub-stat">
            <div class="pub-stat-icon green"><i class="bi bi-arrow-down-circle"></i></div>
            <div>
                <div class="pub-stat-value">₡{{ number_format($precios->min('precio_minimo'), 2) }}</div>
                <div class="pub-stat-label">Mínimo histórico</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pub-stat">
            <div class="pub-stat-icon" style="background:#fff3cd;"><i class="bi bi-arrow-up-circle" style="color:#d69e2e;"></i></div>
            <div>
                <div class="pub-stat-value">₡{{ number_format($precios->max('precio_maximo'), 2) }}</div>
                <div class="pub-stat-label">Máximo histórico</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pub-stat">
            <div class="pub-stat-icon blue"><i class="bi bi-calculator"></i></div>
            <div>
                <div class="pub-stat-value">₡{{ number_format($precios->avg('promedio'), 2) }}</div>
                <div class="pub-stat-label">Promedio general</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pub-stat">
            <div class="pub-stat-icon" style="background:#f0fff4;"><i class="bi bi-calendar3" style="color:#38a169;"></i></div>
            <div>
                <div class="pub-stat-value">{{ $precios->count() }}</div>
                <div class="pub-stat-label">Días con datos</div>
            </div>
        </div>
    </div>
</div>

<!-- Gráfica -->
<div class="pub-card mb-4">
    <div class="pub-card-header">
        <span><i class="bi bi-graph-up me-2" style="color:#4f6ef7;"></i>Evolución de Precios</span>
        <span style="font-size:0.78rem;color:#718096;">
            {{ $precios->first()->boletin->fecha_plaza->format('d/m/Y') }}
            — {{ $precios->last()->boletin->fecha_plaza->format('d/m/Y') }}
        </span>
    </div>
    <div class="pub-card-body">
        <canvas id="chartProducto" height="90"></canvas>
    </div>
</div>

@endif

<!-- Tabla histórico -->
<div class="pub-card">
    <div class="pub-card-header">
        <span><i class="bi bi-table me-2" style="color:#4f6ef7;"></i>Histórico de Precios</span>
        <a href="{{ route('publico.productos') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al catálogo
        </a>
    </div>

    @if($precios->isEmpty())
    <div class="pub-card-body text-center py-5" style="color:#718096;">
        <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;"></i>
        No hay registros históricos para este producto.
    </div>
    @else
    <div class="table-responsive">
        <table class="pub-table">
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
                    $icono = '';
                    if ($anterior !== null) {
                        $variacion = $precio->promedio - $anterior;
                        $clase = $variacion > 0 ? 'text-danger' : ($variacion < 0 ? 'text-success' : 'text-muted');
                        $icono = $variacion > 0 ? 'bi-arrow-up-short' : ($variacion < 0 ? 'bi-arrow-down-short' : '');
                    }
                    $anterior = $precio->promedio;
                @endphp
                <tr>
                    <td class="fw-semibold">{{ $precio->boletin->fecha_plaza->format('d/m/Y') }}</td>
                    <td class="text-end">₡{{ number_format($precio->precio_minimo, 2) }}</td>
                    <td class="text-end">₡{{ number_format($precio->precio_maximo, 2) }}</td>
                    <td class="text-end">₡{{ number_format($precio->moda, 2) }}</td>
                    <td class="text-end fw-semibold" style="color:#4f6ef7;">₡{{ number_format($precio->promedio, 2) }}</td>
                    <td class="text-end {{ $clase }}">
                        @if($variacion !== null)
                            @if($icono)<i class="bi {{ $icono }}"></i>@endif
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
    @endif
</div>

@endsection

@push('scripts')
@if($precios->isNotEmpty())
<script>
new Chart(document.getElementById('chartProducto').getContext('2d'), {
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
                borderDash: [5, 3],
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
                borderDash: [5, 3],
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
