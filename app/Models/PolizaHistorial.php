<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolizaHistorial extends Model
{
    protected $table = 'poliza_historial';

    protected $fillable = [
        'poliza_id',
        'usuario_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'observacion',
    ];

    public function poliza(): BelongsTo
    {
        return $this->belongsTo(Poliza::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}