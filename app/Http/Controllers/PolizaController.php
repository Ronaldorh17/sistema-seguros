<?php

namespace App\Http\Controllers;

use App\Services\PolizaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Enums\PolizaEstado;
use App\Models\Poliza;
use Illuminate\Validation\Rule;
use App\Services\PolizaWorkflowService;

class PolizaController extends Controller
{
    public function __construct(
        protected PolizaService $polizaService
    ) {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\Poliza::class);

        $porPagina = min(
            (int) $request->input('per_page', 15),
            100
        );

        $polizas = $this->polizaService->listar(
            $porPagina
        );

        return response()->json([
            'ok' => true,
            'data' => $polizas,
        ]);
    }

    public function enviarRevision(
    Poliza $poliza,
    PolizaWorkflowService $workflow
) {
    Gate::authorize('sendForReview', $poliza);

    try {
        $poliza = $workflow->enviarRevision($poliza);

        return response()->json([
            'ok' => true,
            'mensaje' => 'La póliza fue enviada a revisión correctamente.',
            'data' => $poliza,
        ]);
    } catch (\InvalidArgumentException $e) {
        return response()->json([
            'ok' => false,
            'mensaje' => $e->getMessage(),
        ], 422);
    }
}

    public function store(Request $request)
{
    Gate::authorize('create', Poliza::class);

    $datos = $request->validate([
        'tipo_poliza_id' => [
            'required',
            'integer',
            'exists:tipos_poliza,id',
        ],

        'numero_poliza' => [
            'required',
            'string',
            'max:150',
        ],

        'compania_aseguradora' => [
            'required',
            'string',
            'max:255',
        ],

        'vigencia_desde' => [
            'required',
            'date',
        ],

        'vigencia_hasta' => [
            'required',
            'date',
            'after_or_equal:vigencia_desde',
        ],

        'moneda_monto' => [
            'required',
            'string',
            'max:10',
        ],

        'monto_asegurado' => [
            'required',
            'numeric',
            'min:0',
        ],

        'moneda_prima' => [
            'required',
            'string',
            'max:10',
        ],

        'prima_seguro' => [
            'required',
            'numeric',
            'min:0',
        ],

        'observaciones' => [
            'nullable',
            'string',
        ],
    ]);

    $poliza = $this->polizaService->crear($datos);

    return response()->json([
        'ok' => true,
        'mensaje' => 'Póliza registrada correctamente.',
        'data' => $poliza,
    ], 201);
}

public function observar(
    Request $request,
    Poliza $poliza,
    PolizaWorkflowService $workflow
) {
    Gate::authorize('observe', $poliza);

    $datos = $request->validate([
        'observacion' => [
            'required',
            'string',
            'min:5',
            'max:2000',
        ],
    ]);

    try {

        $poliza = $workflow->observar(
            $poliza,
            $datos['observacion']
        );

        return response()->json([
            'ok' => true,
            'mensaje' => 'La póliza fue observada correctamente.',
            'data' => $poliza,
        ]);

    } catch (\InvalidArgumentException $e) {

        return response()->json([
            'ok' => false,
            'mensaje' => $e->getMessage(),
        ], 422);
    }
}

public function validar(
    Poliza $poliza,
    PolizaWorkflowService $workflow
) {
    Gate::authorize('validate', $poliza);

    try {

        $poliza = $workflow->validar($poliza);

        return response()->json([
            'ok' => true,
            'mensaje' => 'La póliza fue validada correctamente.',
            'data' => $poliza,
        ]);

    } catch (\InvalidArgumentException $e) {

        return response()->json([
            'ok' => false,
            'mensaje' => $e->getMessage(),
        ], 422);
    }
}

public function bloquear(
    Poliza $poliza,
    PolizaWorkflowService $workflow
) {
    Gate::authorize('block', $poliza);

    try {

        $poliza = $workflow->bloquear($poliza);

        return response()->json([
            'ok' => true,
            'mensaje' => 'La póliza fue bloqueada correctamente.',
            'data' => $poliza,
        ]);

    } catch (\InvalidArgumentException $e) {

        return response()->json([
            'ok' => false,
            'mensaje' => $e->getMessage(),
        ], 422);
    }
}

