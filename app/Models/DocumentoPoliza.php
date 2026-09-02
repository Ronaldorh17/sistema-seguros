<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoPoliza extends Model
{
    protected $table = 'documentos_poliza';

    protected $fillable = [
        'poliza_id',
        'nombre_original',
        'nombre_archivo',
        'ruta',
        'mime_type',
        'tamano',
        'hash',
        'created_by',
    ];

    public function poliza(): BelongsTo
    {
        return $this->belongsTo(
            Poliza::class,
            'poliza_id'
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}