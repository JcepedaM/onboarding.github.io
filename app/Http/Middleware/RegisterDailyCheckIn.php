<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CheckInAcceso;
use App\Models\AuditoriaOnboarding;

class RegisterDailyCheckIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo para usuarios autenticados
        if (auth()->check()) {
            $usuario = auth()->user();
            
            // Verificar si ya existe un acceso para hoy
            $accesoHoy = CheckInAcceso::where('usuario_id', $usuario->id)
                ->where('area_id', $usuario->area_id)
                ->whereDate('fecha_acceso', today())
                ->first();
            
            // Si no existe, crear uno nuevo
            if (!$accesoHoy) {
                try {
                    $checkIn = CheckInAcceso::create([
                        'usuario_id' => $usuario->id,
                        'area_id' => $usuario->area_id,
                        'fecha_acceso' => today(),
                        'hora_acceso' => now()->toTimeString(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'dispositivo_tipo' => $this->detectarDispositivo($request),
                        'navegador' => $this->detectarNavegador($request),
                    ]);
                    
                    // Registrar en auditoría
                    AuditoriaOnboarding::registrar(
                        accion: 'login-checkin',
                        entidad: 'CheckInAcceso',
                        entidadId: $checkIn->id,
                        motivo: "Primer acceso del día registrado automáticamente - Área: " . ($usuario->area?->nombre ?? 'Sin Área'),
                        valoresNuevos: $checkIn->toArray()
                    );
                } catch (\Exception $e) {
                    // Si hay error, continuar sin fallar (no interrumpir flujo de login)
                    \Log::warning("Error registrando CheckInAcceso: " . $e->getMessage());
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * Detectar el tipo de dispositivo del usuario
     */
    private function detectarDispositivo(Request $request): string
    {
        $userAgent = $request->header('User-Agent') ?? '';
        
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent)) {
            if (preg_match('/Tablet|iPad/', $userAgent)) {
                return 'Tablet';
            }
            return 'Móvil';
        }
        
        return 'Escritorio';
    }
    
    /**
     * Detectar el navegador del usuario
     */
    private function detectarNavegador(Request $request): string
    {
        $userAgent = $request->header('User-Agent') ?? '';
        
        if (preg_match('/Chrome/', $userAgent) && !preg_match('/Chromium|Edg/', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Safari/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Edg/', $userAgent)) {
            return 'Edge';
        }
        
        return 'Otro';
    }
}
