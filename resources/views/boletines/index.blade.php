@extends('layouts.app')

@section('title', 'Boletines - SIMM CENADA')
@section('page-title', 'Boletines')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">Boletines de Precios</h1>
        <p class="page-header-sub">Historial de boletines importados</p>
    </div>
    <a href="{{ route('admin.boletines.create') }}" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i> Importar PDF
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plaza</th>
                        <th>Fecha de Plaza</th>
                        <th class="text-center">Productos</th>
                        <th class="text-end">Tipo Cambio USD</th>
                        <th>Importado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($boletines as $boletin)
                    <tr>
                        <td class="text-muted">{{ $boletin->id }}</td>
                        <td>{{ $boletin->plaza->nombre }}</td>
                        <td><span class="fw-semibold">{{ $boletin->fecha_plaza->format('d/m/Y') }}</span></td>
                        <td class="text-center"><span class="badge bg-light text-secondary">{{ $boletin->precios_count }}</span></td>
                        <td class="text-end">₡{{ number_format($boletin->tipo_cambio_usd, 2) }}</td>
                        <td class="text-muted small">{{ $boletin->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.boletines.show', $boletin) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger"
                                onclick="confirmDelete('{{ route('admin.boletines.destroy', $boletin) }}', 'Boletín {{ $boletin->fecha_plaza->format('d/m/Y') }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;"></i>
                            No hay boletines importados aún
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($boletines->hasPages())
    <div class="card-footer d-flex justify-content-end">
        {{ $boletines->links() }}
    </div>
    @endif
</div>

@endsection
