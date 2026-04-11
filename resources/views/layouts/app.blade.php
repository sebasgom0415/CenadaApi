<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMM - CENADA')</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="bi bi-bar-chart-line-fill"></i>
            </div>
            <div class="sidebar-brand-text">
                <span class="brand-title">SIMM</span>
                <span class="brand-sub">CENADA</span>
            </div>
        </div>

        <div class="sidebar-nav">
            <div class="sidebar-section-label">Principal</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('publico.index') }}" class="nav-link" target="_blank">
                        <i class="bi bi-globe"></i>
                        <span>Vista pública</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-label">API</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.api.token') }}" class="nav-link {{ request()->routeIs('admin.api.*') ? 'active' : '' }}">
                        <i class="bi bi-key"></i>
                        <span>API Token</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-label">Gestión</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.boletines.index') }}" class="nav-link {{ request()->routeIs('admin.boletines.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>Boletines</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.productos.index') }}" class="nav-link {{ request()->routeIs('admin.productos.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i>
                        <span>Productos</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer d-flex align-items-center justify-content-between">
            <span class="text-muted small">SIFPIMA &copy; {{ date('Y') }}</span>
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-link text-muted p-0" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-left">
                <h5 class="topbar-title mb-0">@yield('page-title', 'Dashboard')</h5>
            </div>

            <div class="topbar-right">
                <span class="badge-date">
                    <i class="bi bi-calendar3 me-1"></i>
                    {{ \Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM [del] YYYY') }}
                </span>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/js/chart.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
