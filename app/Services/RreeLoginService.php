<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RreeLoginService
{
    public function __construct(
        protected RreeAuthService $rreeAuth,
        protected RreeRrhhService $rreeRrhh,
        protected PersonaSyncService $personaSync
    ) {
    }

    public function login(
        string $usuario,
        string $contrasena,
        string $ip
    ): array {
        // 1. Autenticar contra RREE
        $respuesta = $this->rreeAuth->signIn(
            $usuario,
            $contrasena,
            'Personal',
            $ip
        );

        if (!$respuesta || empty($respuesta['token'])) {
            throw ValidationException::withMessages([
                'usuario' => 'Las credenciales proporcionadas no son válidas.'
            ]);
        }

        // 2. Obtener información del JWT
        $jwt = $this->rreeAuth->decodeJwt(
            $respuesta['token']
        );

        if (!$jwt || empty($jwt['nameid'])) {
            throw ValidationException::withMessages([
                'usuario' => 'No fue posible obtener la identidad del usuario.'
            ]);
        }

        $nameid = (string) $jwt['nameid'];

        // 3. Consultar RRHH
        $rrhhResponse = $this->rreeRrhh->getUserById($nameid);

        if (!$rrhhResponse || empty($rrhhResponse['objeto'])) {
            throw ValidationException::withMessages([
                'usuario' => 'No fue posible obtener la información del funcionario desde RRHH.'
            ]);
        }

        // 4. Sincronizar Persona y Unidad Organizacional
        $persona = $this->personaSync->sincronizar(
            $rrhhResponse['objeto'],
            $nameid
        );

        // 5. Crear/actualizar usuario local
        $user = DB::transaction(function () use ($persona, $jwt, $usuario) {
        
            return User::updateOrCreate(
                [
                    'persona_id' => $persona->id,
                ],
 [
    'name' => $persona->nombre_completo,
    'usuario_rree' => $usuario,
]
            );
        });
        if (!$user->hasAnyRole([
    'SERVICIO_EXTERIOR',
    'ACTIVOS_FIJOS',
    'ADMINISTRADOR',
])) {
    $user->assignRole('SERVICIO_EXTERIOR');
}
        // 6. Crear token Sanctum
        $token = $user->createToken(
            'sistema-seguros'
        )->plainTextToken;

        return [
            'token' => $token,
            'user' => $user->load(
                'persona.unidadOrganizacional',
                'roles:id,name'
            ),
            'rree' => [
                'nameid' => $nameid,
                'rol' => $jwt['role'] ?? null,
            ],
        ];
    }
}
