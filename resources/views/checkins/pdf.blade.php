<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.6;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        
        .page {
            width: 100%;
            max-width: 21cm;
            margin: 0 auto;
            padding: 0;
            background: white;
        }
        
        .header {
            text-align: center;
            margin: 0;
            padding: 15px;
            border-bottom: 3px solid #0066cc;
        }
        
        .header h1 {
            font-size: 20px;
            color: #0066cc;
            margin: 0 0 5px 0;
        }
        
        .header p {
            font-size: 11px;
            color: #666;
            margin: 0;
        }
        
        .documento-info {
            text-align: right;
            font-size: 10px;
            margin: 15px;
            background-color: #f5f5f5;
            padding: 10px 15px;
            border-radius: 5px;
        }
        
        .documento-info p {
            margin: 3px 0;
        }
        
        .section {
            margin: 15px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #0066cc;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            margin: 0;
            font-size: 12px;
            border-radius: 3px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-col {
            display: table-cell;
            border: 1px solid #ddd;
            padding: 8px 12px;
            width: 50%;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
            color: #0066cc;
            width: 35%;
        }
        
        .info-value {
            width: 65%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }
        
        th {
            background-color: #e6f2ff;
            border: 1px solid #0066cc;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            color: #0066cc;
        }
        
        td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .centered {
            text-align: center;
        }
        
        .firma-section {
            margin: 30px 15px 15px 15px;
            padding: 20px 0;
            border-top: 2px solid #ddd;
            display: table;
            width: calc(100% - 30px);
        }
        
        .firma-item {
            display: table-cell;
            width: 50%;
            text-align: center;
            font-size: 10px;
            padding: 0 15px;
            vertical-align: bottom;
        }
        
        .linea-firma {
            border-top: 1px solid #333;
            padding-top: 8px;
            font-weight: bold;
            margin-top: 50px;
        }
        
        .estado-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .estado-completado {
            background-color: #d4edda;
            color: #155724;
        }
        
        .estado-pendiente {
            background-color: #fff3cd;
            color: #856404;
        }

        .pie-pagina {
            text-align: center;
            font-size: 9px;
            color: #999;
            margin: 15px;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h1>ACTA DE ENTREGA DE ACTIVOS</h1>
            <p>Proceso de Onboarding - Nuevo Empleado</p>
        </div>
        
        <!-- Información del Documento -->
        <div class="documento-info">
            <p><strong>Código Proceso:</strong> {{ $checkin->procesoIngreso->codigo }}</p>
            <p><strong>Código Verificación:</strong> {{ $checkin->codigo_verificacion }}</p>
            <p><strong>Fecha Generación:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
            <p><strong>Estado:</strong> 
                <span class="estado-badge {{ $checkin->estado_checkin === 'Completado' ? 'estado-completado' : 'estado-pendiente' }}">
                    {{ $checkin->estado_checkin }}
                </span>
            </p>
        </div>
        
        <!-- Sección 1: Información del Empleado -->
        <div class="section">
            <div class="section-title">INFORMACIÓN DEL EMPLEADO</div>
            <table>
                <tr>
                    <td class="info-label">Nombre Completo:</td>
                    <td>{{ $checkin->procesoIngreso->nombre_completo }}</td>
                </tr>
                <tr>
                    <td class="info-label">Cargo:</td>
                    <td>{{ $checkin->procesoIngreso->cargo->nombre }}</td>
                </tr>
                <tr>
                    <td class="info-label">Área:</td>
                    <td>{{ $checkin->procesoIngreso->area->nombre }}</td>
                </tr>
                <tr>
                    <td class="info-label">Jefe Inmediato:</td>
                    <td>{{ $checkin->procesoIngreso->jefeCargo?->nombre ?? $checkin->procesoIngreso->cargo?->jefeInmediato?->nombre ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Fecha de Ingreso:</td>
                    <td>{{ $checkin->procesoIngreso->fecha_ingreso }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Sección 2: Listado de Activos -->
        <div class="section">
            <div class="section-title">LISTADO DE ACTIVOS ENTREGADOS</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Ítem</th>
                        <th style="width: 35%;">Especificaciones</th>
                        <th style="width: 15%;" class="centered">Estado</th>
                        <th style="width: 10%;" class="centered">✓</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($checkin->activos_entregados)
                        @foreach ($checkin->activos_entregados as $activo)
                            <tr>
                                <td>{{ $activo['item'] ?? '-' }}</td>
                                <td>{{ $activo['especificaciones'] ?? '-' }}</td>
                                <td class="centered">
                                    <span class="estado-badge 
                                        {{ ($activo['entregado'] ?? false) ? 'estado-completado' : 'estado-pendiente' }}">
                                        {{ ($activo['entregado'] ?? false) ? 'Entregado' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="centered">
                                    {{ ($activo['entregado'] ?? false) ? '✓' : '✗' }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="centered">Sin activos registrados</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Sección 3: Información de Confirmación -->
        @if ($checkin->estado_checkin === 'Completado' && $checkin->fecha_confirmacion)
            <div class="section">
                <div class="section-title">CONFIRMACIÓN DE ENTREGA</div>
                <table>
                    <tr>
                        <td class="info-label">Fecha de Confirmación:</td>
                        <td>{{ $checkin->fecha_confirmacion->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Dispositivo:</td>
                        <td>{{ $checkin->dispositivo_confirmacion ?? 'No registrado' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Dirección IP:</td>
                        <td>{{ $checkin->ip_confirmacion ?? 'No registrada' }}</td>
                    </tr>
                </table>
            </div>
        @endif
        
        <!-- Sección 4: Firmas -->
        <div class="firma-section">
            <div class="firma-item">
                <div style="height: 80px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #999;">
                    @if ($checkin->firma_digital)
                        [Firma Digital Registrada]
                    @else
                        [Espacio para firma del empleado]
                    @endif
                </div>
                <div class="linea-firma">Firma Empleado</div>
                <p>{{ $checkin->procesoIngreso->nombre_completo }}</p>
            </div>
            <div class="firma-item">
                <div style="height: 80px;">
                </div>
                <div class="linea-firma">Firma Jefe Inmediato</div>
                <p>{{ $checkin->procesoIngreso->jefeCargo?->nombre ?? $checkin->procesoIngreso->cargo?->jefeInmediato?->nombre ?? '—' }}</p>
            </div>
        </div>
        
        <!-- Pie de Página -->
        <div class="pie-pagina">
            <p>Este documento es una constancia oficial de entrega de activos. Ambas partes reconocen haber recibido/entregado los items listados anteriormente.</p>
            <p>Generado automáticamente por el Sistema de Onboarding | {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
