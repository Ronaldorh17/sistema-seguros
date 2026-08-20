<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polizas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unidad_organizacional_id')
                ->constrained('unidades_organizacionales')
                ->restrictOnDelete();

            $table->foreignId('tipo_poliza_id')
                ->constrained('tipos_poliza')
                ->restrictOnDelete();

            $table->string('numero_poliza', 150);

            $table->string('compania_aseguradora', 255);

            $table->date('vigencia_desde');

            $table->date('vigencia_hasta');

            $table->string('moneda_monto', 10);

            $table->decimal('monto_asegurado', 15, 2);

            $table->string('moneda_prima', 10);

            $table->decimal('prima_seguro', 15, 2);

            $table->text('observaciones')->nullable();

            $table->string('estado', 30)->default('BORRADOR');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('estado');
            $table->index('vigencia_hasta');

            // Evita duplicar una misma póliza para una misma unidad.
            $table->unique([
                'unidad_organizacional_id',
                'numero_poliza'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polizas');
    }
};