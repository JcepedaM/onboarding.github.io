<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener todas las solicitudes donde fecha_límite < fecha de creación
        $solicitudesProblematicas = DB::table('solicitudes')
            ->whereRaw('DATE(fecha_limite) < DATE(created_at)')
            ->get();

        if ($solicitudesProblematicas->isNotEmpty()) {
            info("🔧 Corrigiendo " . $solicitudesProblematicas->count() . " solicitudes con fechas inconsistentes...");
            
            foreach ($solicitudesProblematicas as $solicitud) {
                // Obtener el proceso para acceder a fecha_ingreso
                $proceso = DB::table('procesos_ingresos')->find($solicitud->proceso_ingreso_id);
                
                if (!$proceso) continue;

                $created_at = Carbon::parse($solicitud->created_at);
                $fecha_ingreso = Carbon::parse($proceso->fecha_ingreso);
                
                // Estrategia: Usar la fecha más tardia entre:
                // 1. Fecha de ingreso - 1 día (ideal para completar antes)
                // 2. Fecha de creación + 2 días (tiempo mínimo para completar)
                
                $fecha_limite_ideal = $fecha_ingreso->copy()->subDays(1);
                $fecha_limite_minima = $created_at->copy()->addDays(2);
                
                $nueva_fecha_limite = $fecha_limite_ideal->gt($fecha_limite_minima) 
                    ? $fecha_limite_ideal 
                    : $fecha_limite_minima;

                // Actualizar solicitud
                DB::table('solicitudes')
                    ->where('id', $solicitud->id)
                    ->update([
                        'fecha_limite' => $nueva_fecha_limite->toDateString(),
                        'updated_at' => now(),
                    ]);

                info("✅ Solicitud #{$solicitud->id}: {$solicitud->fecha_limite} → {$nueva_fecha_limite->format('Y-m-d')}");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Esta migración es principalmente correctiva, no reversible
        // pero se puede registrar un log de los cambios
    }
};
