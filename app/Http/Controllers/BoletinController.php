<?php

namespace App\Http\Controllers;

use App\Models\Boletin;
use App\Models\Plaza;
use App\Models\Precio;
use App\Models\Producto;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class BoletinController extends Controller
{
    public function index()
    {
        $boletines = Boletin::with('plaza')
            ->withCount('precios')
            ->latest('fecha_plaza')
            ->paginate(15);

        return view('boletines.index', compact('boletines'));
    }

    public function create()
    {
        return view('boletines.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pdfs'   => 'required|array|min:1',
            'pdfs.*' => 'file|mimes:pdf|max:20480',
        ]);

        $parser   = new Parser();
        $plaza    = Plaza::firstOrCreate(
            ['nombre'    => 'CENADA'],
            ['ubicacion' => 'Heredia, Costa Rica']
        );

        $importados = 0;
        $errores    = [];

        foreach ($request->file('pdfs') as $file) {
            $nombreArchivo = $file->getClientOriginalName();

            try {
                $pdf   = $parser->parseFile($file->getPathname());
                $texto = $pdf->getText();
                $datos = $this->parsearBoletin($texto);

                if (empty($datos['productos'])) {
                    $errores[] = "$nombreArchivo: no se pudieron extraer productos.";
                    continue;
                }

                if (empty($datos['fecha'])) {
                    $errores[] = "$nombreArchivo: no se pudo detectar la fecha del boletín.";
                    continue;
                }

                $existe = Boletin::where('plaza_id', $plaza->id)
                    ->where('fecha_plaza', $datos['fecha'])
                    ->exists();

                if ($existe) {
                    $errores[] = "$nombreArchivo: ya existe un boletín para la fecha {$datos['fecha']}.";
                    continue;
                }

                $pdfPath = $file->store('boletines', 'public');

                $boletin = Boletin::create([
                    'plaza_id'        => $plaza->id,
                    'fecha_plaza'     => $datos['fecha'],
                    'tipo_cambio_usd' => $datos['tipo_cambio'],
                    'archivo_pdf'     => $pdfPath,
                ]);

                foreach ($datos['productos'] as $item) {
                    $producto = Producto::firstOrCreate(
                        ['nombre' => $item['nombre']],
                        ['unidad_comercializacion' => $item['unidad']]
                    );

                    if ($producto->unidad_comercializacion !== $item['unidad']) {
                        $producto->update(['unidad_comercializacion' => $item['unidad']]);
                    }

                    Precio::create([
                        'boletin_id'    => $boletin->id,
                        'producto_id'   => $producto->id,
                        'precio_minimo' => $item['minimo'],
                        'precio_maximo' => $item['maximo'],
                        'moda'          => $item['moda'],
                        'promedio'      => $item['promedio'],
                    ]);
                }

                $importados++;

            } catch (\Exception $e) {
                $errores[] = "$nombreArchivo: error al procesar (" . $e->getMessage() . ").";
            }
        }

        if ($importados === 0) {
            return back()->with('error', 'No se importó ningún boletín. ' . implode(' | ', $errores));
        }

        $msg = "Se importaron $importados boletín(es) correctamente.";
        if (!empty($errores)) {
            $msg .= ' Advertencias: ' . implode(' | ', $errores);
        }

        return redirect()->route('admin.boletines.index')->with('success', $msg);
    }

    public function show(Boletin $boletin)
    {
        $boletin->load(['plaza', 'precios.producto']);
        $unidades = $boletin->precios
            ->map(fn($p) => $p->producto->unidad_comercializacion)
            ->unique()->sort()->values();

        return view('boletines.show', compact('boletin', 'unidades'));
    }

    public function destroy(Boletin $boletin)
    {
        $boletin->delete();
        return response()->json(['ok' => true]);
    }

    // ─── Parser del PDF ────────────────────────────────────────────────────────

    private function parsearBoletin(string $texto): array
    {
        $fecha      = null;
        $tipoCambio = null;
        $productos  = [];

        $lineas = preg_split('/\r?\n/', $texto);

        // Detectar fecha de plaza
        // Formato real extraído: "10/4/2026Fecha de Plaza:" o "Fecha de Plaza: 10/04/2026"
        foreach ($lineas as $linea) {
            if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $linea, $m)) {
                if (str_contains($linea, 'Fecha de Plaza') || str_contains($linea, 'Fecha:')) {
                    $fecha = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                    break;
                }
            }
        }

        // Detectar tipo de cambio
        foreach ($lineas as $linea) {
            if (preg_match('/1\s*USD\s*=\s*[¢c]?\.?\s*([\d,\.]+)/i', $linea, $m)) {
                $tipoCambio = (float) str_replace(',', '', $m[1]);
                break;
            }
        }

        // Formato real del texto extraído:
        // "UNIDAD\tPROMEDIO+MODA+MAXIMO+MINIMO+NOMBRE DEL PRODUCTO"
        // Ejemplo: "Unidad\t490.00500.00500.00450.00Aguacate criollo"
        // Los 4 precios están concatenados (sin espacio), cada uno con exactamente 2 decimales.
        // El orden en el texto es: Promedio, Moda, Máximo, Mínimo (inverso al PDF visual)

        $patron = '/^(.+)\t([\d,]+\.\d{2})([\d,]+\.\d{2})([\d,]+\.\d{2})([\d,]+\.\d{2})(.+)$/u';

        $ignorar = [
            'unidad de comercialización mayorista',
            'producto',
        ];

        foreach ($lineas as $linea) {
            if (!str_contains($linea, "\t")) continue;

            if (preg_match($patron, $linea, $m)) {
                $unidad  = trim($m[1]);
                $nombre  = trim($m[6]);

                if (in_array(strtolower($unidad), $ignorar)) continue;
                if (strlen($nombre) < 2) continue;

                // Orden en texto extraído: Promedio, Moda, Máximo, Mínimo
                $productos[] = [
                    'nombre'   => $nombre,
                    'unidad'   => $unidad,
                    'promedio' => $this->parsearNumero($m[2]),
                    'moda'     => $this->parsearNumero($m[3]),
                    'maximo'   => $this->parsearNumero($m[4]),
                    'minimo'   => $this->parsearNumero($m[5]),
                ];
            }
        }

        // Eliminar duplicados por nombre (el PDF tiene 3 páginas con solapamiento)
        $vistos    = [];
        $unicos    = [];
        foreach ($productos as $p) {
            if (!in_array($p['nombre'], $vistos)) {
                $vistos[]  = $p['nombre'];
                $unicos[]  = $p;
            }
        }

        return [
            'fecha'       => $fecha,
            'tipo_cambio' => $tipoCambio,
            'productos'   => $unicos,
        ];
    }

    private function parsearNumero(string $valor): float
    {
        return (float) str_replace(',', '', trim($valor));
    }
}
