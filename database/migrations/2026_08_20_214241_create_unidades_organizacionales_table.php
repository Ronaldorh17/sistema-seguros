<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_organizacionales', function (Blueprint $table) {
            $table->id();

            // Identificador de la Unidad Organizacional en RRHH
            $table->unsignedBigInteger('rrhh_id')->unique();

            $table->string('nombre', 255);

            $table->boolean('activo')->default(true);

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->timestamp('ultima_sincronizacion')->nullable();

            $table->timestamps();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_organizacionales');
    }
};