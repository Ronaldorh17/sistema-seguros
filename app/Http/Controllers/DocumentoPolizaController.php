<?php

namespace App\Http\Controllers;

use App\Models\DocumentoPoliza;
use App\Models\Poliza;
use App\Services\DocumentoPolizaService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DocumentoPolizaController extends Controller
{
public function store(
    Request $request,
    Poliza $poliza,
    DocumentoPolizaService $service
) {
    /*
     * Creamos un objeto temporal para poder
     * utilizar la Policy.
     */
    $documento = new DocumentoPoliza([
        'poliza_id' => $poliza->id,
    ]);

    $documento->setRelation('poliza', $poliza);

    Gate::authorize('upload', $documento);

    $datos = $request->validate([
        'archivo' => [
            'required',
            'file',
            'max:10240',
            'mimes:pdf,jpg,jpeg,png',
        ],
    ]);

    try {

        $documento = $service->subir(
            $poliza,
            $datos['archivo']
        );

        // Registrar la carga en el historial
        $poliza->historial()->create([
            'usuario_id' => $request->user()->id,
            'accion' => 'DOCUMENTO_CARGADO',
            'estado_anterior' => $poliza->estado,
            'estado_nuevo' => $poliza->estado,
            'observacion' => 'Se cargó el documento: '
                . $documento->nombre_original,
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Documento cargado correctamente.',
            'data' => $documento,
        ], 201);

    } catch (\Throwable $e) {

        report($e);

        return response()->json([
            'ok' => false,
            'mensaje' => 'No fue posible cargar el documento.',
        ], 500);
    }
}

    public function index(Poliza $poliza)
{
    Gate::authorize('view', $poliza);

    $documentos = $poliza->documentos()
        ->with('usuario:id,name')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'ok' => true,
        'data' => $documentos,
    ]);
}

public function download(
    DocumentoPoliza $documento,
    DocumentoPolizaService $service
) {
    Gate::authorize('view', $documento);

    try {

        return $service->descargar($documento);

    } catch (\RuntimeException $e) {

        return response()->json([
            'ok' => false,
            'mensaje' => $e->getMessage(),
        ], 404);
    }
}

public function destroy(
    DocumentoPoliza $documento,
    DocumentoPolizaService $service
) {
    Gate::authorize('delete', $documento);

    try {

        $nombre = $documento->nombre_original;

        $service->eliminar($documento);

        return response()->json([
            'ok' => true,
            'mensaje' => "Documento '{$nombre}' eliminado correctamente.",
        ]);

    } catch (\Throwable $e) {

        report($e);

        return response()->json([
            'ok' => false,
            'mensaje' => 'No fue posible eliminar el documento.',
        ], 500);
    }
}
}