public function show(Poliza $poliza)
{
    Gate::authorize('view', $poliza);

    $poliza->load([
        'unidadOrganizacional:id,rrhh_id,nombre',
        'tipoPoliza:id,nombre',
        'historial' => function ($query) {
            $query->with([
                'usuario:id,name,persona_id',
            ])->orderBy('created_at', 'asc');
        },
        'documentos',
    ]);

    return response()->json([
        'ok' => true,
        'data' => $poliza,
    ]);
}

public function update(
    Request $request,
    Poliza $poliza
) {
    Gate::authorize('update', $poliza);

    $datos = $request->validate([
        'tipo_poliza_id' => [
            'required',
            'integer',
            'exists:tipos_poliza,id',
        ],

        'numero_poliza' => [
            'required',
            'string',
            'max:150',
        ],

        'compania_aseguradora' => [
            'required',
            'string',
            'max:255',
        ],

        'vigencia_desde' => [
            'required',
            'date',
        ],

        'vigencia_hasta' => [
            'required',
            'date',
            'after_or_equal:vigencia_desde',
        ],

        'moneda_monto' => [
            'required',
            'string',
            'max:10',
        ],

        'monto_asegurado' => [
            'required',
            'numeric',
            'min:0',
        ],

        'moneda_prima' => [
            'required',
            'string',
            'max:10',
        ],

        'prima_seguro' => [
            'required',
            'numeric',
            'min:0',
        ],

        'observaciones' => [
            'nullable',
            'string',
        ],
    ]);

    try {

        $poliza = $this->polizaService->actualizar(
            $poliza,
            $datos
        );

        return response()->json([
            'ok' => true,
            'mensaje' => 'La póliza fue actualizada correctamente.',
            'data' => $poliza,
        ]);

    } catch (\Illuminate\Database\QueryException $e) {

        return response()->json([
            'ok' => false,
            'mensaje' => 'No fue posible actualizar la póliza.',
        ], 422);
    }
}

public function misPolizas(Request $request)
{
    $user = $request->user();

    if (!$user->hasRole('SERVICIO_EXTERIOR')) {
        abort(403);
    }

    if (!$user->persona) {
        return response()->json([
            'ok' => false,
            'mensaje' => 'El usuario no tiene una persona asociada.'
        ], 422);
    }

    $request->validate([
        'estado' => [
            'nullable',
            'string',
            'in',
            'BORRADOR',
            'PENDIENTE_REVISION',
            'OBSERVADA',
            'VALIDADA',
            'BLOQUEADA',
        ],

        'buscar' => [
            'nullable',
            'string',
            'max:100',
        ],
    ]);

    $polizas = $this->polizaService->misPolizas(
        $user->persona->unidad_organizacional_id,
        $request->estado,
        $request->buscar
    );

    return response()->json([
        'ok' => true,
        'data' => $polizas,
    ]);
}

public function resumenMisPolizas(Request $request)
{
    $user = $request->user();

    if (!$user->hasRole('SERVICIO_EXTERIOR')) {
        abort(403);
    }

    if (!$user->persona || !$user->persona->unidad_organizacional_id) {
        return response()->json([
            'ok' => false,
            'mensaje' => 'El usuario no tiene una persona o unidad organizacional asociada.'
        ], 422);
    }

    return response()->json([
        'ok' => true,
        'data' => $this->polizaService->resumenMisPolizas(
            $user->persona->unidad_organizacional_id
        ),
    ]);
}

public function detalleMiPoliza(
    Request $request,
    Poliza $poliza
) {
    $user = $request->user();

    if (!$user->hasRole('SERVICIO_EXTERIOR')) {
        abort(403);
    }

    if (!$user->persona) {
        return response()->json([
            'ok' => false,
            'mensaje' => 'El usuario no tiene una persona asociada.'
        ], 422);
    }

    if (
        $user->persona->unidad_organizacional_id
        !== $poliza->unidad_organizacional_id
    ) {
        abort(403);
    }

    return response()->json([
        'ok' => true,
        'data' => $this->polizaService
            ->detalleMiPoliza($poliza),
    ]);
}

}