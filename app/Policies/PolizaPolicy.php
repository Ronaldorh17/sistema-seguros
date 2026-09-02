<?php

namespace App\Policies;

use App\Models\Poliza;
use App\Models\User;
use App\Enums\PolizaEstado;

class PolizaPolicy
{
    /**
     * Ver una póliza específica.
     */
    public function view(User $user, Poliza $poliza): bool
    {
        if ($user->hasAnyRole([
            'ACTIVOS_FIJOS',
            'ADMINISTRADOR',
        ])) {
            return true;
        }

        if ($user->hasRole('SERVICIO_EXTERIOR')) {
            return $user->persona
                && $user->persona->unidad_organizacional_id
                    === $poliza->unidad_organizacional_id;
        }

        return false;
    }

    /**
     * Crear una póliza.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'SERVICIO_EXTERIOR',
            'ACTIVOS_FIJOS',
            'ADMINISTRADOR',
        ]);
    }

    /**
     * Editar una póliza.
     */
public function update(User $user, Poliza $poliza): bool
{
    if ($user->hasRole('ADMINISTRADOR')) {
        return true;
    }

    if ($user->hasRole('ACTIVOS_FIJOS')) {
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

    /**
     * Eliminar una póliza.
     */
    public function delete(User $user, Poliza $poliza): bool
    {
        if ($user->hasRole('ADMINISTRADOR')) {
            return true;
        }

        if ($user->hasRole('SERVICIO_EXTERIOR')) {
            return $user->persona
                && $user->persona->unidad_organizacional_id
                    === $poliza->unidad_organizacional_id
                && $poliza->estado === PolizaEstado::BORRADOR;
        }

        return false;
    }

    /**
     * Observar una póliza.
     */
    public function observe(User $user, Poliza $poliza): bool
    {
        return $user->hasAnyRole([
            'ACTIVOS_FIJOS',
            'ADMINISTRADOR',
        ])
        && $poliza->estado === PolizaEstado::PENDIENTE_REVISION;
    }

    /**
     * Validar una póliza.
     */
    public function validate(User $user, Poliza $poliza): bool
    {
        return $user->hasAnyRole([
            'ACTIVOS_FIJOS',
            'ADMINISTRADOR',
        ])
        && $poliza->estado === PolizaEstado::PENDIENTE_REVISION;
    }

    /**
     * Bloquear una póliza.
     */
    public function block(User $user, Poliza $poliza): bool
    {
        return $user->hasAnyRole([
            'ACTIVOS_FIJOS',
            'ADMINISTRADOR',
        ])
        && $poliza->estado === PolizaEstado::VALIDADA;
    }

    public function viewAny(User $user): bool
{
    return $user->hasAnyRole([
        'SERVICIO_EXTERIOR',
        'ACTIVOS_FIJOS',
        'ADMINISTRADOR',
    ]);
}

public function sendForReview(User $user, Poliza $poliza): bool
{
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
}