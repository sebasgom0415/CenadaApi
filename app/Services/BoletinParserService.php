<?php

namespace App\Services;

use App\Models\Boletin;
use App\Models\Plaza;
use App\Models\Precio;
use App\Models\Producto;
use Smalot\PdfParser\Parser;

class BoletinParserService
{
    /**
     * Procesa un archivo PDF de boletín y lo guarda en la base de datos.
     *
     * @param  string  $rutaPdf       Ruta absoluta o temporal al archivo PDF
     * @param  string  $nombreArchivo Nombre para mostrar en mensajes de error
     * @param  string|null $rutaPublica Ruta relativa en storage/public (para boletines subidos vía web)
     * @return array{importado: bool, error: string|null}
     */
    public function procesarPdf(string $rutaPdf, string $nombreArchivo, ?string $rutaPublica = null): array
    {
        $parser = new Parser();
        $plaza  = Plaza::firstOrCreate(
            ['nombre'    => 'CENADA'],
            ['ubicacion' => 'Heredia, Costa Rica']
        );

        try {
            $pdf   = $parser->parseFile($rutaPdf);
            $texto = $pdf->getText();
            $datos = $this->parsearBoletin($texto);

            if (empty($datos['productos'])) {
                return ['importado' => false, 'error' => "$nombreArchivo: no se pudieron extraer productos."];
            }

            if (empty($datos['fecha'])) {
                return ['importado' => false, 'error' => "$nombreArchivo: no se pudo detectar la fecha del boletín."];
            }

            $existe = Boletin::where('plaza_id', $plaza->id)
                ->where('fecha_plaza', $datos['fecha'])
                ->exists();

            if ($existe) {
                return ['importado' => false, 'error' => "$nombreArchivo: ya existe un boletín para la fecha {$datos['fecha']}."];
            }

            $boletin = Boletin::create([
                'plaza_id'        => $plaza->id,
                'fecha_plaza'     => $datos['fecha'],
                'tipo_cambio_usd' => $datos['tipo_cambio'],
                'archivo_pdf'     => $rutaPublica ?? '',
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

            return ['importado' => true, 'error' => null, 'boletin_id' => $boletin->id, 'fecha' => $datos['fecha']];

        } catch (\Exception $e) {
            return ['importado' => false, 'error' => "$nombreArchivo: error al procesar (" . $e->getMessage() . ")."];
        }
    }

    // ─── Parser del PDF ────────────────────────────────────────────────────────

    public function parsearBoletin(string $texto): array
    {
        $fecha      = null;
        $tipoCambio = null;
        $productos  = [];

        $lineas = preg_split('/\r?\n/', $texto);

        // Detectar fecha de plaza
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

        // Formato: "UNIDAD\tPROMEDIO+MODA+MAXIMO+MINIMO+NOMBRE DEL PRODUCTO"
        $patron = '/^(.+)\t([\d,]+\.\d{2})([\d,]+\.\d{2})([\d,]+\.\d{2})([\d,]+\.\d{2})(.+)$/u';

        $ignorar = [
            'unidad de comercialización mayorista',
            'producto',
        ];

        foreach ($lineas as $linea) {
            if (!str_contains($linea, "\t")) continue;

            if (preg_match($patron, $linea, $m)) {
                $unidad = trim($m[1]);
                $nombre = trim($m[6]);

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
        $vistos = [];
        $unicos = [];
        foreach ($productos as $p) {
            if (!in_array($p['nombre'], $vistos)) {
                $vistos[] = $p['nombre'];
                $unicos[] = $p;
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
