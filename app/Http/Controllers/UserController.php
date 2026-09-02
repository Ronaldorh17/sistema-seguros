<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listar usuarios.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $porPagina = min(
            (int) $request->input('per_page', 15),
            100
        );

        $buscar = $request->input('buscar');

        $query = User::query()
            ->with([
                'persona:id,nombre_completo,unidad_organizacional_id',
                'persona.unidadOrganizacional:id,nombre',
                'roles:id,name',
            ])
            ->orderBy('name');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'ILIKE', "%{$buscar}%")
                    ->orWhere(
                        'usuario_rree',
                        'ILIKE',
                        "%{$buscar}%"
                    );
            });
        }

        $usuarios = $query->paginate($porPagina);

        return response()->json([
            'ok' => true,
            'data' => $usuarios,
        ]);
    }

    /**
     * Ver detalle de usuario.
     */
    public function show(User $user)
    {
        Gate::authorize('view', $user);

        $user->load([
            'persona:id,nombre_completo,unidad_organizacional_id',
            'persona.unidadOrganizacional:id,nombre',
            'roles:id,name',
        ]);

        return response()->json([
            'ok' => true,
            'data' => $user,
        ]);
    }

    /**
     * Cambiar rol del usuario.
     */
    public function updateRole(
        Request $request,
        User $user
    ) {
        Gate::authorize('updateRole', $user);

        $datos = $request->validate([
            'rol' => [
                'required',
                'string',
                Rule::in([
                    'SERVICIO_EXTERIOR',
                    'ACTIVOS_FIJOS',
                    'ADMINISTRADOR',
                ]),
            ],
        ]);

        $user->syncRoles([
            $datos['rol'],
        ]);

        $user->load('roles:id,name');

        return response()->json([
            'ok' => true,
            'mensaje' => 'Rol actualizado correctamente.',
            'data' => $user,
        ]);
    }
}