<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();

            // Identificador de persona proveniente de RRHH
            $table->unsignedBigInteger('rrhh_persona_id')->unique();

            // Identificador proveniente del JWT de autenticación RREE
            $table->string('rree_nameid', 100)->unique();

            $table->string('prim_apellido', 100)->nullable();
            $table->string('seg_apellido', 100)->nullable();
            $table->string('nombre', 150)->nullable();

            $table->string('nombre_completo', 300);

            $table->string('num_documento', 50)->nullable();
            $table->string('expedicion', 100)->nullable();

            $table->foreignId('unidad_organizacional_id')
                ->nullable()
                ->constrained('unidades_organizacionales')
                ->nullOnDelete();

            $table->string('puesto', 200)->nullable();
            $table->string('cargo', 200)->nullable();

            $table->unsignedInteger('categoria')->nullable();

            $table->string('telefono', 50)->nullable();
            $table->string('pais', 100)->nullable();

            $table->timestamp('ultima_sincronizacion')->nullable();

            $table->timestamps();

            $table->index('unidad_organizacional_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};