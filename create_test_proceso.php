<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProcesoIngreso;
use App\Models\Solicitud;
use App\Models\Area;
use App\Models\Cargo;
use App\Jobs\EnviarNotificacionesProcesoJob;
use Illuminate\Support\Facades\DB;

echo "📋 Creando Proceso de Ingreso de Prueba para HU15...\n";
echo str_repeat("=", 80) . "\n\n";

try {
    // Obtener un cargo aleatorio
    $cargo = Cargo::first();
    if (!$cargo) {
        echo "❌ No hay cargos en la BD\n";
        exit(1);
    }

    // Obtener áreas
    $areas = Area::all();
    if ($areas->isEmpty()) {
        echo "❌ No hay áreas en la BD\n";
        exit(1);
    }

    // Crear proceso
    $proceso = ProcesoIngreso::create([
        'codigo' => 'TEST-' . uniqid(),
        'nombre_completo' => 'Juan de Prueba HU15',
        'tipo_documento' => 'CC',
        'documento' => (string) rand(100000000, 999999999), // Documento único aleatorio
        'cargo_id' => $cargo->id,
        'area_id' => $areas->first()->id,
        'fecha_ingreso' => now()->addDays(7),
        'email' => 'test@example.com',
        'telefono' => '3001234567',
        'jefe_id' => 4, // Root user
        'estado' => 'Pendiente',
    ]);

    echo "✅ Proceso creado:\n";
    echo "   Código: {$proceso->codigo}\n";
    echo "   Empleado: {$proceso->nombre_completo}\n";
    echo "   Área: {$proceso->area->nombre}\n\n";

    // Crear solicitudes para diferentes áreas
    echo "📝 Creando solicitudes para todas las áreas...\n";
    foreach ($areas as $area) {
        $solicitud = Solicitud::create([
            'proceso_ingreso_id' => $proceso->id,
            'area_id' => $area->id,
            'tipo' => 'Equipo',
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(5),
            'descripcion' => 'Solicitud de prueba para HU15',
        ]);
        echo "   ✓ Área: {$area->nombre}\n";
    }

    // 📧 DISPARAR JOB DE NOTIFICACIONES
    EnviarNotificacionesProcesoJob::dispatch($proceso);

    echo "\n⏳ El Job de notificaciones se ha disparado automáticamente.\n";
    echo "   Espera 5-10 segundos mientras el queue listener procesa el job...\n";
    echo "\n📧 Revisa tu Gmail (sinergianotificaciones0@gmail.com) en unos momentos.\n";
    echo "   Deberías recibir los emails de notificación.\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
?>
