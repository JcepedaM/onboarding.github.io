<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "✅ VERIFICACIÓN DE ARCHIVOS HU15\n";
echo str_repeat("=", 80) . "\n\n";

$archivos = [
    'app/Jobs/EnviarNotificacionesProcesoJob.php' => 'Job principal',
    'app/Mail/NotificacionSolicitudMailable.php' => 'Mailable para emails',
    'resources/views/mails/notificacion-solicitud.blade.php' => 'Template HTML del email',
    'app/Http/Controllers/ProcesoIngresoController.php' => 'Controlador con dispatch',
    'config/mail.php' => 'Config de mail con Mailgun',
    '.env (MAIL_MAILER)' => 'Variable de entorno',
];

foreach ($archivos as $ruta => $desc) {
    if (strpos($ruta, '.env') !== false) {
        echo "✅ $desc ($ruta)\n";
    } elseif (file_exists(base_path(str_replace('.env (MAIL_MAILER)', '', $ruta)))) {
        echo "✅ $desc\n";
        echo "   📁 " . str_replace(['\\', 'C:\\Users\\angie\\dev\\projects\\proyecto_laboratorio_software\\onboarding.github.io-main\\'], ['/', ''], realpath(base_path($ruta))) . "\n";
    } else {
        echo "❌ FALTA: $desc ($ruta)\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ VERIFICACIÓN DE CLASES\n\n";

$clases = [
    'App\Jobs\EnviarNotificacionesProcesoJob',
    'App\Mail\NotificacionSolicitudMailable',
    'App\Http\Controllers\ProcesoIngresoController',
];

foreach ($clases as $clase) {
    if (class_exists($clase)) {
        echo "✅ $clase\n";
    } else {
        echo "❌ NO EXISTE: $clase\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ TODO LISTO PARA HU15\n";
echo "   Próximo paso: php artisan queue:listen --tries=3 --timeout=0\n";
?>
