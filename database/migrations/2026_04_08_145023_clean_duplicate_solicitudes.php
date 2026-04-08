<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Limpia las solicitudes duplicadas que fueron creadas mientras
     * estaban activas las plantillas duplicadas.
     */
    public function up(): void
    {
        // Eliminar solicitudes duplicadas: para cada (proceso_ingreso_id, tipo)
        // mantener solo la solicitud con el ID más bajo
        DB::statement('
            DELETE FROM solicitudes 
            WHERE id NOT IN (
                SELECT MIN(id) 
                FROM solicitudes 
                GROUP BY proceso_ingreso_id, tipo
            )
        ');
    }

    /**
     * Reverse the migrations.
     * 
     * NOTA: No se pueden restaurar las solicitudes eliminadas.
     * Esta migración es de una sola dirección.
     */
    public function down(): void
    {
        // No se puede deshacer esta migración de forma segura
        throw new \Exception('No se puede revertir esta migración. Las solicitudes duplicadas fueron eliminadas permanentemente.');
    }
};

