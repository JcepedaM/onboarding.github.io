<?php

namespace App\Mail;

use App\Models\ProcesoIngreso;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionSolicitudMailable extends Mailable
{
    use Queueable, SerializesModels;

    public ProcesoIngreso $proceso;
    public array $solicitudes;
    public string $area;
    public string $urlPanel;

    public function __construct(ProcesoIngreso $proceso, array $solicitudes, string $area, string $urlPanel)
    {
        $this->proceso = $proceso;
        $this->solicitudes = $solicitudes;
        $this->area = $area;
        $this->urlPanel = $urlPanel;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 Nueva Solicitud de Onboarding - ' . $this->proceso->nombre_completo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.notificacion-solicitud',
            with: [
                'proceso' => $this->proceso,
                'solicitudes' => $this->solicitudes,
                'area' => $this->area,
                'urlPanel' => $this->urlPanel,
            ],
        );
    }
}
