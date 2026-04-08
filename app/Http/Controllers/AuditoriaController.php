<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaOnboarding;
use App\Models\ProcesoIngreso;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AuditoriaController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditoriaOnboarding::class);

        $query = AuditoriaOnboarding::with('usuario')->whereNotNull('usuario_id');

        if ($request->accion) {
            $query->where('accion', $request->accion);
        }

        if ($request->entidad) {
            $query->where('entidad', $request->entidad);
        }

        if ($request->usuario_id) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->fecha_desde) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->where('created_at', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        if ($request->busqueda) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('accion', 'like', "%{$busqueda}%")
                  ->orWhere('entidad', 'like', "%{$busqueda}%")
                  ->orWhereHas('usuario', function ($userQ) use ($busqueda) {
                      $userQ->where('name', 'like', "%{$busqueda}%")
                            ->orWhere('email', 'like', "%{$busqueda}%");
                  });
            });
        }

        $registros = $query->orderBy('created_at', 'desc')->paginate(25);

        $acciones = [
            'create' => 'Creación',
            'update' => 'Actualización',
            'delete' => 'Eliminación',
            'view' => 'Visualización',
            'export' => 'Exportación',
            'anular' => 'Anulación',
        ];

        $entidades = AuditoriaOnboarding::select('entidad')->distinct()->pluck('entidad')->toArray();
        $usuarios = User::whereHas('auditorias')->select('id', 'name', 'email')->get();

        return view('auditoria.index', [
            'registros' => $registros,
            'acciones' => $acciones,
            'entidades' => $entidades,
            'usuarios' => $usuarios,
        ]);
    }

    public function show(AuditoriaOnboarding $auditoria)
    {
        $this->authorize('view', $auditoria);

        return view('auditoria.show', [
            'registro' => $auditoria,
            'cambios' => $auditoria->obtenerCambios(),
        ]);
    }

    public function porProceso(ProcesoIngreso $proceso)
    {
        $this->authorize('viewAny', AuditoriaOnboarding::class);

        $registros = AuditoriaOnboarding::where('entidad', 'ProcesoIngreso')
                                       ->where('entidad_id', $proceso->id)
                                       ->orWhere(function ($query) use ($proceso) {
                                           $query->where('entidad', 'Solicitud')
                                                 ->whereIn('entidad_id', $proceso->solicitudes()->pluck('id'));
                                       })
                                       ->with('usuario')                                       ->whereNotNull('usuario_id')                                       ->orderBy('created_at', 'desc')
                                       ->paginate(20);

        return view('auditoria.por-proceso', [
            'proceso' => $proceso,
            'registros' => $registros,
        ]);
    }

    public function exportar(Request $request)
    {
        $this->authorize('viewAny', AuditoriaOnboarding::class);

        $query = AuditoriaOnboarding::with('usuario')->whereNotNull('usuario_id');

        if ($request->fecha_desde) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->where('created_at', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        if ($request->accion) {
            $query->where('accion', $request->accion);
        }

        if ($request->entidad) {
            $query->where('entidad', $request->entidad);
        }

        $registros = $query->get();

        AuditoriaOnboarding::registrarExportacion('AuditoriaOnboarding', $registros->count());

        return response()->json($registros);
    }

    public function reportePorArea(Request $request)
    {
        $this->authorize('viewAny', AuditoriaOnboarding::class);

        $areas = Area::all();
        $reportesPorArea = [];

        foreach ($areas as $area) {
            $registros = AuditoriaOnboarding::where('entidad', 'ProcesoIngreso')
                                           ->whereHas('usuario', function ($query) use ($area) {
                                               $query->where('area_id', $area->id);
                                           })
                                           ->count();

            $reportesPorArea[] = [
                'area' => $area->nombre,
                'cantidad_cambios' => $registros,
            ];
        }

        return view('auditoria.reporte-por-area', [
            'reportes' => $reportesPorArea,
        ]);
    }

    public function dashboard(Request $request)
    {
        $this->authorize('viewAny', AuditoriaOnboarding::class);

        // Estadísticas generales
        $totalRegistros = AuditoriaOnboarding::count();
        $registrosHoy = AuditoriaOnboarding::whereDate('created_at', today())->count();
        $registrosEstaSemanagento = AuditoriaOnboarding::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $registrosEsteMes = AuditoriaOnboarding::whereMonth('created_at', now()->month)->count();

        // Top 10 usuarios más activos
        $usuariosActivos = AuditoriaOnboarding::with('usuario')
            ->whereNotNull('usuario_id')
            ->select('usuario_id', DB::raw('count(*) as total'))
            ->groupBy('usuario_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Acciones más frecuentes
        $accionesFrequentes = AuditoriaOnboarding::select('accion', DB::raw('count(*) as total'))
            ->groupBy('accion')
            ->orderByDesc('total')
            ->get();

        // Entidades más modificadas
        $entidadesModificadas = AuditoriaOnboarding::select('entidad', DB::raw('count(*) as total'))
            ->groupBy('entidad')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Últimos 10 registros
        $ultimosRegistros = AuditoriaOnboarding::with('usuario')
            ->whereNotNull('usuario_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Cambios por día (últimos 30 días)
        $cambiosPorDia = AuditoriaOnboarding::select(DB::raw('DATE(created_at) as fecha'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        return view('auditoria.dashboard', [
            'totalRegistros' => $totalRegistros,
            'registrosHoy' => $registrosHoy,
            'registrosEstaSemanagento' => $registrosEstaSemanagento,
            'registrosEsteMes' => $registrosEsteMes,
            'usuariosActivos' => $usuariosActivos,
            'accionesFrequentes' => $accionesFrequentes,
            'entidadesModificadas' => $entidadesModificadas,
            'ultimosRegistros' => $ultimosRegistros,
            'cambiosPorDia' => $cambiosPorDia,
        ]);
    }

    public function actividadPorUsuario(Request $request)
    {
        $this->authorize('viewAny', AuditoriaOnboarding::class);

        $usuarios = User::whereHas('auditorias')
            ->with(['auditorias' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(50);
            }])
            ->get();

        return view('auditoria.actividad-por-usuario', [
            'usuarios' => $usuarios,
        ]);
    }

    public function actividadPorEntidad(Request $request)
    {
        $this->authorize('viewAny', AuditoriaOnboarding::class);

        $entidad = $request->entidad;
        $query = AuditoriaOnboarding::with('usuario')->whereNotNull('usuario_id');

        if ($entidad) {
            $query->where('entidad', $entidad);
        }

        $registros = $query->orderBy('created_at', 'desc')->paginate(30);

        $entidades = AuditoriaOnboarding::select('entidad')->distinct()->pluck('entidad')->toArray();

        return view('auditoria.actividad-por-entidad', [
            'registros' => $registros,
            'entidades' => $entidades,
            'entidadSeleccionada' => $entidad,
        ]);
    }

    public function timelineAuditoria(Request $request)
    {
        $this->authorize('view', AuditoriaOnboarding::class);

        $dias = $request->dias ?? 30;
        $registros = AuditoriaOnboarding::with('usuario')
            ->whereNotNull('usuario_id')
            ->where('created_at', '>=', now()->subDays($dias))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('auditoria.timeline', [
            'registros' => $registros,
            'dias' => $dias,
        ]);
    }
}
