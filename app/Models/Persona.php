<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $table = 'personas';

    protected $fillable = [
        'rrhh_persona_id',
        'rree_nameid',
        'prim_apellido',
        'seg_apellido',
        'nombre',
        'nombre_completo',
        'num_documento',
        'expedicion',
        'unidad_organizacional_id',
        'puesto',
        'cargo',
        'categoria',
        'telefono',
        'pais',
        'ultima_sincronizacion',
    ];

    protected $casts = [
        'categoria' => 'integer',
        'ultima_sincronizacion' => 'datetime',
    ];

    public function unidadOrganizacional(): BelongsTo
    {
        return $this->belongsTo(
            UnidadOrganizacional::class,
            'unidad_organizacional_id'
        );
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}