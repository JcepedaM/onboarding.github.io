<?php

namespace App\Http\Controllers;

use App\Models\CheckInAcceso;
use App\Models\AuditoriaOnboarding;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckInAccesoController extends Controller
{
    /**
     * Mostrar pantalla de bienvenida (check-in)
     */
    public function mostrarBienvenida()
    {
        // Redirigir si no está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user();
        $area = $usuario->area;

        // Obtener último check-in del usuario
        $ultimoAcceso = CheckInAcceso::ultimoAcceso($usuario->id);
        
        // Obtener estadísticas rápidas
        $accesoHoy = CheckInAcceso::where('usuario_id', $usuario->id)
                                   ->whereDate('fecha_acceso', today())
                                   ->count();
        
        $ultimosAccesos = CheckInAcceso::porUsuario($usuario->id)
                                       ->limit(5)
                                       ->get();

        return view('checkin.bienvenida', [
            'usuario' => $usuario,
            'area' => $area,
            'ultimoAcceso' => $ultimoAcceso,
            'accesoHoy' => $accesoHoy,
            'ultimosAccesos' => $ultimosAccesos,
        ]);
    }

    /**
     * Procesar check-in del usuario
     */
    public function procesarCheckIn(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user();

        // Registrar el check-in
        $checkIn = CheckInAcceso::registrar(
            $usuario->id,
            $usuario->area_id,
            $request->ip(),
            $request->header('User-Agent')
        );

        // Registrar en auditoría
        AuditoriaOnboarding::registrar(
            accion: 'check-in',
            entidad: 'CheckInAcceso',
            entidadId: $checkIn->id,
            motivo: "Usuario ingresó al sistema - Área: " . ($usuario->area?->nombre ?? 'Sin Área'),
            valoresNuevos: $checkIn->toArray()
        );

        // Retornar respuesta JSON o redirigir
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in registrado exitosamente',
                'redirect' => route('dashboard'),
                'usuario' => [
                    'nombre' => $usuario->name,
                    'email' => $usuario->email,
                    'area' => $usuario->area->nombre,
                    'rol' => $usuario->getRoleNames(),
                ],
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Bienvenido al sistema');
    }

    /**
     * Ver historial de accesos del usuario
     */
    public function verHistorialAccesos()
    {
        $usuario = Auth::user();

        $accesos = CheckInAcceso::porUsuario($usuario->id)
                                ->paginate(20);

        // Estadísticas
        $totalAccesos = CheckInAcceso::where('usuario_id', $usuario->id)->count();
        $accesosMes = CheckInAcceso::where('usuario_id', $usuario->id)
                                   ->whereMonth('fecha_acceso', now()->month)
                                   ->count();
        $accesosSemana = CheckInAcceso::where('usuario_id', $usuario->id)
                                      ->whereBetween('fecha_acceso', [now()->startOfWeek(), now()->endOfWeek()])
                                      ->count();

        return view('checkin.historial', [
            'usuario' => $usuario,
            'accesos' => $accesos,
            'estadisticas' => [
                'total' => $totalAccesos,
                'mes' => $accesosMes,
                'semana' => $accesosSemana,
            ],
        ]);
    }

    /**
     * Panel de administración de accesos (para Admin/Root)
     */
    public function panelAdministracion(Request $request)
    {
        // Verificar autorización
        $usuario = Auth::user();
        if (!$usuario->hasAnyRole(['Admin', 'Root'])) {
            abort(403, 'No autorizado');
        }

        $query = CheckInAcceso::with('usuario', 'area');

        // Filtros
        if ($request->area_id) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->usuario_id) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->fecha_desde) {
            $query->where('fecha_acceso', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->where('fecha_acceso', '<=', $request->fecha_hasta);
        }

        $accesos = $query->orderBy('fecha_acceso', 'desc')
                         ->orderBy('hora_acceso', 'desc')
                         ->paginate(50);

        // Obtener áreas y usuarios para filtros
        $areas = Area::all();
        $usuarios = User::with('area')->get();

        // Estadísticas
        $totalAccesosHoy = CheckInAcceso::whereDate('fecha_acceso', today())->count();
        $usuariosUnicos = CheckInAcceso::select('usuario_id')
                                       ->distinct()
                                       ->count();
        $promedioPorArea = CheckInAcceso::selectRaw('area_id, COUNT(*) as total')
                                        ->whereDate('fecha_acceso', today())
                                        ->groupBy('area_id')
                                        ->with('area')
                                        ->get();

        return view('checkin.panel-admin', [
            'accesos' => $accesos,
            'areas' => $areas,
            'usuarios' => $usuarios,
            'estadisticas' => [
                'totalHoy' => $totalAccesosHoy,
                'usuariosUnicos' => $usuariosUnicos,
                'porArea' => $promedioPorArea,
            ],
        ]);
    }

    /**
     * Exportar historial de accesos
     */
    public function exportarAccesos(Request $request)
    {
        // Verificar autorización
        $usuario = Auth::user();
        if (!$usuario->hasAnyRole(['Admin', 'Root'])) {
            abort(403, 'No autorizado');
        }

        $query = CheckInAcceso::with('usuario', 'area');

        // Aplicar filtros si existen
        if ($request->usuario_id) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->area_id) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->fecha_desde) {
            $query->where('fecha_acceso', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->where('fecha_acceso', '<=', $request->fecha_hasta);
        }

        $accesos = $query->get();

        // Registrar en auditoría
        AuditoriaOnboarding::registrar(
            accion: 'export',
            entidad: 'CheckInAcceso',
            entidadId: 0,
            motivo: "Exportación de {$accesos->count()} registros de check-in"
        );

        return response()->json($accesos);
    }

    /**
     * Obtener estadísticas de accesos por área
     */
    public function estadisticasArea($areaId)
    {
        // Verificar autorización
        $usuario = Auth::user();
        if (!$usuario->hasAnyRole(['Admin', 'Root'])) {
            abort(403, 'No autorizado');
        }

        $area = Area::findOrFail($areaId);

        // Hoy
        $accesoHoy = CheckInAcceso::where('area_id', $areaId)
                                  ->whereDate('fecha_acceso', today())
                                  ->count();

        // Esta semana
        $accesoSemana = CheckInAcceso::where('area_id', $areaId)
                                     ->whereBetween('fecha_acceso', [now()->startOfWeek(), now()->endOfWeek()])
                                     ->count();

        // Este mes
        $accesoMes = CheckInAcceso::where('area_id', $areaId)
                                  ->whereMonth('fecha_acceso', now()->month)
                                  ->count();

        // Usuarios activos
        $usuariosActivos = CheckInAcceso::where('area_id', $areaId)
                                        ->whereDate('fecha_acceso', today())
                                        ->select('usuario_id')
                                        ->distinct()
                                        ->count();

        // Últimos accesos
        $ultimosAccesos = CheckInAcceso::where('area_id', $areaId)
                                       ->with('usuario')
                                       ->orderBy('fecha_acceso', 'desc')
                                       ->orderBy('hora_acceso', 'desc')
                                       ->limit(10)
                                       ->get();

        return view('checkin.estadisticas-area', [
            'area' => $area,
            'estadisticas' => [
                'hoy' => $accesoHoy,
                'semana' => $accesoSemana,
                'mes' => $accesoMes,
                'usuariosActivos' => $usuariosActivos,
            ],
            'ultimosAccesos' => $ultimosAccesos,
        ]);
    }

    /**
     * Obtener datos de check-in para AJAX
     */
    public function obtenerDatosCheckIn()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $usuario = Auth::user();

        return response()->json([
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->name,
                'email' => $usuario->email,
                'area' => $usuario->area?->nombre,
                'area_id' => $usuario->area_id,
            ],
            'ultimoAcceso' => CheckInAcceso::ultimoAcceso($usuario->id),
            'accesoHoy' => CheckInAcceso::where('usuario_id', $usuario->id)
                                        ->whereDate('fecha_acceso', today())
                                        ->first(),
        ]);
    }
}
