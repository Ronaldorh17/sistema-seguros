<?php

namespace App\Services;

use App\Models\DocumentoPoliza;
use App\Models\Poliza;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoPolizaService
{
    public function subir(
        Poliza $poliza,
        UploadedFile $archivo
    ): DocumentoPoliza {

        return DB::transaction(function () use ($poliza, $archivo) {

            /*
             * Generamos un nombre interno que no dependa
             * del nombre enviado por el usuario.
             */
            $nombreArchivo = Str::uuid()
                .'.'
                .$archivo->extension();

            /*
             * Guardamos dentro de storage/app/private.
             */
            $ruta = $archivo->storeAs(
                'polizas/'.$poliza->id,
                $nombreArchivo,
                'local'
            );

            /*
             * Hash SHA-256 del archivo.
             */
            $hash = hash_file(
                'sha256',
                $archivo->getRealPath()
            );

            return DocumentoPoliza::create([
                'poliza_id' => $poliza->id,
                'nombre_original' => $archivo->getClientOriginalName(),
                'nombre_archivo' => $nombreArchivo,
                'ruta' => $ruta,
                'mime_type' => $archivo->getMimeType(),
                'tamano' => $archivo->getSize(),
                'hash' => $hash,
                'created_by' => Auth::id(),
            ]);
        });
    }
    public function descargar(DocumentoPoliza $documento)
{
    if (!Storage::disk('local')->exists($documento->ruta)) {
        throw new \RuntimeException(
            'El archivo no existe en el almacenamiento.'
        );
    }

    return response()->download(
        Storage::disk('local')->path($documento->ruta),
        $documento->nombre_original,
        [
            'Content-Type' => $documento->mime_type,
            'Content-Disposition' => 'attachment; filename="' .
                $documento->nombre_original .
                '"',
        ]
    );
}

public function eliminar(DocumentoPoliza $documento): void
{
    DB::transaction(function () use ($documento) {

        $ruta = $documento->ruta;
        $poliza = $documento->poliza;

        if (Storage::disk('local')->exists($ruta)) {
            Storage::disk('local')->delete($ruta);
        }

        $documento->delete();

        $poliza->historial()->create([
            'usuario_id' => Auth::id(),
            'accion' => 'DOCUMENTO_ELIMINADO',
            'estado_anterior' => $poliza->estado->value,
            'estado_nuevo' => $poliza->estado->value,
            'observacion' => 'Se eliminó el documento: ' .
                $documento->nombre_original,
        ]);
    });
}
}