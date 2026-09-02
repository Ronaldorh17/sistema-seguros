<?php

namespace App\Services;

use App\Models\Poliza;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PolizaService
{
    public function listar(int $porPagina = 15): LengthAwarePaginator
    {
        $query = Poliza::query()
            ->with([
                'unidadOrganizacional:id,rrhh_id,nombre',
                'tipoPoliza:id,nombre',
            ])
            ->orderByDesc('id');

        $user = Auth::user();
        /** @var \App\Models\User $user */

        /*
        |--------------------------------------------------------------------------
        | SERVICIO EXTERIOR
        |--------------------------------------------------------------------------
        | Solo puede consultar las pólizas de su Unidad Organizacional.
        */
        if ($user->hasAnyRole([
            'ACTIVOS_FIJOS',
            'ADMINISTRADOR',
        ])) {
            return $query->paginate($porPagina);
        }

        if ($user->hasRole('SERVICIO_EXTERIOR')) {

            $unidadId = $user->persona?->unidad_organizacional_id;

            if (!$unidadId) {
                return $query
                    ->whereRaw('1 = 0')
                    ->paginate($porPagina);
            }

            $query->where(
                'unidad_organizacional_id',
                $unidadId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ACTIVOS FIJOS / ADMINISTRADOR
        |--------------------------------------------------------------------------
        | Pueden consultar todas las unidades.
        */

        return $query->paginate($porPagina);
    }

    public function crear(array $datos): Poliza
{
    $user = Auth::user();

    $persona = $user->persona;

    if (!$persona) {
        throw new \RuntimeException(
            'El usuario no tiene una persona asociada.'
        );
    }

    if (!$persona->unidad_organizacional_id) {
        throw new \RuntimeException(
            'La persona no tiene una Unidad Organizacional asociada.'
        );
    }

    return DB::transaction(function () use ($datos, $persona, $user) {

        $poliza = Poliza::create([
            'unidad_organizacional_id' => $persona->unidad_organizacional_id,

            'tipo_poliza_id' => $datos['tipo_poliza_id'],
            'numero_poliza' => $datos['numero_poliza'],
            'compania_aseguradora' => $datos['compania_aseguradora'],

            'vigencia_desde' => $datos['vigencia_desde'],
            'vigencia_hasta' => $datos['vigencia_hasta'],

            'moneda_monto' => $datos['moneda_monto'],
            'monto_asegurado' => $datos['monto_asegurado'],

            'moneda_prima' => $datos['moneda_prima'],
            'prima_seguro' => $datos['prima_seguro'],

            'observaciones' => $datos['observaciones'] ?? null,

            'estado' => \App\Enums\PolizaEstado::BORRADOR,

            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $poliza->historial()->create([
            'usuario_id' => $user->id,
            'accion' => 'CREAR',
            'estado_anterior' => null,
            'estado_nuevo' => \App\Enums\PolizaEstado::BORRADOR->value,
            'observacion' => 'Registro inicial de la póliza.',
        ]);

        return $poliza->load([
            'unidadOrganizacional:id,rrhh_id,nombre',
            'tipoPoliza:id,nombre',
            'historial',
        ]);
    });
}
public function actualizar(
    Poliza $poliza,
    array $datos
): Poliza {
    return DB::transaction(function () use ($poliza, $datos) {

        $cambios = [];

        foreach ($datos as $campo => $valor) {
            if ($poliza->{$campo} != $valor) {
                $cambios[$campo] = [
                    'anterior' => $poliza->{$campo},
                    'nuevo' => $valor,
                ];
            }
        }

        $usuarioId = Auth::id();

        $poliza->update([
            'tipo_poliza_id' => $datos['tipo_poliza_id'],
            'numero_poliza' => $datos['numero_poliza'],
            'compania_aseguradora' => $datos['compania_aseguradora'],
            'vigencia_desde' => $datos['vigencia_desde'],
            'vigencia_hasta' => $datos['vigencia_hasta'],
            'moneda_monto' => $datos['moneda_monto'],
            'monto_asegurado' => $datos['monto_asegurado'],
            'moneda_prima' => $datos['moneda_prima'],
            'prima_seguro' => $datos['prima_seguro'],
            'observaciones' => $datos['observaciones'] ?? null,
            'updated_by' => $usuarioId,
        ]);

        $poliza->historial()->create([
            'usuario_id' => $usuarioId,
            'accion' => 'ACTUALIZAR',
            'estado_anterior' => $poliza->estado->value,
            'estado_nuevo' => $poliza->estado->value,
            'observacion' => 'Actualización de datos de la póliza.',
        ]);

        return $poliza->fresh([
            'unidadOrganizacional',
            'tipoPoliza',
            'historial',
            'documentos',
        ]);
    });
}

public function misPolizas(
    int $unidadOrganizacionalId,
    ?string $estado = null,
    ?string $buscar = null
) {
    $query = Poliza::with([
        'unidadOrganizacional:id,rrhh_id,nombre',
        'tipoPoliza:id,nombre',
    ])
    ->withCount('documentos')
    ->where(
        'unidad_organizacional_id',
        $unidadOrganizacionalId
    );

    if ($estado) {
        $query->where('estado', $estado);
    }

    if ($buscar) {
        $query->where(function ($q) use ($buscar) {
            $q->where(
                'numero_poliza',
                'ILIKE',
                "%{$buscar}%"
            )
            ->orWhere(
                'compania_aseguradora',
                'ILIKE',
                "%{$buscar}%"
            );
        });
    }

    return $query
        ->orderBy('created_at', 'desc')
        ->paginate(15);
}

public function resumenMisPolizas(
    int $unidadOrganizacionalId
): array {
    $base = Poliza::where(
        'unidad_organizacional_id',
        $unidadOrganizacionalId
    );

    return [
        'total' => (clone $base)->count(),

        'borradores' => (clone $base)
            ->where('estado', \App\Enums\PolizaEstado::BORRADOR)
            ->count(),

        'pendientes_revision' => (clone $base)
            ->where('estado', \App\Enums\PolizaEstado::PENDIENTE_REVISION)
            ->count(),

        'observadas' => (clone $base)
            ->where('estado', \App\Enums\PolizaEstado::OBSERVADA)
            ->count(),

        'validadas' => (clone $base)
            ->where('estado', \App\Enums\PolizaEstado::VALIDADA)
            ->count(),

        'bloqueadas' => (clone $base)
            ->where('estado', \App\Enums\PolizaEstado::BLOQUEADA)
            ->count(),

        'por_vencer' => (clone $base)
            ->whereNotIn('estado', [
                \App\Enums\PolizaEstado::BLOQUEADA,
            ])
            ->whereBetween(
                'vigencia_hasta',
                [
                    now()->toDateString(),
                    now()->addDays(30)->toDateString(),
                ]
            )
            ->count(),
    ];
}

public function detalleMiPoliza(Poliza $poliza): Poliza
{
    return $poliza->load([
        'unidadOrganizacional:id,rrhh_id,nombre',
        'tipoPoliza:id,nombre',
        'documentos' => function ($query) {
            $query->orderBy('created_at', 'desc');
        },
        'documentos.usuario:id,name',
        'historial' => function ($query) {
            $query->orderBy('created_at', 'asc');
        },
        'historial.usuario:id,name',
    ]);
}

}
