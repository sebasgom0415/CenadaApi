<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIMM CENADA — Precios Mayoristas')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/publico.css') }}">
</head>
<body>

<!-- Navbar pública -->
<nav class="pub-navbar">
    <div class="container-fluid px-4 d-flex align-items-center gap-3">
        <div class="pub-brand">
            <div class="pub-brand-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
            <div>
                <span class="pub-brand-title">SIMM</span>
                <span class="pub-brand-sub">CENADA · Precios Mayoristas</span>
            </div>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="pub-date-badge">
                <i class="bi bi-calendar3 me-1"></i>
                {{ \Carbon\Carbon::now()->locale('es')->isoFormat('D MMM YYYY') }}
            </span>
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-lock me-1"></i>Admin
            </a>
        </div>
    </div>
</nav>

<div class="pub-content">
    @yield('content')
</div>

<footer class="pub-footer">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span>Sistema de Información de Mercados Mayoristas — CENADA, Heredia, Costa Rica</span>
        <span>SIFPIMA / PIMA &copy; {{ date('Y') }}</span>
    </div>
</footer>

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/js/chart.min.js') }}"></script>
@stack('scripts')
</body>
</html>
