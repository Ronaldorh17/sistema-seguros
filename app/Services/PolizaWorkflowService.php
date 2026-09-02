<?php

namespace App\Services;

use App\Enums\PolizaEstado;
use App\Models\Poliza;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PolizaWorkflowService
{
    public function enviarRevision(Poliza $poliza): Poliza
    {
        if (
            !in_array($poliza->estado, [
                PolizaEstado::BORRADOR,
                PolizaEstado::OBSERVADA,
            ], true)
        ) {
            throw new InvalidArgumentException(
                'La póliza no puede ser enviada a revisión desde su estado actual.'
            );
        }

        return DB::transaction(function () use ($poliza) {

            $estadoAnterior = $poliza->estado;

             $accion = $estadoAnterior === PolizaEstado::OBSERVADA
            ? 'REENVIAR_REVISION'
            : 'ENVIAR_REVISION';


            $poliza->update([
                'estado' => PolizaEstado::PENDIENTE_REVISION,
                'fecha_envio_revision' => now(),
                'updated_by' => Auth::id(),
            ]);

        $poliza->historial()->create([
            'usuario_id' => Auth::id(),
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior->value,
            'estado_nuevo' => PolizaEstado::PENDIENTE_REVISION->value,
            'observacion' => $estadoAnterior === PolizaEstado::OBSERVADA
                ? 'La póliza fue corregida y reenviada a revisión.'
                : 'La póliza fue enviada a revisión.',
        ]);

        return $poliza->fresh([
            'unidadOrganizacional',
            'tipoPoliza',
            'historial',
            'documentos',
        ]);
        });
    }
    public function observar(
    Poliza $poliza,
    string $observacion
): Poliza {

    if ($poliza->estado !== PolizaEstado::PENDIENTE_REVISION) {
        throw new InvalidArgumentException(
            'La póliza no se encuentra pendiente de revisión.'
        );
    }

    if (trim($observacion) === '') {
        throw new InvalidArgumentException(
            'Debe registrar una observación.'
        );
    }

    return DB::transaction(function () use ($poliza, $observacion) {

        $estadoAnterior = $poliza->estado;

        $poliza->update([
            'estado' => PolizaEstado::OBSERVADA,
            'updated_by' => Auth::id(),
        ]);

        $poliza->historial()->create([
            'usuario_id' => Auth::id(),
            'accion' => 'OBSERVAR',
            'estado_anterior' => $estadoAnterior->value,
            'estado_nuevo' => PolizaEstado::OBSERVADA->value,
            'observacion' => trim($observacion),
        ]);

        return $poliza->fresh([
            'unidadOrganizacional',
            'tipoPoliza',
            'historial',
        ]);
    });
}

    public function validar(Poliza $poliza): Poliza
{
    if ($poliza->estado !== PolizaEstado::PENDIENTE_REVISION) {
        throw new InvalidArgumentException(
            'La póliza no se encuentra pendiente de revisión.'
        );
    }

    return DB::transaction(function () use ($poliza) {

        $estadoAnterior = $poliza->estado;

        $poliza->update([
            'estado' => PolizaEstado::VALIDADA,
            'fecha_validacion' => now(),
            'updated_by' => Auth::id(),
        ]);

        $poliza->historial()->create([
            'usuario_id' => Auth::id(),
            'accion' => 'VALIDAR',
            'estado_anterior' => $estadoAnterior->value,
            'estado_nuevo' => PolizaEstado::VALIDADA->value,
            'observacion' => 'La póliza fue validada correctamente.',
        ]);

        return $poliza->fresh([
            'unidadOrganizacional',
            'tipoPoliza',
            'historial',
        ]);
    });
}

public function bloquear(Poliza $poliza): Poliza
{
    if ($poliza->estado !== PolizaEstado::VALIDADA) {
        throw new InvalidArgumentException(
            'La póliza no se encuentra validada.'
        );
    }

    return DB::transaction(function () use ($poliza) {

        $estadoAnterior = $poliza->estado;

        $poliza->update([
            'estado' => PolizaEstado::BLOQUEADA,
            'updated_by' => Auth::id(),
        ]);

        $poliza->historial()->create([
            'usuario_id' => Auth::id(),
            'accion' => 'BLOQUEAR',
            'estado_anterior' => $estadoAnterior->value,
            'estado_nuevo' => PolizaEstado::BLOQUEADA->value,
            'observacion' => 'La póliza fue bloqueada.',
        ]);

        return $poliza->fresh([
            'unidadOrganizacional',
            'tipoPoliza',
            'historial',
        ]);
    });
}


}