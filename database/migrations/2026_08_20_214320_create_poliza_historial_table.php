<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poliza_historial', function (Blueprint $table) {
            $table->id();

            $table->foreignId('poliza_id')
                ->constrained('polizas')
                ->cascadeOnDelete();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('accion', 100);

            $table->string('estado_anterior', 30)->nullable();

            $table->string('estado_nuevo', 30)->nullable();

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->index('poliza_id');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poliza_historial');
    }
};