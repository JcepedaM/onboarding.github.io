<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "📧 Enviando email de prueba...\n";

try {
    Mail::raw('Correo de prueba del sistema de onboarding', function($message) {
        $message->to('sinergianotificaciones0@gmail.com')
                ->subject('Test Email - Sistema Onboarding');
    });
    
    echo "✅ Email enviado exitosamente!\n";
} catch (\Exception $e) {
    echo "❌ Error al enviar email:\n";
    echo "   " . $e->getMessage() . "\n";
}
?>
