@extends('layouts.publico')

@section('title', 'Catálogo de Productos — SIMM CENADA')

@section('content')

<!-- Hero -->
<div class="pub-hero mb-4">
    <div class="row align-items-center">
        <div class="col-12 col-md-8">
            <h1><i class="bi bi-box-seam me-2"></i>Catálogo de Productos</h1>
            <p>Todos los productos registrados en los boletines de precios del CENADA</p>
        </div>
        <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
            <div style="color:rgba(255,255,255,0.7); font-size:0.82rem;">Total de productos</div>
            <div style="font-size:1.4rem; font-weight:700;">{{ $productos->total() }}</div>
            <div style="color:rgba(255,255,255,0.6); font-size:0.78rem;">en el catálogo</div>
        </div>
    </div>
</div>

<!-- Barra de búsqueda -->
<div class="filter-bar mb-4">
    <form method="GET" action="{{ route('publico.productos') }}">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-8">
                <label class="form-label fw-semibold" style="font-size:0.78rem;color:#718096;">BUSCAR PRODUCTO</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           class="form-control border-start-0"
                           placeholder="Ej: Tomate, Papa, Cebolla...">
                    <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                @if(request('buscar'))
                    <a href="{{ route('publico.productos') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-x me-1"></i>Limpiar búsqueda
                    </a>
                @else
                    <a href="{{ route('publico.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-table me-1"></i>Ver tabla de precios
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Resultados -->
<div class="pub-card">
    <div class="pub-card-header">
        <span>
            @if(request('buscar'))
                Resultados para "{{ request('buscar') }}" — {{ $productos->total() }} producto(s)
            @else
                Todos los productos
            @endif
        </span>
        <span style="font-size:0.78rem;color:#718096;">
            Página {{ $productos->currentPage() }} de {{ $productos->lastPage() }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="pub-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Unidad de comercialización</th>
                    <th class="text-center">Registros</th>
                    <th class="text-center">Historial</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                <tr>
                    <td class="fw-semibold">{{ $producto->nombre }}</td>
                    <td><span class="pub-badge">{{ $producto->unidad_comercializacion }}</span></td>
                    <td class="text-center" style="color:#718096;">{{ $producto->precios_count }}</td>
                    <td class="text-center">
                        <a href="{{ route('publico.productos.show', $producto) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-graph-up me-1"></i>Ver historial
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5" style="color:#718096;">
                        <i class="bi bi-search d-block mb-2" style="font-size:2rem;"></i>
                        No se encontraron productos para "{{ request('buscar') }}"
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($productos->hasPages())
    <div class="pub-card-body d-flex justify-content-center pt-0">
        {{ $productos->links() }}
    </div>
    @endif
</div>

@endsection
