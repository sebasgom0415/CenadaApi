<?php

namespace App\Console\Commands;

use App\Services\BoletinParserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchBoletinEmail extends Command
{
    protected $signature = 'simm:fetch-boletin
                            {--force : Procesar aunque el correo ya esté marcado como leído}
                            {--dry-run : Solo mostrar qué emails se encontrarían, sin importar}';

    protected $description = 'Busca el boletín de precios CENADA en el correo y lo importa automáticamente';

    // Basta con que el asunto contenga UNA de estas palabras (case-insensitive)
    private const SUBJECT_KEYWORDS = ['PIMA', 'CENADA', 'Boletín', 'Boletin'];

    public function handle(BoletinParserService $parser): int
    {
        $host   = config('imap.host');
        $port   = config('imap.port');
        $enc    = config('imap.encryption');
        $user   = config('imap.user');
        $pass   = config('imap.pass');
        $folder = config('imap.folder', 'INBOX');

        if (empty($host) || empty($user) || empty($pass)) {
            $this->error('Configuración IMAP incompleta. Revise IMAP_HOST, IMAP_USER y IMAP_PASS en .env');
            return self::FAILURE;
        }

        $mailbox = "{" . $host . ":" . $port . "/imap/" . $enc . "/novalidate-cert}" . $folder;

        $this->info("Conectando a {$host}:{$port} como {$user}…");

        $imap = @imap_open($mailbox, $user, $pass, 0, 1);

        if (!$imap) {
            $error = imap_last_error();
            $this->error("No se pudo conectar al servidor IMAP: {$error}");
            Log::error("FetchBoletinEmail: fallo de conexión IMAP – {$error}");
            return self::FAILURE;
        }

        $criterio = $this->option('force') ? 'ALL' : 'UNSEEN';
        $ids = imap_search($imap, $criterio);

        if (!$ids) {
            $this->info('No hay correos' . ($this->option('force') ? '' : ' no leídos') . ' en la bandeja.');
            imap_close($imap);
            return self::SUCCESS;
        }

        $this->info('Correos encontrados: ' . count($ids));

        $importados = 0;
        $omitidos   = 0;

        foreach ($ids as $msgId) {
            $header  = imap_headerinfo($imap, $msgId);
            $subject = $this->decodificarAsunto($header->subject ?? '');

            if (!$this->esBoletinCenada($subject)) {
                $this->line("  · Omitido (asunto no coincide): {$subject}");
                $omitidos++;
                continue;
            }

            $this->info("  ✓ Boletín detectado: {$subject}");

            if ($this->option('dry-run')) {
                $this->warn("    [dry-run] No se importará.");
                continue;
            }

            $estructura = imap_fetchstructure($imap, $msgId);
            $adjuntos   = $this->obtenerAdjuntosPdf($estructura);

            if (empty($adjuntos)) {
                $this->warn("    Sin adjuntos PDF. Se omite.");
                Log::warning("FetchBoletinEmail: correo '{$subject}' sin PDF adjunto.");
                continue;
            }

            foreach ($adjuntos as $adjunto) {
                ['parte' => $numParte, 'nombre' => $nombreArchivo, 'encoding' => $encoding] = $adjunto;

                $contenido = imap_fetchbody($imap, $msgId, (string) $numParte);

                $contenido = match ($encoding) {
                    3 => base64_decode($contenido),          // BASE64
                    4 => quoted_printable_decode($contenido), // QP
                    default => $contenido,
                };

                // Archivo temporal para el parser
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . Str::random(16) . '.pdf';
                file_put_contents($tmpPath, $contenido);

                // Ruta permanente en storage/public
                $storageName = 'boletines/' . date('Y-m-d_His') . '_' . Str::slug(pathinfo($nombreArchivo, PATHINFO_FILENAME)) . '.pdf';
                Storage::disk('public')->put($storageName, $contenido);

                $resultado = $parser->procesarPdf($tmpPath, $nombreArchivo, $storageName);

                @unlink($tmpPath);

                if ($resultado['importado']) {
                    $fecha = $resultado['fecha'] ?? 'desconocida';
                    $this->info("    Importado correctamente (fecha: {$fecha})");
                    Log::info("FetchBoletinEmail: importado. Fecha={$fecha}, archivo={$storageName}");
                    $importados++;
                } else {
                    Storage::disk('public')->delete($storageName);
                    $this->warn("    " . $resultado['error']);
                    Log::warning("FetchBoletinEmail: " . $resultado['error']);
                }
            }

            // Marcar como leído para no reprocesarlo
            imap_setflag_full($imap, (string) $msgId, '\\Seen');
        }

        imap_close($imap);

        $this->newLine();
        $this->info("Resumen: {$importados} importado(s), {$omitidos} omitido(s) por asunto.");

        return self::SUCCESS;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function esBoletinCenada(string $asunto): bool
    {
        foreach (self::SUBJECT_KEYWORDS as $kw) {
            if (stripos($asunto, $kw) !== false) {
                return true;
            }
        }
        return false;
    }

    private function decodificarAsunto(string $asunto): string
    {
        $partes = imap_mime_header_decode($asunto);
        return implode('', array_map(fn($p) => $p->text, $partes));
    }

    /**
     * Recorre la estructura MIME y devuelve todos los adjuntos PDF.
     * Cada elemento: ['parte' => string, 'nombre' => string, 'encoding' => int]
     */
    private function obtenerAdjuntosPdf(object $estructura, string $prefijo = ''): array
    {
        $adjuntos = [];

        if (!isset($estructura->parts)) {
            // Mensaje simple
            if ($this->esPdf($estructura)) {
                $adjuntos[] = [
                    'parte'    => $prefijo ?: '1',
                    'nombre'   => $this->nombreArchivo($estructura) ?: 'boletin.pdf',
                    'encoding' => $estructura->encoding ?? 3,
                ];
            }
            return $adjuntos;
        }

        foreach ($estructura->parts as $i => $parte) {
            $numParte = ($prefijo ? $prefijo . '.' : '') . ($i + 1);

            if ($this->esPdf($parte)) {
                $adjuntos[] = [
                    'parte'    => $numParte,
                    'nombre'   => $this->nombreArchivo($parte) ?: "boletin_{$numParte}.pdf",
                    'encoding' => $parte->encoding ?? 3,
                ];
            } elseif (isset($parte->parts)) {
                // Multipart anidado — recurrir
                $adjuntos = array_merge($adjuntos, $this->obtenerAdjuntosPdf($parte, $numParte));
            }
        }

        return $adjuntos;
    }

    private function esPdf(object $parte): bool
    {
        if (strtolower($parte->subtype ?? '') === 'pdf') return true;

        foreach ($parte->parameters ?? [] as $param) {
            if (strtolower($param->attribute) === 'name' && str_ends_with(strtolower($param->value), '.pdf')) {
                return true;
            }
        }

        foreach ($parte->dparameters ?? [] as $param) {
            if (strtolower($param->attribute) === 'filename' && str_ends_with(strtolower($param->value), '.pdf')) {
                return true;
            }
        }

        return false;
    }

    private function nombreArchivo(object $parte): string
    {
        foreach ($parte->dparameters ?? [] as $param) {
            if (strtolower($param->attribute) === 'filename') return $param->value;
        }
        foreach ($parte->parameters ?? [] as $param) {
            if (strtolower($param->attribute) === 'name') return $param->value;
        }
        return '';
    }
}
