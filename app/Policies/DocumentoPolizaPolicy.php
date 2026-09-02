<?php

namespace App\Policies;

use App\Models\DocumentoPoliza;
use App\Models\User;
use App\Enums\PolizaEstado;

class DocumentoPolizaPolicy
{
    public function upload(User $user, DocumentoPoliza $documento): bool
    {
        $poliza = $documento->poliza;

        if (!$poliza) {
            return false;
        }

        if (!$user->hasRole('SERVICIO_EXTERIOR')) {
            return false;
        }

        if (!$user->persona) {
            return false;
        }

        if (
            $user->persona->unidad_organizacional_id
            !== $poliza->unidad_organizacional_id
        ) {
            return false;
        }

        return in_array($poliza->estado, [
            PolizaEstado::BORRADOR,
            PolizaEstado::OBSERVADA,
        ], true);
    }

    public function view(User $user, DocumentoPoliza $documento): bool
{
    $poliza = $documento->poliza;

    if (!$poliza || !$user->persona) {
        return false;
    }

    // Administrador y Activos Fijos pueden consultar documentos
    if ($user->hasAnyRole([
        'ADMINISTRADOR',
        'ACTIVOS_FIJOS',
    ])) {
        return true;
    }

    // Servicio Exterior solo puede consultar
    // documentos de su propia Unidad Organizacional
    if ($user->hasRole('SERVICIO_EXTERIOR')) {
        return $user->persona->unidad_organizacional_id
            === $poliza->unidad_organizacional_id;
    }

    return false;
}

public function delete(User $user, DocumentoPoliza $documento): bool
{
    $poliza = $documento->poliza;

    if (!$poliza || !$user->persona) {
        return false;
    }

    if (!$user->hasRole('SERVICIO_EXTERIOR')) {
        return false;
    }

    if (
        $user->persona->unidad_organizacional_id
        !== $poliza->unidad_organizacional_id
    ) {
        return false;
    }

    return in_array($poliza->estado, [
        PolizaEstado::BORRADOR,
        PolizaEstado::OBSERVADA,
    ], true);
}
}