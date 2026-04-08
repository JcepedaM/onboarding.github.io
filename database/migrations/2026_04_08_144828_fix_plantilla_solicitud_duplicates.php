<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Eliminar duplicados - mantener el registro con ID más bajo
        DB::statement('
            DELETE FROM plantilla_solicitudes 
            WHERE id NOT IN (
                SELECT MIN(id) 
                FROM plantilla_solicitudes 
                GROUP BY cargo_id, tipo_solicitud, area_id
            )
        ');

        // 2. Agregar índice único para prevenir futuros duplicados
        Schema::table('plantilla_solicitudes', function (Blueprint $table) {
            $table->unique(['cargo_id', 'tipo_solicitud', 'area_id'], 'plantilla_solicitud_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantilla_solicitudes', function (Blueprint $table) {
            $table->dropUnique('plantilla_solicitud_unique');
        });
    }
};

