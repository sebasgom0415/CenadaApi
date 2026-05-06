<?php

namespace App\Http\Controllers;

use App\Models\Boletin;
use App\Services\BoletinParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BoletinController extends Controller
{
    public function __construct(private BoletinParserService $parser) {}

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

        $importados = 0;
        $errores    = [];

        foreach ($request->file('pdfs') as $file) {
            $nombreArchivo = $file->getClientOriginalName();

            // Guardar el PDF antes de procesarlo para tener la ruta pública
            $pdfPath = $file->store('boletines', 'public');

            $resultado = $this->parser->procesarPdf(
                $file->getPathname(),
                $nombreArchivo,
                $pdfPath
            );

            if ($resultado['importado']) {
                $importados++;
            } else {
                // Si falla, eliminar el archivo ya subido
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pdfPath);
                $errores[] = $resultado['error'];
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

    public function fetchFromEmail()
    {
        try {
            $exitCode = Artisan::call('simm:fetch-boletin');
            $output   = trim(Artisan::output());

            if ($exitCode === 0) {
                $resumen = collect(explode("\n", $output))->last();
                return redirect()->route('admin.boletines.index')
                    ->with('success', 'Importación desde correo completada. ' . $resumen);
            }

            return redirect()->route('admin.boletines.index')
                ->with('error', 'Error al conectar con el correo. ' . $output);
        } catch (\Exception $e) {
            return redirect()->route('admin.boletines.index')
                ->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }

    public function destroy(Boletin $boletin)
    {
        $boletin->delete();
        return response()->json(['ok' => true]);
    }

}
