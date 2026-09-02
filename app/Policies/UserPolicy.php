<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Ver listado de usuarios.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('ADMINISTRADOR');
    }

    /**
     * Ver un usuario.
     */
    public function view(User $user, User $target): bool
    {
        return $user->hasRole('ADMINISTRADOR');
    }

    /**
     * Cambiar el rol de un usuario.
     */
    public function updateRole(User $user, User $target): bool
    {
        if (!$user->hasRole('ADMINISTRADOR')) {
            return false;
        }

        // Evitamos que el administrador
        // pueda quitarse su propio rol.
        if ($user->id === $target->id) {
            return false;
        }

        return true;
    }
}