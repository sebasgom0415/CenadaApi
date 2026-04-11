<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro API — SIMM CENADA</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <style>
        body { background:#f4f6f9; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .reg-card { width:100%; max-width:460px; }
        .reg-logo { width:52px; height:52px; background:#4f6ef7; border-radius:14px; display:flex; align-items:center; justify-content:center; color:white; font-size:1.5rem; margin:0 auto 16px; }
    </style>
</head>
<body>
<div class="reg-card p-3">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <div class="reg-logo"><i class="bi bi-key-fill"></i></div>
                <h5 class="fw-bold mb-1">Acceso a la API</h5>
                <p class="text-muted small mb-0">Crea tu cuenta para obtener un token de acceso</p>
            </div>

            <form method="POST" action="{{ route('registro.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="Tu nombre" autofocus required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="tu@correo.com" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Mínimo 8 caracteres" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Crear cuenta y obtener token
                    </button>
                </div>
            </form>
        </div>
    </div>
    <p class="text-center text-muted small mt-3">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a>
        &nbsp;·&nbsp;
        <a href="{{ route('publico.index') }}" class="text-muted">Portal público</a>
    </p>
</div>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
