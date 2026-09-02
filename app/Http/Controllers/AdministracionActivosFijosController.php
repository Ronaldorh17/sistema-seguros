<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdministracionActivosFijosController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdministrator($request);

        $usuarios = User::query()
            ->with([
                'roles:id,name',
                'persona.unidadOrganizacional:id,nombre',
            ])
            ->orderBy('name')
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $usuarios,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAdministrator($request);

        $datos = $request->validate([
            'activo' => ['required', 'boolean'],
        ]);

        if ($datos['activo']) {
            $user->assignRole('ACTIVOS_FIJOS');
        } else {
            $user->removeRole('ACTIVOS_FIJOS');
        }

        return response()->json([
            'ok' => true,
            'mensaje' => $datos['activo']
                ? 'Rol de Activos Fijos asignado correctamente.'
                : 'Rol de Activos Fijos retirado correctamente.',
            'data' => $user->fresh([
                'roles:id,name',
                'persona.unidadOrganizacional:id,nombre',
            ]),
        ]);
    }

    private function authorizeAdministrator(Request $request): void
    {
        abort_unless(
            $request->user()?->hasRole('ADMINISTRADOR'),
            403
        );
    }
}
