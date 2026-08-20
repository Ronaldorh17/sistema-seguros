<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_poliza', function (Blueprint $table) {
            $table->id();

            $table->foreignId('poliza_id')
                ->constrained('polizas')
                ->cascadeOnDelete();

            $table->string('nombre_original', 255);

            $table->string('nombre_archivo', 255);

            $table->string('ruta', 500);

            $table->string('mime_type', 100);

            $table->unsignedBigInteger('tamano')->nullable();

            $table->string('hash', 128)->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('poliza_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_poliza');
    }
};