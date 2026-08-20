<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('persona_id')
                ->nullable()
                ->after('id')
                ->constrained('personas')
                ->nullOnDelete();

            $table->index('persona_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropIndex(['persona_id']);
            $table->dropColumn('persona_id');
        });
    }
};