@extends('layouts.app')

@section('title', 'Productos - SIMM CENADA')
@section('page-title', 'Productos')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">Catálogo de Productos</h1>
        <p class="page-header-sub">Todos los productos registrados en los boletines</p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.productos.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0 ps-0" placeholder="Buscar producto...">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Buscar</button>
                @if(request('buscar'))
                    <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary ms-1">Limpiar</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Unidad de comercialización</th>
                        <th class="text-center">Boletines</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr>
                        <td class="fw-500">{{ $producto->nombre }}</td>
                        <td><span class="badge bg-light text-secondary">{{ $producto->unidad_comercializacion }}</span></td>
                        <td class="text-center">{{ $producto->precios_count }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.productos.show', $producto) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-graph-up me-1"></i>Ver historial
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;"></i>
                            No hay productos registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($productos->hasPages())
    <div class="card-footer d-flex justify-content-end">
        {{ $productos->links() }}
    </div>
    @endif
</div>

@endsection
