<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

use App\Services\ActivosFijosService;
use App\Models\Poliza;

class ActivosFijosController extends Controller
{
    public function dashboard(
        ActivosFijosService $service
    ) {
        Gate::authorize('viewAny', \App\Models\Poliza::class);

        $user = Auth::user();

        if (!$user->{'hasAnyRole'}([
            'ACTIVOS_FIJOS',
            'ADMINISTRADOR',
        ])) {
            abort(403);
        }

        return response()->json([
            'ok' => true,
            'data' => $service->dashboard(),
        ]);
    }

    public function polizasPendientes(
    Request $request,
    ActivosFijosService $service
) {
    Gate::authorize(
        'viewAny',
        \App\Models\Poliza::class
    );

    $this->authorizeReviewer($request);

    $porPagina = min(
        (int) $request->input('per_page', 15),
        100
    );

    return response()->json([
        'ok' => true,
        'data' => $service->polizasPendientes($porPagina),
    ]);
}

public function detalleRevision(
    Request $request,
    Poliza $poliza,
    ActivosFijosService $service
) {
    Gate::authorize(
        'viewAny',
        Poliza::class
    );

    $this->authorizeReviewer($request);

    if ($poliza->estado !== \App\Enums\PolizaEstado::PENDIENTE_REVISION) {
        return response()->json([
            'ok' => false,
            'mensaje' => 'La póliza no se encuentra pendiente de revisión.',
        ], 422);
    }

    return response()->json([
        'ok' => true,
        'data' => $service->detalleRevision($poliza),
    ]);
}

public function polizas(Request $request, ActivosFijosService $service)
{
    Gate::authorize(
        'viewAny',
        Poliza::class
    );

    $this->authorizeReviewer($request);

    $datos = $request->validate([
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

        'unidad_organizacional_id' => [
            'nullable',
            'integer',
            'exists:unidades_organizacionales,id',
        ],

        'tipo_poliza_id' => [
            'nullable',
            'integer',
            'exists:tipos_poliza,id',
        ],

        'buscar' => [
            'nullable',
            'string',
            'max:100',
        ],
    ]);

    $porPagina = min(
        (int) $request->input('per_page', 15),
        100
    );

    $polizas = $service->polizas(
        $datos['estado'] ?? null,
        $datos['unidad_organizacional_id'] ?? null,
        $datos['tipo_poliza_id'] ?? null,
        $datos['buscar'] ?? null,
        $porPagina
    );

    return response()->json([
        'ok' => true,
        'data' => $polizas,
    ]);
}

public function detallePoliza(
    Request $request,
    Poliza $poliza,
    ActivosFijosService $service
) {
    Gate::authorize(
        'viewAny',
        Poliza::class
    );

    $this->authorizeReviewer($request);

    return response()->json([
        'ok' => true,
        'data' => $service->detallePoliza($poliza),
    ]);
}

private function authorizeReviewer(Request $request): void
{
    abort_unless(
        $request->user()?->hasAnyRole([
            'ACTIVOS_FIJOS',
            'ADMINISTRADOR',
        ]),
        403
    );
}

}
