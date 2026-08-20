<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RreeAuthService
{
    protected string $authUrl;

    public function __construct()
    {
        $this->authUrl = config('services.rree.auth_url');
    }

    public function signIn(
        string $usuario,
        string $contrasena,
        string $grupo,
        string $ip
    ): ?array {

        $response = Http::withOptions([
            'verify' => false,
            'timeout' => 30,
        ])->post($this->authUrl, [
            'a' => $usuario,
            'b' => $contrasena,
            'c' => $grupo,
            'd' => $ip,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Error al hacer login con RREE', [
            'status' => $response->status(),
            'response' => $response->body(),
        ]);

        return null;
    }

    public function decodeJwt(string $token): ?array
    {
        try {
            $partes = explode('.', $token);

            if (count($partes) !== 3) {
                return null;
            }

            $payload = $partes[1];

            $payload .= str_repeat(
                '=',
                (4 - strlen($payload) % 4) % 4
            );

            return json_decode(
                base64_decode(
                    strtr($payload, '-_', '+/')
                ),
                true
            );

        } catch (\Throwable $e) {

            Log::error('Error decodificando JWT', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}