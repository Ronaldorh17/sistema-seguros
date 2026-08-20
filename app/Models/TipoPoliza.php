<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPoliza extends Model
{
    protected $table = 'tipos_poliza';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function polizas(): HasMany
    {
        return $this->hasMany(Poliza::class);
    }
}