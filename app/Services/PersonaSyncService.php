<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\UnidadOrganizacional;
use Illuminate\Support\Facades\DB;

class PersonaSyncService
{
    public function sincronizar(
        array $personaData,
        string $rreeNameid
    ): Persona {
        return DB::transaction(function () use ($personaData, $rreeNameid) {

            $unidad = UnidadOrganizacional::updateOrCreate(
                [
                    'rrhh_id' => $personaData['uniOrganizacionalId'],
                ],
                [
                    'nombre' => $personaData['uniOrganizacional'],
                    'activo' => true,
                    'ultima_sincronizacion' => now(),
                ]
            );

            return Persona::updateOrCreate(
                [
                    'rrhh_persona_id' => $personaData['id'],
                ],
                [
                    'rree_nameid' => $rreeNameid,
                    'prim_apellido' => $personaData['primApellido'] ?? null,
                    'seg_apellido' => $personaData['segApellido'] ?? null,
                    'nombre' => $personaData['nombre'] ?? null,
                    'nombre_completo' => $personaData['nombreCompleto'],
                    'num_documento' => $personaData['numDocumento'] ?? null,
                    'expedicion' => $personaData['expedicion'] ?? null,
                    'unidad_organizacional_id' => $unidad->id,
                    'puesto' => $personaData['puesto'] ?? null,
                    'cargo' => $personaData['cargo'] ?? null,
                    'categoria' => $personaData['categoria'] ?? null,
                    'telefono' => $personaData['telefono'] ?? null,
                    'pais' => $personaData['pais'] ?? null,
                    'ultima_sincronizacion' => now(),
                ]
            );
        });
    }
}