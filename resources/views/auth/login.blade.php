<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIMM CENADA</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { width: 100%; max-width: 400px; }
        .login-logo { width: 52px; height: 52px; background: #4f6ef7; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin: 0 auto 16px; }
    </style>
</head>
<body>
<div class="login-card p-3">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <div class="login-logo"><i class="bi bi-bar-chart-line-fill"></i></div>
                <h5 class="fw-bold mb-1">Panel de Administración</h5>
                <p class="text-muted small mb-0">SIMM — CENADA, Heredia</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="admin@cenada.cr" autofocus required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Recordarme</label>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Ingresar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <p class="text-center text-muted small mt-3">
        ¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate para acceder a la API</a>
    </p>
    <p class="text-center text-muted small mt-1">
        <a href="{{ route('publico.index') }}" class="text-muted">
            <i class="bi bi-arrow-left me-1"></i>Ver portal público
        </a>
    </p>
</div>

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
