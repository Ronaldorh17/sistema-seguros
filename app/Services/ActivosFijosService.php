<?php

namespace App\Services;

use App\Models\Poliza;
use App\Enums\PolizaEstado;

class ActivosFijosService
{
    public function dashboard(): array
    {
        return [
            'borradores' => Poliza::where(
                'estado',
                PolizaEstado::BORRADOR
            )->count(),

            'pendientes_revision' => Poliza::where(
                'estado',
                PolizaEstado::PENDIENTE_REVISION
            )->count(),

            'observadas' => Poliza::where(
                'estado',
                PolizaEstado::OBSERVADA
            )->count(),

            'validadas' => Poliza::where(
                'estado',
                PolizaEstado::VALIDADA
            )->count(),

            'bloqueadas' => Poliza::where(
                'estado',
                PolizaEstado::BLOQUEADA
            )->count(),

            'por_vencer' => Poliza::whereNotIn('estado', [
                PolizaEstado::BLOQUEADA,
            ])
                ->whereBetween(
                    'vigencia_hasta',
                    [now()->toDateString(), now()->addDays(30)->toDateString()]
                )
                ->count(),
        ];
    }

    public function polizasPendientes(int $porPagina = 15)
{
    return Poliza::with([
        'unidadOrganizacional:id,rrhh_id,nombre',
        'tipoPoliza:id,nombre',
        'documentos:id,poliza_id,nombre_original,mime_type,tamano',
    ])
    ->withCount('documentos')
    ->where(
        'estado',
        PolizaEstado::PENDIENTE_REVISION
    )
    ->orderBy('fecha_envio_revision', 'asc')
    ->paginate($porPagina);
}

public function detalleRevision(Poliza $poliza): Poliza
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
        'creador:id,name,email',
        'actualizador:id,name,email',
    ]);
}

public function polizas(
    ?string $estado = null,
    ?int $unidadOrganizacionalId = null,
    ?int $tipoPolizaId = null,
    ?string $buscar = null,
    int $porPagina = 15
) {
    $query = Poliza::with([
        'unidadOrganizacional:id,rrhh_id,nombre',
        'tipoPoliza:id,nombre',
    ])
    ->withCount('documentos')
    ->with([
        'historial' => function ($query) {
            $query
                ->with('usuario:id,name')
                ->latest('created_at')
                ->limit(1);
        }
    ]);

    if ($estado) {
        $query->where('estado', $estado);
    }

    if ($unidadOrganizacionalId) {
        $query->where(
            'unidad_organizacional_id',
            $unidadOrganizacionalId
        );
    }

    if ($tipoPolizaId) {
        $query->where(
            'tipo_poliza_id',
            $tipoPolizaId
        );
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
        ->paginate($porPagina);
}

public function detallePoliza(Poliza $poliza): Poliza
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

        'creador:id,name,email',
        'actualizador:id,name,email',
    ]);
}

}