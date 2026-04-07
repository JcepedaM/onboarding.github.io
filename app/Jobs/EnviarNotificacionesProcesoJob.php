<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\ProcesoIngreso;
use App\Models\Solicitud;
use App\Models\AuditoriaOnboarding;
use App\Mail\NotificacionSolicitudMailable;
use Exception;

class EnviarNotificacionesProcesoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ProcesoIngreso $proceso;
    public int $maxTries = 3;
    public int $backoffMultiplier = 60; // segundos

    public function __construct(ProcesoIngreso $proceso)
    {
        $this->proceso = $proceso;
    }

    public function handle(): void
    {
        try {
            // Obtener todas las solicitudes del proceso
            $solicitudes = $this->proceso->solicitudes()
                ->with(['area' => function($query) {
                    $query->select('id', 'nombre');
                }])
                ->get();

            // Agrupar por área para enviar 1 email por área
            $porArea = $solicitudes->groupBy('area_id');

            foreach ($porArea as $areaId => $solicitudesDeArea) {
                $solicitud = $solicitudesDeArea->first();
                $area = $solicitud->area;

                // Obtener usuarios responsables del área (activos)
                $usuariosArea = \App\Models\User::where('area_id', $areaId)
                    ->where('activo', true)
                    ->pluck('email_notificaciones')
                    ->toArray();

                if (!empty($usuariosArea)) {
                    // Preparar datos para el email
                    $datosEnvio = [
                        'codigo' => $this->proceso->codigo,
                        'empleado' => $this->proceso->nombre_completo,
                        'cargo' => $this->proceso->cargo?->nombre ?? 'N/A',
                        'fechaIngreso' => $this->proceso->fecha_ingreso->format('d/m/Y'),
                        'solicitudes' => $solicitudesDeArea->map(function($s) {
                            return [
                                'tipo' => $s->tipo,
                                'fechaLimite' => $s->fecha_limite->format('d/m/Y'),
                                'estado' => $s->estado,
                            ];
                        })->toArray(),
                    ];

                    $urlPanel = route('solicitudes.index', ['area_id' => $areaId]);

                    $mailable = new NotificacionSolicitudMailable(
                        $this->proceso,
                        $datosEnvio['solicitudes'],
                        $area->nombre,
                        $urlPanel
                    );

                    // Enviar a múltiples destinatarios
                    foreach ($usuariosArea as $email) {
                        try {
                            Mail::to($email)->send($mailable);
                            
                            // Registrar envío exitoso en auditoría
                            AuditoriaOnboarding::registrar(
                                'notificacion_enviada',
                                'ProcesoIngreso',
                                $this->proceso->id,
                                null,
                                null,
                                [
                                    'area_id' => $areaId,
                                    'destinatario' => $email,
                                    'solicitudes' => $datosEnvio['solicitudes'],
                                    'timestamp' => now()->toIso8601String(),
                                ]
                            );
                        } catch (Exception $e) {
                            // Registrar fallo individual del email
                            AuditoriaOnboarding::registrar(
                                'notificacion_fallida',
                                'ProcesoIngreso',
                                $this->proceso->id,
                                null,
                                null,
                                [
                                    'area_id' => $areaId,
                                    'destinatario' => $email,
                                    'error' => $e->getMessage(),
                                    'timestamp' => now()->toIso8601String(),
                                ]
                            );

                            throw $e;
                        }
                    }
                } else {
                    // Registrar que no hay responsables - SIN FALLAR
                    AuditoriaOnboarding::registrar(
                        'notificacion_fallida',
                        'ProcesoIngreso',
                        $this->proceso->id,
                        null,
                        null,
                        [
                            'area_id' => $areaId,
                            'area_nombre' => $area->nombre ?? 'Desconocida',
                            'error' => 'No hay usuarios activos en el área para enviar notificación',
                            'timestamp' => now()->toIso8601String(),
                        ]
                    );
                    // NO lanzamos excepción - continuamos con las otras áreas
                }
            }
        } catch (Exception $e) {
            // Registrar error general
            AuditoriaOnboarding::registrar(
                'notificacion_fallida',
                'ProcesoIngreso',
                $this->proceso->id,
                null,
                null,
                [
                    'error' => $e->getMessage(),
                    'archivo' => $e->getFile(),
                    'línea' => $e->getLine(),
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            // Re-lanzar la excepción para que Laravel intente reintentar
            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {
        // Registrar cuando el job falla definitivamente (después de todos los reintentos)
        AuditoriaOnboarding::registrar(
            'notificacion_fallida',
            'ProcesoIngreso',
            $this->proceso->id,
            null,
            null,
            [
                'error' => $exception->getMessage(),
                'intentos' => $this->maxTries,
                'timestamp' => now()->toIso8601String(),
            ]
        );
    }
}
