<?php

namespace Database\Seeders;

use App\Models\TipoPoliza;
use Illuminate\Database\Seeder;

class TipoPolizaSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Seguro de Vida',
            'Seguro Médico',
            'Seguro de Accidentes',
        ];

        foreach ($tipos as $tipo) {
            TipoPoliza::firstOrCreate([
                'nombre' => $tipo,
            ]);
        }
    }
}