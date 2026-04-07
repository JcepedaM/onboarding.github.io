<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Onboarding</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 10px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
        }
        .employee-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: 600;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .solicitudes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .solicitudes-table th {
            background-color: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        .solicitudes-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }
        .solicitudes-table tr:hover {
            background-color: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .cta-button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
            transition: background-color 0.3s;
        }
        .cta-button:hover {
            background-color: #764ba2;
        }
        .important-note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 3px;
            margin: 20px 0;
        }
        .important-note strong {
            color: #856404;
        }
        .footer {
            background-color: #f9f9f9;
            border-top: 1px solid #ddd;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .area-badge {
            background-color: #e8f4f8;
            padding: 8px 12px;
            border-radius: 4px;
            color: #0c5460;
            font-weight: 600;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Nueva Solicitud de Onboarding</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Procesos de Ingreso - Sinergia</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Saludo -->
            <p style="font-size: 16px; margin-bottom: 20px;">
                Buenas,(s)<br><br>
                Se ha creado un nuevo proceso de onboarding que requiere tu atención.
            </p>

            <!-- Área Responsable -->
            <div class="section">
                <div class="area-badge">
                    👥 Área Responsable: {{ $area }}
                </div>
            </div>

            <!-- Información del Empleado -->
            <div class="section">
                <div class="section-title">Información del Empleado</div>
                <div class="employee-info">
                    <div class="info-row">
                        <span class="info-label">Nombre:</span>
                        <span class="info-value">{{ $proceso->nombre_completo }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cédula:</span>
                        <span class="info-value">{{ $proceso->documento }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cargo:</span>
                        <span class="info-value">{{ $proceso->cargo?->nombre ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha de Ingreso:</span>
                        <span class="info-value">{{ $proceso->fecha_ingreso->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Código del Proceso:</span>
                        <span class="info-value">{{ $proceso->codigo }}</span>
                    </div>
                </div>
            </div>

            <!-- Solicitudes -->
            <div class="section">
                <div class="section-title">Solicitudes Pendientes</div>
                <table class="solicitudes-table">
                    <thead>
                        <tr>
                            <th>Tipo de Solicitud</th>
                            <th>Fecha Límite</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($solicitudes as $solicitud)
                        <tr>
                            <td>{{ $solicitud['tipo'] }}</td>
                            <td>{{ $solicitud['fechaLimite'] }}</td>
                            <td>
                                @if ($solicitud['estado'] === 'pendiente')
                                    <span class="badge badge-pending">Pendiente</span>
                                @elseif ($solicitud['estado'] === 'completada')
                                    <span class="badge badge-completed">Completada</span>
                                @else
                                    <span class="badge badge-rejected">{{ ucfirst($solicitud['estado']) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Nota Importante -->
            <div class="important-note">
                <strong>⏰ Nota Importante:</strong><br>
                Por favor, completa las solicitudes dentro de las fechas límite especificadas. El cumplimiento oportuno es importante para garantizar una experiencia de onboarding óptima.
            </div>

            <!-- Botón de Acción -->
            <div style="text-align: center;">
                <p style="color: #666; margin-bottom: 10px;">
                    Accede al panel para gestionar las solicitudes:
                </p>
                <a href="{{ $urlPanel }}" class="cta-button">
                    Ir al Panel de Solicitudes
                </a>
            </div>

            <!-- Información de Contacto -->
            <div class="section" style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 20px;">
                <p style="font-size: 12px; color: #666; margin: 0;">
                    Si tienes preguntas o necesitas ayuda, contacta al equipo de Recursos Humanos.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 10px 0;">
                © 2026 Sinergia Financiera - Sistema de Onboarding
            </p>
            <p style="margin: 0; color: #999;">
                Este es un correo automático. Por favor, no respondas a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
