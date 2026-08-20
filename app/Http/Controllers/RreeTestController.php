<?php

namespace App\Http\Controllers;

use App\Services\RreeAuthService;
use App\Services\RreeRrhhService;
use Illuminate\Http\Request;

class RreeTestController extends Controller
{
    public function login(
        Request $request,
        RreeAuthService $rreeAuth
    ) {
        $request->validate([
            'usuario' => ['required', 'string'],
            'contrasena' => ['required', 'string'],
        ]);

        $respuesta = $rreeAuth->signIn(
            $request->usuario,
            $request->contrasena,
            'Personal',
            $request->ip()
        );

        if (!$respuesta) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No fue posible autenticar con RREE.'
            ], 401);
        }

        return response()->json([
            'ok' => true,
            'respuesta' => $respuesta
        ]);
    }

    public function user(
        Request $request,
        RreeAuthService $rreeAuth,
        RreeRrhhService $rreeRrhh
    ) {
        $request->validate([
            'usuario' => ['required', 'string'],
            'contrasena' => ['required', 'string'],
        ]);

        // 1. Autenticación
        $respuesta = $rreeAuth->signIn(
            $request->usuario,
            $request->contrasena,
            'Personal',
            $request->ip()
        );

        if (!$respuesta || empty($respuesta['token'])) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No fue posible autenticar con RREE.'
            ], 401);
        }

        // 2. Decodificar JWT
        $jwt = $rreeAuth->decodeJwt($respuesta['token']);

        if (!$jwt || empty($jwt['nameid'])) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No se pudo obtener el identificador de la persona.'
            ], 401);
        }

        // 3. Consultar RRHH
        $persona = $rreeRrhh->getUserById(
            (string) $jwt['nameid']
        );

        if (!$persona) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No fue posible obtener información de RRHH.'
            ], 502);
        }

        return response()->json([
            'ok' => true,
            'nameid' => $jwt['nameid'],
            'jwt' => [
                'role' => $jwt['role'] ?? null,
                'nombreCompleto' => $jwt['NombreCompleto'] ?? null,
                'uniOrganizacional' => $jwt['UniOrganizacional'] ?? null,
                'uniOrganizacionalID' => $jwt['UniOrganizacionalID'] ?? null,
            ],
            'persona' => $persona,
        ]);
    }
}