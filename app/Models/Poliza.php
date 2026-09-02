<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\PolizaEstado;

class Poliza extends Model
{
    protected $table = 'polizas';

protected $fillable = [
    'unidad_organizacional_id',
    'tipo_poliza_id',
    'numero_poliza',
    'compania_aseguradora',
    'vigencia_desde',
    'vigencia_hasta',
    'moneda_monto',
    'monto_asegurado',
    'moneda_prima',
    'prima_seguro',
    'observaciones',
    'estado',
    'fecha_envio_revision',
    'fecha_validacion',
    'created_by',
    'updated_by',
];

    protected $casts = [
        'vigencia_desde' => 'date',
        'vigencia_hasta' => 'date',
        'fecha_envio_revision' => 'datetime',
        'fecha_validacion' => 'datetime',
        'monto_asegurado' => 'decimal:2',
        'prima_seguro' => 'decimal:2',
        'estado' => PolizaEstado::class,
    ];

    public function unidadOrganizacional(): BelongsTo
    {
        return $this->belongsTo(
            UnidadOrganizacional::class,
            'unidad_organizacional_id'
        );
    }

    public function tipoPoliza(): BelongsTo
    {
        return $this->belongsTo(
            TipoPoliza::class,
            'tipo_poliza_id'
        );
    }

public function documentos()
{
    return $this->hasMany(
        DocumentoPoliza::class,
        'poliza_id'
    );
}

    public function historial(): HasMany
    {
        return $this->hasMany(PolizaHistorial::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}