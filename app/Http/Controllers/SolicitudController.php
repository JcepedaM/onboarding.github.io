<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\DetalleTecnologia;
use App\Models\DetalleUniforme;
use App\Models\Checkin;
use App\Models\ProcesoIngreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SolicitudController extends Controller
{
    /**
     * Listar solicitudes según el rol del usuario
     */
    public function index()
    {
        $user = auth()->user();
        $baseQuery = Solicitud::whereHas('proceso', function ($query) {
            $query->where('estado', '!=', 'Cancelado');
        });
        
        // Root ve todas las solicitudes
        if ($user->hasRole('Root')) {
            $solicitudes = $baseQuery->with(['proceso', 'area', 'detalleTecnologia', 'detalleUniforme', 'detalleBienes', 'puestoTrabajo', 'cursos'])
                ->latest()
                ->paginate(15);
        }
        // Jefe RRHH ve todas las solicitudes (gestiona todo el onboarding)
        elseif ($user->hasRole('Jefe RRHH')) {
            $solicitudes = $baseQuery->with(['proceso', 'area', 'detalleTecnologia', 'detalleUniforme', 'detalleBienes', 'puestoTrabajo', 'cursos'])
                ->latest()
                ->paginate(15);
        }
        // Jefe Tecnología ve SOLO solicitudes de Tecnología
        elseif ($user->hasRole('Jefe Tecnología')) {
            $solicitudes = $baseQuery->where('tipo', 'Tecnología')
                ->with(['proceso', 'area', 'detalleTecnologia'])
                ->latest()
                ->paginate(15);
        }
        // Jefe Dotación ve SOLO solicitudes de Dotación
        elseif ($user->hasRole('Jefe Dotación')) {
            $solicitudes = $baseQuery->where('tipo', 'Dotación')
                ->with(['proceso', 'area', 'detalleUniforme'])
                ->latest()
                ->paginate(15);
        }
        // Jefe Servicios Generales ve SOLO solicitudes de Servicios Generales
        elseif ($user->hasRole('Jefe Servicios Generales')) {
            $solicitudes = $baseQuery->where('tipo', 'Servicios Generales')
                ->with(['proceso', 'area', 'puestoTrabajo'])
                ->latest()
                ->paginate(15);
        }
        // Jefe Bienes y Servicios ve SOLO solicitudes de Bienes y Servicios
        elseif ($user->hasRole('Jefe Bienes y Servicios')) {
            $solicitudes = $baseQuery->where('tipo', 'Bienes y Servicios')
                ->with(['proceso', 'area', 'detalleBienes'])
                ->latest()
                ->paginate(15);
        }
        // Otros usuarios ven sus propias solicitudes
        else {
            $solicitudes = $baseQuery->where('proceso_ingreso_id', auth()->id())
                ->with(['proceso', 'area'])
                ->latest()
                ->paginate(15);
        }

        return view('solicitudes.index', compact('solicitudes'));
    }

    /**
     * Ver detalles de la solicitud - Redirige a vista específica por tipo
     */
    public function show(Solicitud $solicitude)
    {
        $this->verificarPermiso($solicitude);
        
        $solicitude->load([
            'proceso' => function($q) {
                $q->with(['cargo', 'area', 'jefe']);
            },
            'area',
            'detalleTecnologia',
            'detalleUniforme',
            'detalleBienes',
            'puestoTrabajo',
            'cursos'
        ]);

        // Redirigir a vista específica según el tipo
        $tipoView = match($solicitude->tipo) {
            'Tecnología' => 'tipo-tecnologia',
            'Dotación' => 'tipo-dotacion',
            'Servicios Generales' => 'tipo-servicios-generales',
            'Formación' => 'tipo-formacion',
            'Bienes' => 'tipo-bienes',
            'Bienes y Servicios' => 'tipo-bienes',
            default => 'show',
        };

        return view("solicitudes.{$tipoView}", compact('solicitude'));
    }

    /**
     * Guardar especificaciones de Tecnología
     */
    public function guardarTecnologia(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        $request->validate([
            'necesita_computador' => 'required|boolean',
            'gama_computador' => 'nullable|required_if:necesita_computador,1|in:Básica,Media,Premium',
            'credenciales_plataformas' => 'required|string|max:2000',
        ]);

        $detalleTecnologia = DetalleTecnologia::firstOrCreate(
            ['solicitud_id' => $solicitud->id],
            ['proceso_ingreso_id' => $solicitud->proceso_ingreso_id]
        );

        if (! $detalleTecnologia->proceso_ingreso_id) {
            $detalleTecnologia->proceso_ingreso_id = $solicitud->proceso_ingreso_id;
        }

        $detalleTecnologia->update([
            'proceso_ingreso_id' => $solicitud->proceso_ingreso_id,
            'necesita_computador' => (bool) $request->necesita_computador,
            'gama_computador' => $request->gama_computador,
            'credenciales_plataformas' => $request->credenciales_plataformas,
        ]);

        return redirect()->route('solicitudes.show', $solicitud->id)
                       ->with('success', 'Especificaciones de tecnología guardadas correctamente');
    }

    /**
     * Guardar especificaciones de Dotación
     */
    public function guardarDotacion(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        $request->validate([
            'necesita_dotacion' => 'required|boolean',
            'genero' => 'nullable|required_if:necesita_dotacion,1|in:Masculino,Femenino,Otro',
            'talla_pantalon' => 'nullable|required_if:necesita_dotacion,1|string|max:50',
            'talla_camiseta' => 'nullable|required_if:necesita_dotacion,1|string|max:50',
            'justificacion_no_dotacion' => 'nullable|required_if:necesita_dotacion,0|string|max:1000',
        ]);

        $detalleUniforme = DetalleUniforme::firstOrCreate(
            ['solicitud_id' => $solicitud->id],
            ['proceso_ingreso_id' => $solicitud->proceso_ingreso_id]
        );

        if (! $detalleUniforme->proceso_ingreso_id) {
            $detalleUniforme->proceso_ingreso_id = $solicitud->proceso_ingreso_id;
        }

        $detalleUniforme->update([
            'proceso_ingreso_id' => $solicitud->proceso_ingreso_id,
            'necesita_dotacion' => (bool) $request->necesita_dotacion,
            'genero' => $request->genero,
            'talla_pantalon' => $request->talla_pantalon,
            'talla_camiseta' => $request->talla_camiseta,
            'justificacion_no_dotacion' => $request->justificacion_no_dotacion,
        ]);

        return redirect()->route('solicitudes.show', $solicitud->id)
                       ->with('success', 'Especificaciones de dotación guardadas correctamente');
    }

    /**
     * Guardar asignación de puesto (Servicios Generales)
     */
    public function guardarServiciosGenerales(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        $request->validate([
            'puesto_trabajo_id' => 'required|exists:puestos_trabajo,id',
        ]);

        $puestoAnterior = $solicitud->puestoTrabajo;
        $puesto = \App\Models\PuestoTrabajo::find($request->puesto_trabajo_id);

        // Verificar que el puesto esté disponible
        if ($puesto && $puesto->estado !== 'Disponible' && $puesto->estado !== 'Asignado') {
            return redirect()->route('solicitudes.show', $solicitud->id)
                           ->with('error', "El puesto no está disponible. Estado actual: {$puesto->estado}");
        }

        // Liberar puesto anterior si existe
        if ($puestoAnterior && $puestoAnterior->estado === 'Asignado') {
            $puestoAnterior->estado = 'Disponible';
            $puestoAnterior->save();
        }

        // Asignar nuevo puesto
        $solicitud->puesto_trabajo_id = $request->puesto_trabajo_id;
        $solicitud->save();

        // Marcar el puesto como asignado
        if ($puesto) {
            $puesto->estado = 'Asignado';
            $puesto->save();
        }

        // Registrar en auditoría
        \App\Models\AuditoriaOnboarding::create([
            'usuario_id' => auth()->id(),
            'proceso_ingreso_id' => $solicitud->proceso_ingreso_id,
            'accion' => 'Asignación de Puesto de Trabajo',
            'descripcion' => $puestoAnterior 
                ? "Cambio de puesto de {$puestoAnterior->numero_puesto} a {$puesto->numero_puesto}"
                : "Asignación de nuevo puesto {$puesto->numero_puesto}",
            'tabla_afectada' => 'solicitudes',
            'registro_id' => $solicitud->id,
            'datos_anteriores' => [
                'puesto_trabajo_id' => $puestoAnterior?->id,
                'puesto_numero' => $puestoAnterior?->numero_puesto,
            ],
            'datos_nuevos' => [
                'puesto_trabajo_id' => $puesto->id,
                'puesto_numero' => $puesto->numero_puesto,
                'piso' => $puesto->piso,
                'seccion' => $puesto->seccion,
                'fecha_asignacion' => now(),
            ],
            'ip_usuario' => $request->ip(),
        ]);

        return redirect()->route('solicitudes.show', $solicitud->id)
                       ->with('success', 'Puesto de trabajo asignado correctamente');
    }

    /**
     * Guardar cursos de Formación
     */
    public function guardarFormacion(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        $request->validate([
            'curso_ids' => 'nullable|array',
            'curso_ids.*' => 'exists:cursos,id',
        ]);

        // Limpiar cursos anteriores
        $solicitud->cursos()->detach();

        // Asignar nuevos cursos
        if ($request->has('curso_ids')) {
            $solicitud->cursos()->attach($request->curso_ids);
        }

        return redirect()->route('solicitudes.show', $solicitud->id)
                       ->with('success', 'Plan de formación guardado correctamente');
    }

    /**
     * Guardar bienes y servicios
     */
    public function guardarBienes(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        $request->validate([
            'bienes' => 'nullable|array',
            'observaciones_bienes' => 'nullable|string|max:1000',
        ]);

        $detalleBienes = \App\Models\DetalleBienes::firstOrCreate(
            ['solicitud_id' => $solicitud->id],
            []
        );

        $detalleBienes->update([
            'bienes_requeridos' => $request->bienes ?? [],
            'observaciones' => $request->observaciones_bienes,
        ]);

        return redirect()->route('solicitudes.show', $solicitud->id)
                       ->with('success', 'Bienes y servicios guardados correctamente');
    }

    /**
     * Mostrar formulario para especificar detalles de TI
     */
    public function especificarTI($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        // Verificar que sea una solicitud de Tecnología
        if ($solicitud->tipo !== 'Tecnología') {
            return back()->withErrors(['error' => 'Esta solicitud no es de Tecnología']);
        }

        // Verificar que exista proceso y que el usuario sea jefe del proceso o del area
        if (! $solicitud->proceso) {
            return back()->withErrors(['error' => 'No tiene permiso para editar esta solicitud']);
        }

        $proceso = $solicitud->proceso;
        $esJefeAsignado = $proceso->jefe_id && $proceso->jefe_id === auth()->id();
        $esRolJefe = auth()->user()->getRoleNames()->contains(fn($role) => strpos($role, 'Jefe') !== false);
        $esJefeArea = $esRolJefe && auth()->user()->area_id && $proceso->area_id === auth()->user()->area_id;

        if (! $esJefeAsignado && ! $esJefeArea && ! auth()->user()->hasRole('Root')) {
            return back()->withErrors(['error' => 'No tiene permiso para editar esta solicitud']);
        }

        $detalle = $solicitud->detalleTecnologia ?? new DetalleTecnologia();
        
        // Obtener kit estándar sugerido basado en el cargo
        $kitEstandar = DetalleTecnologia::obtenerKitEstandar($solicitud->proceso->cargo_id);
        $estadisticas = DetalleTecnologia::obtenerEstadisticasCargo($solicitud->proceso->cargo_id);

        return view('solicitudes.especificar_ti', compact('solicitud', 'detalle', 'kitEstandar', 'estadisticas'));
    }

    /**
     * Guardar detalles de TI
     */
    public function guardarTI(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        $request->validate([
            'tipo_computador' => 'required|in:Portátil,Escritorio',
            'marca_computador' => 'required|string',
            'especificaciones' => 'required|string',
            'software_requerido' => 'required|string',
            'monitor_adicional' => 'boolean',
            'mouse_teclado' => 'boolean',
        ]);

        DetalleTecnologia::updateOrCreate(
            ['solicitud_id' => $solicitud->id],
            [
                'proceso_ingreso_id' => $solicitud->proceso_ingreso_id,
                'tipo_computador' => $request->tipo_computador,
                'marca_computador' => $request->marca_computador,
                'especificaciones' => $request->especificaciones,
                'software_requerido' => $request->software_requerido,
                'monitor_adicional' => $request->has('monitor_adicional'),
                'mouse_teclado' => $request->has('mouse_teclado'),
            ]
        );

        return redirect()->route('solicitudes.show', $solicitud->id)
            ->with('success', 'Detalles de TI guardados correctamente');
    }

    /**
     * Mostrar formulario para especificar tallas de uniformes
     */
    public function especificarTallas($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        // Verificar que sea una solicitud de Dotación
        if ($solicitud->tipo !== 'Dotación') {
            return back()->withErrors(['error' => 'Esta solicitud no es de Dotación']);
        }

        // Verificar que exista proceso y que el usuario sea jefe del proceso o del area
        if (! $solicitud->proceso) {
            return back()->withErrors(['error' => 'No tiene permiso para editar esta solicitud']);
        }

        $proceso = $solicitud->proceso;
        $esJefeAsignado = $proceso->jefe_id && $proceso->jefe_id === auth()->id();
        $esRolJefe = auth()->user()->getRoleNames()->contains(fn($role) => strpos($role, 'Jefe') !== false);
        $esJefeArea = $esRolJefe && auth()->user()->area_id && $proceso->area_id === auth()->user()->area_id;

        if (! $esJefeAsignado && ! $esJefeArea && ! auth()->user()->hasRole('Root')) {
            return back()->withErrors(['error' => 'No tiene permiso para editar esta solicitud']);
        }

        $detalle = $solicitud->detalleUniforme ?? new DetalleUniforme();
        
        // Obtener kit estándar y estadísticas
        $kitEstandar = DetalleUniforme::obtenerKitEstandar($solicitud->proceso->cargo_id);
        $estadisticas = DetalleUniforme::obtenerEstadisticasCargo($solicitud->proceso->cargo_id);

        return view('solicitudes.especificar_tallas', compact('solicitud', 'detalle', 'kitEstandar', 'estadisticas'));
    }

    /**
     * Guardar tallas de uniformes
     */
    public function guardarTallas(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        $request->validate([
            'talla_camisa' => 'required|string',
            'talla_pantalon' => 'required|string',
            'talla_zapatos' => 'required|string',
            'genero' => 'required|in:Masculino,Femenino,Otro',
            'cantidad_uniformes' => 'required|integer|min:1',
            'observaciones' => 'nullable|string',
        ]);

        DetalleUniforme::updateOrCreate(
            ['solicitud_id' => $solicitud->id],
            [
                'proceso_ingreso_id' => $solicitud->proceso_ingreso_id,
                'talla_camisa' => $request->talla_camisa,
                'talla_pantalon' => $request->talla_pantalon,
                'talla_zapatos' => $request->talla_zapatos,
                'genero' => $request->genero,
                'cantidad_uniformes' => $request->cantidad_uniformes,
                'observaciones' => $request->observaciones,
            ]
        );

        return redirect()->route('solicitudes.show', $solicitud->id)
            ->with('success', 'Tallas guardadas correctamente');
    }

    /**
     * Cambiar estado de la solicitud (Operadores)
     */
    public function cambiarEstado(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);
        $user = auth()->user();

        // Root y Jefe RRHH pueden cambiar estado de cualquier solicitud
        $puedeEditar = $user->hasRole(['Root', 'Jefe RRHH']);

        // Jefes específicos pueden cambiar estado de sus tipos
        if (!$puedeEditar) {
            $tipoRolMap = [
                'Tecnología' => 'Jefe Tecnología',
                'Dotación' => 'Jefe Dotación',
                'Servicios Generales' => 'Jefe Servicios Generales',
                'Formación' => 'Jefe RRHH', // Formación la maneja Jefe RRHH
                'Bienes' => 'Jefe Bienes y Servicios',
                'Bienes y Servicios' => 'Jefe Bienes y Servicios',
            ];

            $rolRequerido = $tipoRolMap[$solicitud->tipo] ?? null;
            if ($rolRequerido && $user->hasRole($rolRequerido)) {
                $puedeEditar = true;
            }
        }

        if (!$puedeEditar) {
            return back()->withErrors(['error' => 'No tienes permiso para cambiar el estado de esta solicitud']);
        }

        $request->validate([
            'estado' => 'required|in:Pendiente,En Proceso,Finalizada',
            'observaciones' => 'nullable|string',
        ]);

        $estadoAnterior = $solicitud->estado;
        $estadoNuevo = $request->estado;

        $updateData = [
            'estado' => $estadoNuevo,
            'observaciones' => $request->observaciones,
        ];

        // Guardar fecha de finalización cuando se marca como Finalizada
        if ($estadoNuevo === 'Finalizada') {
            $updateData['fecha_finalizacion'] = now();
        }

        $solicitud->update($updateData);

        // Log del cambio de estado
        \Illuminate\Support\Facades\Log::info("cambio_estado_solicitud", [
            'solicitud_id' => $solicitud->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'usuario' => auth()->user()->email,
            'timestamp' => now(),
        ]);

        // Enviar notificación si está disponible
        try {
            $this->enviarNotificacionEstado($solicitud, $estadoAnterior, $estadoNuevo);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("notificacion_state_change_failed: {$e->getMessage()}");
        }

        // Si todas las solicitudes están en 'Finalizada', marcar el proceso como completado
        $proceso = $solicitud->proceso;
        if ($proceso && $estadoNuevo === 'Finalizada') {
            // Verificar si TODAS las solicitudes están en Finalizada
            $todasFinalizadas = $proceso->solicitudes()->where('estado', '!=', 'Finalizada')->doesntExist();
            if ($todasFinalizadas) {
                $proceso->update([
                    'estado' => 'Finalizado',
                    'fecha_finalizacion' => now(),
                ]);

                $checkin = $this->generarCheckinAutomatico($proceso);
                if ($checkin) {
                    session()->flash('success', 'Proceso finalizado y check-in generado automaticamente.');
                } else {
                    session()->flash('success', 'Proceso finalizado. El check-in ya estaba generado.');
                }
            }
        }

        return back()->with('success', "Solicitud actualizada a '{$estadoNuevo}' exitosamente");
    }

    /**
     * Finalizar TODAS las solicitudes de un proceso (solo Root y Jefe RRHH)
     */
    public function finalizarTodas(Request $request)
    {
        $user = auth()->user();
        
        // Solo Root y Jefe RRHH pueden usar esta función
        if (!$user->hasRole(['Root', 'Jefe RRHH'])) {
            return back()->withErrors(['error' => 'No tienes permiso para finalizar todas las solicitudes']);
        }
        
        // Validar que se envíe el proceso_ingreso_id
        $request->validate([
            'proceso_ingreso_id' => 'required|exists:procesos_ingresos,id',
        ]);
        
        $proceso = ProcesoIngreso::findOrFail($request->proceso_ingreso_id);
        
        // Obtener todas las solicitudes del proceso
        $solicitudes = Solicitud::where('proceso_ingreso_id', $proceso->id)->get();
        
        if ($solicitudes->isEmpty()) {
            return back()->withErrors(['error' => 'No hay solicitudes para finalizar']);
        }
        
        // Finalizar todas las solicitudes
        foreach ($solicitudes as $solicitud) {
            $estadoAnterior = $solicitud->estado;
            $solicitud->update([
                'estado' => 'Finalizada',
                'fecha_finalizacion' => now(),
            ]);
            
            // Log del cambio
            Log::info("finalizacion_masiva_solicitud", [
                'solicitud_id' => $solicitud->id,
                'estado_anterior' => $estadoAnterior,
                'proceso_id' => $proceso->id,
                'usuario' => $user->email,
                'timestamp' => now(),
            ]);
            
            // Enviar notificación
            try {
                $this->enviarNotificacionEstado($solicitud, $estadoAnterior, 'Finalizada');
            } catch (\Exception $e) {
                Log::warning("notificacion_finalizacion_masiva_failed: {$e->getMessage()}");
            }
        }
        
        // Marcar el proceso como Finalizado
        $proceso->update([
            'estado' => 'Finalizado',
            'fecha_finalizacion' => now(),
        ]);
        
        // Generar check-in automático
        $checkin = $this->generarCheckinAutomatico($proceso);
        if ($checkin) {
            $mensaje = 'Todas las solicitudes finalizadas y check-in generado automáticamente.';
        } else {
            $mensaje = 'Todas las solicitudes finalizadas. El check-in ya estaba generado.';
        }
        
        // Redirigir a procesos-ingreso con mensaje
        return redirect()->route('procesos-ingreso.index')
                       ->with('success', $mensaje);
    }

    /**
     * Enviar notificación de cambio de estado
     */
    /**
     * Mostrar check-in consolidado (cuando todas las solicitudes están finalizadas)
     */
    public function checkinConsolidado($procesoId)
    {
        $proceso = \App\Models\ProcesoIngreso::with(['solicitudes', 'cargo', 'area', 'jefe'])->findOrFail($procesoId);

        // Verificar que sea Jefe RRHH, Root, o un Jefe del proceso
        $user = auth()->user();
        $puedeVerCheckin = $user->hasRole(['Root', 'Jefe RRHH']) || 
                          $user->hasRole(['Jefe Tecnología', 'Jefe Dotación', 'Jefe Servicios Generales', 'Jefe Bienes y Servicios']);

        if (!$puedeVerCheckin) {
            abort(403, 'No tienes permiso para ver este check-in.');
        }

        // Verificar que TODAS las solicitudes estén finalizadas
        if ($proceso->solicitudes()->where('estado', '!=', 'Finalizada')->exists()) {
            return back()->withErrors(['error' => 'No todas las solicitudes están finalizadas. El check-in solo puede verse cuando todas sean "Finalizada".']);
        }

        // Cargar todas las solicitudes y sus detalles
        $solicitudes = $proceso->solicitudes()->with(['detalleTecnologia', 'detalleUniforme', 'detalleBienes', 'puestoTrabajo', 'cursos'])->get();

        $tecnologia = $solicitudes->firstWhere('tipo', 'Tecnología');
        $dotacion = $solicitudes->firstWhere('tipo', 'Dotación');
        $serviciosGenerales = $solicitudes->firstWhere('tipo', 'Servicios Generales');
        $formacion = $solicitudes->firstWhere('tipo', 'Formación');
        $bienes = $solicitudes->firstWhere('tipo', 'Bienes')
            ?? $solicitudes->firstWhere('tipo', 'Bienes y Servicios');

        return view('solicitudes.checkin-consolidado', compact(
            'proceso',
            'tecnologia',
            'dotacion',
            'serviciosGenerales',
            'formacion',
            'bienes'
        ));
    }

    private function enviarNotificacionEstado(Solicitud $solicitud, string $estadoAnterior, string $estadoNuevo)
    {
        $notificationService = new \App\Services\NotificationService();
        $notificationService->notificarCambioEstado($solicitud, $estadoAnterior, $estadoNuevo);
    }

    private function generarCheckinAutomatico(ProcesoIngreso $proceso): ?Checkin
    {
        $proceso->load([
            'solicitudes.puestoTrabajo',
            'solicitudes.detalleTecnologia',
            'solicitudes.detalleUniforme',
            'solicitudes.detalleBienes',
            'solicitudes.cursos',
            'jefe',
        ]);

        if (Checkin::where('proceso_ingreso_id', $proceso->id)->exists()) {
            return null;
        }

        $activos = [];

        foreach ($proceso->solicitudes as $solicitud) {
            switch ($solicitud->tipo) {
                case 'Tecnología':
                    if ($detalles = $solicitud->detalleTecnologia) {
                        if ($detalles->necesita_computador) {
                            $activos[] = [
                                'item' => 'Computador',
                                'especificaciones' => "Gama: {$detalles->gama_computador}",
                                'entregado' => false,
                            ];
                        }
                        if (!empty($detalles->credenciales_plataformas)) {
                            $activos[] = [
                                'item' => 'Credenciales y Plataformas',
                                'especificaciones' => $detalles->credenciales_plataformas,
                                'entregado' => false,
                            ];
                        }
                    }
                    break;

                case 'Dotación':
                    if ($detalles = $solicitud->detalleUniforme) {
                        if ($detalles->necesita_dotacion) {
                            $activos[] = [
                                'item' => 'Dotación',
                                'especificaciones' => "Pantalon: {$detalles->talla_pantalon}, Camiseta: {$detalles->talla_camiseta}",
                                'entregado' => false,
                            ];
                        } else {
                            $activos[] = [
                                'item' => 'Dotación (No requerida)',
                                'especificaciones' => $detalles->justificacion_no_dotacion,
                                'entregado' => false,
                            ];
                        }
                    }
                    break;

                case 'Servicios Generales':
                    if ($solicitud->puestoTrabajo) {
                        $activos[] = [
                            'item' => "Puesto de Trabajo ({$solicitud->puestoTrabajo->numero_puesto})",
                            'especificaciones' => "Seccion: {$solicitud->puestoTrabajo->seccion}, Piso: {$solicitud->puestoTrabajo->piso}",
                            'entregado' => false,
                        ];
                    }
                    break;

                case 'Formación':
                    if ($solicitud->cursos && $solicitud->cursos->count() > 0) {
                        $lista = $solicitud->cursos->pluck('nombre')->implode(', ');
                        $activos[] = [
                            'item' => 'Plan de Formacion',
                            'especificaciones' => $lista,
                            'entregado' => false,
                        ];
                    }
                    break;

                case 'Bienes':
                case 'Bienes y Servicios':
                    if ($solicitud->detalleBienes && !empty($solicitud->detalleBienes->bienes_requeridos)) {
                        $bienesRaw = $solicitud->detalleBienes->bienes_requeridos;
                        if (is_string($bienesRaw)) {
                            $bienesRaw = json_decode($bienesRaw, true);
                        }
                        $bienesLista = is_array($bienesRaw) ? $bienesRaw : [];
                        $bienes = implode(', ', $bienesLista);
                        $activos[] = [
                            'item' => 'Bienes y Servicios',
                            'especificaciones' => $bienes,
                            'entregado' => false,
                        ];
                    }
                    if ($solicitud->detalleBienes && !empty($solicitud->detalleBienes->observaciones)) {
                        $activos[] = [
                            'item' => 'Observaciones de Bienes',
                            'especificaciones' => $solicitud->detalleBienes->observaciones,
                            'entregado' => false,
                        ];
                    }
                    break;

                default:
                    $activos[] = [
                        'item' => ucfirst($solicitud->tipo),
                        'especificaciones' => $solicitud->observaciones,
                        'entregado' => false,
                    ];
            }
        }

        $emailEmpleado = $proceso->email
            ?? $proceso->jefe?->email
            ?? auth()->user()?->email
            ?? config('mail.from.address')
            ?? 'no-reply@sinergia.com';

        return Checkin::create([
            'proceso_ingreso_id' => $proceso->id,
            'codigo_verificacion' => Checkin::generarCodigoVerificacion(),
            'activos_entregados' => $activos,
            'estado_checkin' => 'Pendiente',
            'fecha_generacion' => now(),
            'email_empleado' => $emailEmpleado,
        ]);
    }

    /**
     * Actualizar solicitud (generalmente solo estado)
     */
    public function update(Request $request, Solicitud $solicitude)
    {
        $this->verificarPermiso($solicitude);

        $request->validate([
            'estado' => 'in:Pendiente,En Proceso,Finalizada',
            'observaciones' => 'nullable|string',
        ]);

        if ($request->has('estado')) {
            $solicitude->estado = $request->estado;
        }

        if ($request->has('observaciones')) {
            $solicitude->observaciones = $request->observaciones;
        }

        $solicitude->save();

        return redirect()->route('solicitudes.show', $solicitude->id)
            ->with('success', 'Solicitud actualizada correctamente');
    }

    /**
     * Verificar permisos del usuario
     */
    private function verificarPermiso($solicitud)
    {
        $user = auth()->user();

        // Root puede ver todo
        if ($user->hasRole('Root')) {
            return true;
        }

        // Jefe RRHH puede ver todas las solicitudes
        if ($user->hasRole('Jefe RRHH')) {
            return true;
        }

        // Jefe Tecnología puede ver SOLO solicitudes de Tecnología
        if ($user->hasRole('Jefe Tecnología')) {
            if ($solicitud->tipo === 'Tecnología') {
                return true;
            }
        }

        // Jefe Dotación puede ver SOLO solicitudes de Dotación
        if ($user->hasRole('Jefe Dotación')) {
            if ($solicitud->tipo === 'Dotación') {
                return true;
            }
        }

        // Jefe Servicios Generales puede ver SOLO solicitudes de Servicios Generales
        if ($user->hasRole('Jefe Servicios Generales')) {
            if ($solicitud->tipo === 'Servicios Generales') {
                return true;
            }
        }

        // Jefe Bienes y Servicios puede ver SOLO solicitudes de Bienes y Servicios
        if ($user->hasRole('Jefe Bienes y Servicios')) {
            if (in_array($solicitud->tipo, ['Bienes', 'Bienes y Servicios'], true)) {
                return true;
            }
        }

        // Si no tiene permiso, lanzar excepción
        abort(403, 'No tienes permiso para ver esta solicitud.');
    }

    /**
     * Obtener todos los puestos de trabajo con su estado para el plano interactivo
     */
    public function obtenerPuestosPlano()
    {
        $puestos = \App\Models\PuestoTrabajo::with([
            'solicitudes' => function ($query) {
                $query->where('estado', '!=', 'Cancelado');
            }
        ])->get()->map(function ($puesto) {
            return [
                'id' => $puesto->id,
                'numero_puesto' => $puesto->numero_puesto,
                'piso' => $puesto->piso,
                'seccion' => $puesto->seccion,
                'estado' => $this->determinarEstadoPuesto($puesto),
                'ubicacion_x' => $puesto->ubicacion_x ?? 0,
                'ubicacion_y' => $puesto->ubicacion_y ?? 0,
                'descripcion' => $puesto->descripcion,
                'equipamiento' => $puesto->equipamiento,
            ];
        });

        return response()->json([
            'success' => true,
            'puestos' => $puestos,
            'estados' => [
                'Disponible' => '#28A745',
                'Reservado' => '#FFC107',
                'Asignado' => '#E74C3C',
                'En Mantenimiento' => '#95A5A6',
                'Bloqueado' => '#34495E',
            ]
        ]);
    }

    /**
     * Determinar el estado visual de un puesto
     */
    private function determinarEstadoPuesto($puesto)
    {
        // Si el puesto está marcado como bloqueado en el modelo
        if ($puesto->estado === 'En Mantenimiento' || $puesto->estado === 'Bloqueado') {
            return $puesto->estado;
        }

        // Revisar si tiene solicitudes activas
        $solicitudActiva = $puesto->solicitudes()
            ->where('estado', '!=', 'Cancelado')
            ->first();

        if ($solicitudActiva) {
            return 'Asignado';
        }

        return 'Disponible';
    }

    /**
     * Reservar un puesto para una solicitud
     */
    public function reservarPuesto(Request $request, $puestoId)
    {
        $request->validate([
            'solicitud_id' => 'required|exists:solicitudes,id',
        ]);

        $puesto = \App\Models\PuestoTrabajo::findOrFail($puestoId);
        $solicitud = Solicitud::findOrFail($request->solicitud_id);

        // Verificar que sea una solicitud de Servicios Generales
        if ($solicitud->tipo !== 'Servicios Generales') {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud no es de tipo Servicios Generales'
            ], 400);
        }

        // Verificar que el puesto esté disponible
        $estadoPuesto = $this->determinarEstadoPuesto($puesto);
        if ($estadoPuesto !== 'Disponible') {
            return response()->json([
                'success' => false,
                'message' => "El puesto no está disponible. Estado actual: {$estadoPuesto}"
            ], 400);
        }

        // Desasignar puesto anterior si existe
        if ($solicitud->puesto_trabajo_id) {
            $puestoAnterior = \App\Models\PuestoTrabajo::find($solicitud->puesto_trabajo_id);
            if ($puestoAnterior && $puestoAnterior->estado === 'Asignado') {
                $puestoAnterior->estado = 'Disponible';
                $puestoAnterior->save();
            }
        }

        // Asignar nuevo puesto
        $solicitud->puesto_trabajo_id = $puesto->id;
        $solicitud->save();

        // Marcar puesto como asignado
        $puesto->estado = 'Asignado';
        $puesto->save();

        // Registrar en auditoría
        try {
            \App\Models\AuditoriaOnboarding::create([
                'usuario_id' => auth()->id(),
                'accion' => 'Reserva de Puesto de Trabajo',
                'entidad' => 'Solicitud',
                'entidad_id' => $solicitud->id,
                'motivo' => "Puesto {$puesto->numero_puesto} (Piso {$puesto->piso}, Sección {$puesto->seccion}) asignado",
                'valores_anteriores' => ['puesto_trabajo_id' => null],
                'valores_nuevos' => [
                    'puesto_trabajo_id' => $puesto->id,
                    'puesto_numero' => $puesto->numero_puesto,
                    'fecha_asignacion' => now()->format('Y-m-d H:i:s'),
                ],
                'ip_origin' => $request->ip(),
            ]);
        } catch (\Exception $e) {
            // Si falla la auditoría, no impedir la operación principal
            \Log::warning('Error registrando auditoría en reservarPuesto: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Puesto {$puesto->numero_puesto} reservado correctamente",
            'puesto' => [
                'id' => $puesto->id,
                'numero_puesto' => $puesto->numero_puesto,
                'piso' => $puesto->piso,
                'seccion' => $puesto->seccion,
            ]
        ]);
    }
}

