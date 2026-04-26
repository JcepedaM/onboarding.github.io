<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\AsignacionCursoController;
use App\Http\Controllers\RutaFormacionController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\CheckInAccesoController;

// MÓDULO DE FORMACIÓN Y CURSOS
Route::middleware(['auth', 'verified'])->group(function () {

    // Cursos - CRUD Completo
    Route::resource('cursos', CursoController::class);
    Route::post('/cursos/{curso}/asignar-cargo', [CursoController::class, 'asignarACargo'])
        ->name('cursos.asignar-cargo');
    Route::post('/cursos/exportar', [CursoController::class, 'exportar'])
        ->name('cursos.exportar');

    // Asignaciones de Cursos - Panel RRHH
    Route::prefix('asignaciones')->name('asignaciones.')->group(function () {
        Route::get('/', [AsignacionCursoController::class, 'index'])
            ->name('index');
        Route::get('/panel', [AsignacionCursoController::class, 'panel'])
            ->name('panel');
        Route::get('/{procesoIngreso}/asignar', [AsignacionCursoController::class, 'asignar'])
            ->name('asignar');
        Route::post('/{procesoIngreso}/guardar', [AsignacionCursoController::class, 'guardar'])
            ->name('guardar');
        Route::get('/{asignacion}', [AsignacionCursoController::class, 'show'])
            ->name('show');
        Route::get('/{asignacion}/validar', [AsignacionCursoController::class, 'validar'])
            ->name('validar');
        Route::post('/{asignacion}/marcar-completada', [AsignacionCursoController::class, 'marcarCompletada'])
            ->name('marcar-completada');
        Route::post('/{asignacion}/marcar-progreso', [AsignacionCursoController::class, 'marcarEnProgreso'])
            ->name('marcar-progreso');
        Route::post('/{asignacion}/cancelar', [AsignacionCursoController::class, 'cancelar'])
            ->name('cancelar');
    });

    // Rutas de Formación
    Route::resource('rutas', RutaFormacionController::class);
    Route::post('/rutas/{ruta}/agregar-curso', [RutaFormacionController::class, 'agregarCurso'])
        ->name('rutas.agregar-curso');
    Route::post('/rutas/{ruta}/remover-curso', [RutaFormacionController::class, 'removerCurso'])
        ->name('rutas.remover-curso');

    // MÓDULO DE REPORTES
    Route::prefix('reportes')->name('reportes.')->group(function () {

        // Centro de Reportes
        Route::get('/', [ReporteController::class, 'index'])
            ->name('index');
        
        // Dashboard Ejecutivo
        Route::get('/dashboard', [ReporteController::class, 'dashboard'])
            ->name('dashboard');

        // Cumplimiento por Área
        Route::get('/cumplimiento-por-area', [ReporteController::class, 'cumplimientoPorArea'])
            ->name('cumplimiento-por-area');

        // Formación por Curso
        Route::get('/formacion-por-curso', [ReporteController::class, 'formacionPorCurso'])
            ->name('formacion-por-curso');

        // Asignaciones Pendientes
        Route::get('/asignaciones-pendientes', [ReporteController::class, 'asignacionesPendientes'])
            ->name('asignaciones-pendientes');

        // Retrasos en Formación
        Route::get('/retrasos-formacion', [ReporteController::class, 'retrasosFormacion'])
            ->name('retrasos-formacion');

        // Costos de Formación
        Route::get('/costos-formacion', [ReporteController::class, 'costosFormacion'])
            ->name('costos-formacion');

        // Reporte de Eficiencia
        Route::get('/eficiencia', [ReporteController::class, 'eficiencia'])
            ->name('eficiencia');

        // Exportar datos
        Route::post('/exportar', [ReporteController::class, 'exportarDatos'])
            ->name('exportar');
    });

    // MÓDULO DE AUDITORÍA
    Route::prefix('auditoria')->name('auditoria.')->group(function () {
        
        Route::get('/', [AuditoriaController::class, 'index'])
            ->name('index');
        
        Route::get('/dashboard', [AuditoriaController::class, 'dashboard'])
            ->name('dashboard');

        // Endpoint AJAX para actualización en tiempo real
        Route::get('/datos/actualizados', [AuditoriaController::class, 'obtenerDatosActualizados'])
            ->name('datos-actualizados');

        Route::get('/{auditoria}', [AuditoriaController::class, 'show'])
            ->name('show');
        
        Route::get('/proceso/{proceso}', [AuditoriaController::class, 'porProceso'])
            ->name('por-proceso');
        
        Route::get('/actividad/usuario', [AuditoriaController::class, 'actividadPorUsuario'])
            ->name('actividad-usuario');

        Route::get('/actividad/entidad', [AuditoriaController::class, 'actividadPorEntidad'])
            ->name('actividad-entidad');

        Route::get('/timeline', [AuditoriaController::class, 'timelineAuditoria'])
            ->name('timeline');
        
        Route::get('/reporte/por-area', [AuditoriaController::class, 'reportePorArea'])
            ->name('reporte-por-area');
        
        Route::post('/exportar', [AuditoriaController::class, 'exportar'])
            ->name('exportar');
    });

    // MÓDULO DE CHECK-IN DE ACCESOS (HU11) - ACCESO SEGURO AL SISTEMA
    Route::prefix('checkin-acceso')->name('checkin-acceso.')->group(function () {
        
        // Ruta base: redirigir a bienvenida
        Route::get('', fn() => redirect()->route('checkin-acceso.mostrar-bienvenida'))
            ->name('index');
        
        // Pantalla de Bienvenida (Mostrar al usuario)
        Route::get('/bienvenida', [CheckInAccesoController::class, 'mostrarBienvenida'])
            ->name('mostrar-bienvenida');
        
        // Procesar el check-in del usuario
        Route::post('/procesar', [CheckInAccesoController::class, 'procesarCheckIn'])
            ->name('procesar');
        
        // Ver historial de accesos del usuario
        Route::get('/historial', [CheckInAccesoController::class, 'verHistorialAccesos'])
            ->name('historial');
        
        // Panel de administración (Solo Admin/Root)
        Route::get('/admin', [CheckInAccesoController::class, 'panelAdministracion'])
            ->middleware('can:viewCheckInAdmin')
            ->name('panel-admin');
        
        // Estadísticas por área (Solo Admin/Root)
        Route::get('/area/{areaId}/estadisticas', [CheckInAccesoController::class, 'estadisticasArea'])
            ->middleware('can:viewCheckInAdmin')
            ->name('estadisticas-area');
        
        // Exportar datos (Solo Admin/Root)
        Route::post('/exportar', [CheckInAccesoController::class, 'exportarAccesos'])
            ->middleware('can:exportCheckIn')
            ->name('exportar');
        
        // API: Obtener datos de check-in (AJAX)
        Route::get('/datos', [CheckInAccesoController::class, 'obtenerDatosCheckIn'])
            ->name('datos');
    });
});
