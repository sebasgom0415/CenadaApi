@extends('layouts.app')

@section('title', 'Admin — SIMM CENADA')
@section('page-title', 'Panel de Administración')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">Bienvenido, {{ auth()->user()->name }}</h1>
        <p class="page-header-sub">Panel de gestión de boletines SIMM — CENADA</p>
    </div>
    <a href="{{ route('admin.boletines.create') }}" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i> Importar Boletín PDF
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-file-earmark-pdf"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalBoletines }}</div>
                <div class="stat-label">Boletines importados</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-box-seam"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalProductos }}</div>
                <div class="stat-label">Productos registrados</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-calendar-check"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $ultimaFecha ?? '—' }}</div>
                <div class="stat-label">Último boletín</div>
            </div>
        </div>
    </div>
</div>

<!-- Accesos rápidos -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <a href="{{ route('admin.boletines.create') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #4f6ef7 !important;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon blue flex-shrink-0"><i class="bi bi-upload"></i></div>
                    <div>
                        <div class="fw-semibold">Importar PDF</div>
                        <div class="text-muted small">Subir uno o varios boletines</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-4">
        <a href="{{ route('admin.boletines.index') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #38a169 !important;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon green flex-shrink-0"><i class="bi bi-list-ul"></i></div>
                    <div>
                        <div class="fw-semibold">Ver boletines</div>
                        <div class="text-muted small">Gestionar historial</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-4">
        <a href="{{ route('publico.index') }}" target="_blank" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #d69e2e !important;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon orange flex-shrink-0"><i class="bi bi-globe"></i></div>
                    <div>
                        <div class="fw-semibold">Portal público</div>
                        <div class="text-muted small">Ver como lo ve el usuario</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Últimos boletines -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Boletines recientes</span>
        <a href="{{ route('admin.boletines.index') }}" class="btn btn-sm btn-outline-secondary">Ver todos</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha de Plaza</th>
                        <th>Plaza</th>
                        <th class="text-center">Productos</th>
                        <th>Importado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimosBoletines as $boletin)
                    <tr>
                        <td class="fw-semibold">{{ $boletin->fecha_plaza->format('d/m/Y') }}</td>
                        <td>{{ $boletin->plaza->nombre }}</td>
                        <td class="text-center"><span class="badge bg-light text-secondary">{{ $boletin->precios_count }}</span></td>
                        <td class="text-muted small">{{ $boletin->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.boletines.show', $boletin) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No hay boletines aún</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
