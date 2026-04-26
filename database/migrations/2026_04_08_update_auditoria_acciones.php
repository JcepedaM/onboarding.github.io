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
        // Obtener el tipo de BD para manejar enum correctamente
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            // Para MySQL, modificar el enum agregando nuevos valores
            DB::statement("ALTER TABLE auditoria_onboarding MODIFY COLUMN accion ENUM('create', 'update', 'delete', 'view', 'export', 'anular', 'notificacion_enviada', 'notificacion_fallida', 'check-in', 'login-checkin') NOT NULL");
        } else if ($driver === 'sqlite') {
            // Para SQLite, cambiar a VARCHAR (no soporta ENUM)
            // Esta migración requeriría recrear la tabla
            Schema::table('auditoria_onboarding', function (Blueprint $table) {
                $table->string('accion')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            // Revertir a los valores anteriores (sin check-in ni login-checkin)
            DB::statement("ALTER TABLE auditoria_onboarding MODIFY COLUMN accion ENUM('create', 'update', 'delete', 'view', 'export', 'anular', 'notificacion_enviada', 'notificacion_fallida') NOT NULL");
        }
    }
};
