# SIMM CENADA — Sistema de Precios Mayoristas

Dashboard y API REST para la consulta de precios mayoristas del **CENADA** (Centro Nacional de Abastecimiento y Distribución de Alimentos), Heredia, Costa Rica.

Los datos provienen de los boletines PDF publicados diariamente por el **SIMM/SIFPIMA** (Sistema de Información de Mercados Mayoristas — PIMA).

---

## Características

- **Importación automática de PDFs** — carga uno o varios boletines a la vez y extrae todos los productos y precios automáticamente
- **Dashboard público** — tablas, gráficos de evolución y top 10 de precios, accesible sin login
- **Panel de administración** — gestión de boletines, catálogo de productos e historial de precios
- **API REST con autenticación por token** — consume los datos desde cualquier aplicación externa
- Sin dependencias de CDN — Bootstrap 5, jQuery, Chart.js y SweetAlert2 están incluidos localmente

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12, PHP 8.2 |
| Base de datos | MySQL |
| PDF Parser | `smalot/pdfparser` |
| Frontend | Bootstrap 5, jQuery 3.7, Chart.js 4.4, SweetAlert2 11 |
| Auth | Sesión nativa de Laravel + API token |

---

## Requisitos

- PHP >= 8.2
- MySQL >= 5.7
- Composer
- Servidor web (Apache/Nginx o `php artisan serve`)

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/cenada-simm.git
cd cenada-simm

# 2. Instalar dependencias PHP
composer install

# 3. Copiar el archivo de entorno
cp .env.example .env

# 4. Generar clave de aplicación
php artisan key:generate
```

### Configurar la base de datos

Edita el archivo `.env` con tus credenciales:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cenada_simm
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 5. Crear la base de datos y ejecutar migraciones
php artisan migrate

# 6. Crear el usuario administrador
php artisan db:seed --class=AdminUserSeeder

# 7. Crear enlace de almacenamiento (para los PDFs subidos)
php artisan storage:link
```

### Credenciales por defecto del admin

```
Email:      admin@cenada.cr
Contraseña: cenada2026
```

> Cámbialas desde la base de datos después del primer ingreso.

---

## Estructura de la base de datos

```
plazas
├── id
├── nombre                   (ej: CENADA)
└── ubicacion                (ej: Heredia, Costa Rica)

productos
├── id
├── nombre                   (ej: Tomate primera)
└── unidad_comercializacion  (ej: Caja plástica (18 kg))

boletines
├── id
├── plaza_id
├── fecha_plaza              (date)
├── tipo_cambio_usd
└── archivo_pdf

precios
├── id
├── boletin_id
├── producto_id
├── precio_minimo
├── precio_maximo
├── moda
└── promedio
```

---

## Uso

### Portal público

Accesible sin login:

```
http://localhost/cenada/public/
```

- Selector de fecha de boletín
- Filtros por unidad de medida y búsqueda de producto
- Tab **Tabla** — precios min/max/moda/promedio
- Tab **Gráficos** — evolución histórica, distribución por unidad, top 10 más caros

### Panel de administración

```
http://localhost/cenada/public/admin
http://localhost/cenada/public/login
```

- Importar PDFs (uno o varios a la vez con drag & drop)
- Ver y eliminar boletines
- Catálogo de productos con historial de precios
- Gestión de API tokens

### Importar un boletín PDF

1. Ingresar al panel admin
2. Ir a **Boletines → Importar PDF**
3. Seleccionar el PDF del SIMM  
   Formato esperado: `SIMM-Boletin de Precios PIMA-Plaza YYYY-MM-DD.pdf`
4. El sistema extrae automáticamente fecha, productos y precios

---

## API REST

**Base URL:** `http://localhost/cenada/public/api`

Todas las rutas requieren autenticación. Genera un token desde el panel admin en la sección **API Token**.

### Autenticación

```http
# Opción 1 — Header HTTP
Authorization: Bearer {tu_token}

# Opción 2 — Query string
GET /api/boletines?api_token={tu_token}
```

### Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/boletines` | Lista de todos los boletines disponibles |
| `GET` | `/api/boletines/latest` | Último boletín completo con todos los precios |
| `GET` | `/api/boletines/{fecha}` | Boletín por fecha en formato `YYYY-MM-DD` |
| `GET` | `/api/boletines/{fecha}/producto/{nombre}` | Busca un producto en una fecha específica |
| `GET` | `/api/productos` | Catálogo completo de productos |
| `GET` | `/api/productos/{id}/historial` | Historial de precios de un producto |

### Ejemplos de respuesta

**GET /api/boletines**
```json
{
  "success": true,
  "total": 2,
  "data": [
    {
      "id": 2,
      "fecha_plaza": "2026-04-11",
      "plaza": "CENADA",
      "total_productos": 79
    },
    {
      "id": 1,
      "fecha_plaza": "2026-04-10",
      "plaza": "CENADA",
      "total_productos": 79
    }
  ]
}
```

**GET /api/boletines/2026-04-10**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "fecha_plaza": "2026-04-10",
    "plaza": "CENADA",
    "ubicacion": "Heredia, Costa Rica",
    "total_productos": 79,
    "precios": [
      {
        "producto": "Aguacate criollo",
        "unidad_comercializacion": "Unidad",
        "precio_minimo": 450.00,
        "precio_maximo": 500.00,
        "moda": 500.00,
        "promedio": 490.00
      }
    ]
  }
}
```

**GET /api/productos/1/historial**
```json
{
  "success": true,
  "producto": "Tomate primera",
  "unidad": "Caja plástica (18 kg)",
  "total": 5,
  "data": [
    {
      "fecha_plaza": "2026-04-06",
      "precio_minimo": 28000.00,
      "precio_maximo": 30000.00,
      "moda": 29000.00,
      "promedio": 29200.00
    },
    {
      "fecha_plaza": "2026-04-10",
      "precio_minimo": 31000.00,
      "precio_maximo": 32000.00,
      "moda": 31000.00,
      "promedio": 31444.44
    }
  ]
}
```

**Error — sin token (401)**
```json
{
  "success": false,
  "message": "API token requerido. Envíalo como Bearer token o parámetro api_token."
}
```

**Error — fecha no encontrada (404)**
```json
{
  "success": false,
  "message": "No existe boletín para la fecha 2026-01-01."
}
```

---

## Generar API token por consola

```bash
php artisan simm:api-token

# Para un usuario específico
php artisan simm:api-token usuario@correo.com
```

---

## Librerías frontend (incluidas localmente, sin CDN)

| Librería | Versión |
|---|---|
| Bootstrap | 5.3.3 |
| Bootstrap Icons | 1.11.3 |
| jQuery | 3.7.1 |
| Chart.js | 4.4.4 |
| SweetAlert2 | 11.14.5 |

---

## Licencia

MIT
