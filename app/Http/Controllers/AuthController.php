<?php

namespace App\Http\Controllers;

use App\Services\RreeLoginService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(
        Request $request,
        RreeLoginService $loginService
    ) {
        $validated = $request->validate([
            'usuario' => ['required', 'string'],
            'contrasena' => ['required', 'string'],
        ]);

        try {

            $resultado = $loginService->login(
                $validated['usuario'],
                $validated['contrasena'],
                $request->ip()
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Inicio de sesión satisfactorio.',
                'data' => $resultado,
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => $e->getMessage(),
                'errores' => $e->errors(),
            ], 401);
        }
    }

    public function me(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $request->user()->load(
                'persona.unidadOrganizacional'
            ),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'ok' => true,
            'mensaje' => 'Sesión cerrada correctamente.',
        ]);
    }
}