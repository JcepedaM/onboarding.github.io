<?php
// Script para analizar el resultado del fix

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Analizar la solicitud que mencionaste (501)
$solicitud = \App\Models\Solicitud::find(501);
echo "=== SOLICITUD 501 (DESPUÉS DE FIX) ===\n";
echo "ID: " . $solicitud->id . "\n";
echo "Creada: " . $solicitud->created_at->format('d/m/Y H:i') . "\n";
echo "Fecha Límite (NUEVA): " . $solicitud->fecha_limite->format('d/m/Y') . "\n";
echo "Fecha Ingreso Proceso: " . $solicitud->proceso->fecha_ingreso->format('d/m/Y') . "\n";
echo "\n";

// Contar cuántas solicitudes fueron corregidas
$arregladas = \App\Models\Solicitud::whereRaw('DATE(fecha_limite) >= DATE(created_at)')->count();
$problematicas = \App\Models\Solicitud::whereRaw('DATE(fecha_limite) < DATE(created_at)')->count();

echo "=== ESTADO GENERAL ===\n";
echo "✅ Solicitudes CORRECTAS: " . $arregladas . "\n";
echo "❌ Solicitudes AÚN PROBLEMÁTICAS: " . $problematicas . "\n";
echo "\n";

if ($problematicas > 0) {
    echo "Ejemplos de problemas remanentes:\n";
    \App\Models\Solicitud::whereRaw('DATE(fecha_limite) < DATE(created_at)')
        ->select('id', 'created_at', 'fecha_limite')
        ->limit(5)
        ->get()
        ->each(function($s) {
            echo "  - ID {$s->id}: Creada {$s->created_at->format('d/m')} | Límite {$s->fecha_limite->format('d/m')}\n";
        });
} else {
    echo "✨ ¡Todas las solicitudes han sido corregidas!\n";
}
echo "\n";

// Estadísticas de distribución
echo "=== ANÁLISIS POR RANGO DE FECHAS ===\n";
$stats = \App\Models\Solicitud::selectRaw('
    COUNT(*) as total,
    COUNT(CASE WHEN fecha_limite = DATE(created_at) THEN 1 END) as misma_fecha,
    COUNT(CASE WHEN fecha_limite > DATE(created_at) AND fecha_limite <= DATE_ADD(DATE(created_at), INTERVAL 5 DAY) THEN 1 END) as hasta_5_dias,
    COUNT(CASE WHEN fecha_limite > DATE_ADD(DATE(created_at), INTERVAL 5 DAY) THEN 1 END) as mas_de_5_dias
')->first();

echo "Total solicitudes: " . $stats->total . "\n";
echo "  - Límite = Día de creación: " . $stats->misma_fecha . "\n";
echo "  - Límite 1-5 días después: " . $stats->hasta_5_dias . "\n";
echo "  - Límite > 5 días después: " . $stats->mas_de_5_dias . "\n";
