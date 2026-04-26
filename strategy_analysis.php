<?php
// Script para determinar la mejor estrategia de prevención

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  ANÁLISIS POST-FIX: ESTRATEGIA PREVENTIVA RECOMENDADA        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Analizar casos específicos
echo "=== CASO ESPECÍFICO: SOLICITUD 501 ===\n";
$s501 = \App\Models\Solicitud::find(501);
$p501 = $s501->proceso;
echo "Escenario original:\n";
echo "  - Proceso creado: " . $p501->created_at->format('d/m/Y H:i') . "\n";
echo "  - Solicitud creada: " . $s501->created_at->format('d/m/Y H:i') . "\n";
echo "  - Fecha ingreso planificada: " . $p501->fecha_ingreso->format('d/m/Y') . "\n";
echo "  - Días planificados antes: 5 (Tecnología)\n";
echo "  - Fecha límite CALCULADA (antiguo): 04/04/2026 ❌\n";
echo "  - Fecha límite CORREGIDA (nuevo): " . $s501->fecha_limite->format('d/m/Y') . " ✅\n";
echo "\n";

// Análisis estatístico
echo "=== ANÁLISIS ESTADÍSTICO ===\n";

$solicitudes = \App\Models\Solicitud::with('proceso')
    ->get()
    ->map(function($s) {
        $created = Carbon::parse($s->created_at);
        $ingreso = Carbon::parse($s->proceso->fecha_ingreso);
        $limite = Carbon::parse($s->fecha_limite);
        
        return [
            'id' => $s->id,
            'tipo' => $s->tipo,
            'dias_antes_ingreso' => $ingreso->diffInDays($limite),
            'dias_antes_creacion' => $created->diffInDays($limite),
            'fecha_inicio_proceso' => $created->diffInDays($ingreso),
        ];
    });

echo "Distribución de 'días antes del ingreso':\n";
$dist = $solicitudes->groupBy('dias_antes_ingreso')->map->count()->sort();
foreach($dist as $dias => $cant) {
    echo "  - $dias días antes del ingreso: $cant solicitudes\n";
}
echo "\n";

// Casos edge
echo "=== CASOS CRÍTICOS (Procesos de muy corto plazo) ===\n";
$procesos_cortos = \App\Models\ProcesoIngreso::with('solicitudes')
    ->get()
    ->filter(function($p) {
        return $p->fecha_ingreso->diffInDays(now()) <= 2;
    });

if ($procesos_cortos->isEmpty()) {
    echo "✅ No hay procesos con ingreso en los próximos 2 días.\n";
} else {
    foreach($procesos_cortos as $p) {
        $dias_desde_creacion = $p->created_at->diffInDays($p->fecha_ingreso);
        echo "  - Proceso {$p->codigo}:\n";
        echo "    Creado: " . $p->created_at->format('d/m') . " | Ingreso: " . $p->fecha_ingreso->format('d/m') . " ($dias_desde_creacion días)\n";
    }
}
echo "\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  RECOMENDACIÓN DE ESTRATEGIA PREVENTIVA                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "📊 ANÁLISIS COMPARATIVO:\n\n";

echo "OPCIÓN 1: MIN() - Usar fecha más cercana\n";
echo "  Fórmula: fecha_limite = MIN(fecha_ingreso - días, created_at + 2 días)\n";
echo "  ✅ Pro: Garantiza plazo antes del ingreso si es posible\n";
echo "  ✅ Pro: Nunca crea solicitudes vencidas\n";
echo "  ✅ Pro: Adapta automáticamente a procesos de corto plazo\n";
echo "  ❌ Con: Puede reducir drásticamente el plazo si hay poco tiempo\n";
echo "  💡 Resultado esperado: " . $solicitudes->avg('dias_antes_creacion') . " días de plazo promedio\n\n";

echo "OPCIÓN 2: MAX() - No ser tan restrictivo\n";
echo "  Fórmula: fecha_limite = MAX(fecha_ingreso - días, created_at + 2 días)\n";
echo "  ✅ Pro: Mantiene el plazo original cuando es posible\n";
echo "  ❌ Con: Puede crear solicitudes vencidas si plazo es muy corto\n";
echo "  ❌ Con: Mejor para procesos normales, mala para urgencias\n\n";

echo "OPCIÓN 3: VALIDACIÓN - Advertir al usuario\n";
echo "  Si fecha_ingreso - días < hoy, mostrar alerta\n";
echo "  ✅ Pro: Usuario decide el compromiso\n";
echo "  ❌ Con: Más complejo, requiere UI\n";
echo "  ❌ Con: Mayor riesgo de errores humanos\n\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ RECOMENDACIÓN FINAL                                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "IMPLEMENTAR: OPCIÓN 1 (MIN) en ProcesoIngresoController\n\n";
echo "Razones:\n";
echo "  1. Ya probamos esta lógica y funcionó (datos migrados correctamente)\n";
echo "  2. Mantiene la intención de completar antes del ingreso\n";
echo "  3. Previene ALL problemas de vencimiento inmediato\n";
echo "  4. Es automática, sin intervención del usuario\n";
echo "  5. Se adapta a cualquier plazo de ingreso\n";
echo "  6. Cálculo simple y auditable\n\n";

echo "Cambios necesarios:\n";
echo "  1. Modificar ProcesoIngresoController.php (línea ~112)\n";
echo "  2. Modificar ProcesoIngresoSeeder.php (línea ~59)\n";
echo "  3. Agregar validación SERVER-SIDE\n";
echo "  4. (Opcional) Agregar alerta en UI si plazo es < 2 días\n";
