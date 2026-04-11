@extends('layouts.app')

@section('title', 'Boletín ' . $boletin->fecha_plaza->format('d/m/Y') . ' - SIMM CENADA')
@section('page-title', 'Detalle Boletín')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">Boletín — {{ $boletin->fecha_plaza->format('d \d\e F \d\e\l Y') }}</h1>
        <p class="page-header-sub">{{ $boletin->plaza->nombre }} · {{ $boletin->precios->count() }} productos · Tipo cambio: ₡{{ number_format($boletin->tipo_cambio_usd, 2) }}/USD</p>
    </div>
    <a href="{{ route('admin.boletines.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Buscador -->
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchProducto" class="form-control border-start-0 ps-0" placeholder="Buscar producto...">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select id="filterUnidad" class="form-select">
                    <option value="">Todas las unidades</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u }}">{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 text-md-end">
                <span class="text-muted small" id="countResultados">{{ $boletin->precios->count() }} productos</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="tablaPrecios">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Unidad</th>
                        <th class="text-end">Mínimo</th>
                        <th class="text-end">Máximo</th>
                        <th class="text-end">Moda</th>
                        <th class="text-end">Promedio</th>
                        <th class="text-end">Spread</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($boletin->precios->sortBy('producto.nombre') as $precio)
                    <tr data-nombre="{{ strtolower($precio->producto->nombre) }}" data-unidad="{{ $precio->producto->unidad_comercializacion }}">
                        <td class="fw-500">{{ $precio->producto->nombre }}</td>
                        <td><span class="badge bg-light text-secondary">{{ $precio->producto->unidad_comercializacion }}</span></td>
                        <td class="text-end">₡{{ number_format($precio->precio_minimo, 2) }}</td>
                        <td class="text-end">₡{{ number_format($precio->precio_maximo, 2) }}</td>
                        <td class="text-end">₡{{ number_format($precio->moda, 2) }}</td>
                        <td class="text-end fw-semibold text-primary">₡{{ number_format($precio->promedio, 2) }}</td>
                        <td class="text-end text-muted small">₡{{ number_format($precio->precio_maximo - $precio->precio_minimo, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function filtrarTabla() {
    const busqueda = $('#searchProducto').val().toLowerCase();
    const unidad   = $('#filterUnidad').val();
    let visibles   = 0;

    $('#tablaPrecios tbody tr').each(function () {
        const nombre   = $(this).data('nombre') || '';
        const uniRow   = $(this).data('unidad') || '';
        const matchN   = nombre.includes(busqueda);
        const matchU   = !unidad || uniRow === unidad;

        if (matchN && matchU) {
            $(this).show();
            visibles++;
        } else {
            $(this).hide();
        }
    });

    $('#countResultados').text(visibles + ' productos');
}

$('#searchProducto, #filterUnidad').on('input change', filtrarTabla);
</script>
@endpush
