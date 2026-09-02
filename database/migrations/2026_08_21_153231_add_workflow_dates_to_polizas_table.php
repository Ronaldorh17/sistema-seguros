<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->timestamp('fecha_envio_revision')
                ->nullable()
                ->after('estado');

            $table->timestamp('fecha_validacion')
                ->nullable()
                ->after('fecha_envio_revision');
        });
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_envio_revision',
                'fecha_validacion',
            ]);
        });
    }
};