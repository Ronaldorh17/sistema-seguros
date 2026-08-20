<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnidadOrganizacional extends Model
{
    protected $table = 'unidades_organizacionales';

    protected $fillable = [
        'rrhh_id',
        'nombre',
        'activo',
        'fecha_inicio',
        'fecha_fin',
        'ultima_sincronizacion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'ultima_sincronizacion' => 'datetime',
    ];

    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class);
    }

    public function polizas(): HasMany
    {
        return $this->hasMany(Poliza::class);
    }
}