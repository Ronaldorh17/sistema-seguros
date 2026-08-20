<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RreeRrhhService
{
    protected string $userUrl;
    protected string $apiKey;
    protected string $aplicacion;

    public function __construct()
    {
        $this->userUrl = config('services.rree.rrhh_url');
        $this->apiKey = config('services.rree.api_key');
        $this->aplicacion = config('services.rree.aplicacion');
    }

    public function getUserById(string $id): ?array
{
    $response = Http::withOptions([
        'verify' => false,
        'timeout' => 30,
    ])->withHeaders([
        'Rree-Apikey' => $this->apiKey,
        'Rree-Aplicacion' => $this->aplicacion,
        'Accept' => 'application/json',
    ])->get($this->userUrl . $id);

    if ($response->successful()) {
        return $response->json();
    }

    Log::error('Error al obtener usuario de RREE', [
        'id' => $id,
        'status' => $response->status(),
        'response' => $response->body(),
    ]);

    return null;
}
}