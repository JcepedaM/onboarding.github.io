<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProcesoIngresoController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\CargoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grupo de rutas protegidas por auth
Route::middleware('auth')->group(function () {

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // PROCESOS DE INGRESO - usamos resource para index, create, store, etc.
    Route::resource('procesos-ingreso', ProcesoIngresoController::class);

    // Rutas adicionales para gestión de procesos
    Route::get('/procesos-ingreso/{id}/cambiar-fecha', [ProcesoIngresoController::class, 'cambiarFecha'])->name('procesos-ingreso.cambiar-fecha');
    Route::post('/procesos-ingreso/{id}/actualizar-fecha', [ProcesoIngresoController::class, 'actualizarFecha'])->name('procesos-ingreso.actualizar-fecha');
    Route::get('/procesos-ingreso/{id}/cancelar', [ProcesoIngresoController::class, 'mostrarCancelacion'])->name('procesos-ingreso.mostrar-cancelacion');
    Route::post('/procesos-ingreso/{id}/cancelar', [ProcesoIngresoController::class, 'cancelar'])->name('procesos-ingreso.cancelar');
    
    // Histórico de ingresos
    Route::get('/procesos-ingreso-historico', [ProcesoIngresoController::class, 'historico'])->name('procesos-ingreso.historico');
;

    // Gestion de cargos (Root)
    Route::get('/cargos', [CargoController::class, 'index'])->name('cargos.index');
    Route::patch('/cargos/{cargo}/estado', [CargoController::class, 'actualizarEstado'])->name('cargos.estado');

    // API para obtener puestos
    Route::get('/api/puestos/plano', [SolicitudController::class, 'obtenerPuestosPlano'])->name('api.puestos.plano');
    Route::post('/api/puestos/{id}/reservar', [SolicitudController::class, 'reservarPuesto'])->name('api.puestos.reservar');

    // SOLICITUDES - Módulo completo de solicitudes por área
    Route::resource('solicitudes', SolicitudController::class);
    
    // Guardar detalles de cada tipo de solicitud (vistas específicas)
    Route::post('/solicitudes/{id}/guardar-tecnologia', [SolicitudController::class, 'guardarTecnologia'])->name('solicitudes.guardar-tecnologia');
    Route::post('/solicitudes/{id}/guardar-dotacion', [SolicitudController::class, 'guardarDotacion'])->name('solicitudes.guardar-dotacion');
    Route::post('/solicitudes/{id}/guardar-servicios-generales', [SolicitudController::class, 'guardarServiciosGenerales'])->name('solicitudes.guardar-servicios-generales');
    Route::post('/solicitudes/{id}/guardar-formacion', [SolicitudController::class, 'guardarFormacion'])->name('solicitudes.guardar-formacion');
    Route::post('/solicitudes/{id}/guardar-bienes', [SolicitudController::class, 'guardarBienes'])->name('solicitudes.guardar-bienes');
    
    // Cambiar estado de solicitudes
    Route::post('/solicitudes/{id}/cambiar-estado', [SolicitudController::class, 'cambiarEstado'])->name('solicitudes.cambiar-estado');
    
    // Finalizar TODAS las solicitudes del proceso (solo Root y Jefe RRHH)
    Route::post('/solicitudes/finalizar-todas', [SolicitudController::class, 'finalizarTodas'])->name('solicitudes.finalizar-todas');
    
    // Check-in consolidado (cuando todas las solicitudes están finalizadas)
    Route::get('/procesos-ingreso/{id}/checkin-consolidado', [SolicitudController::class, 'checkinConsolidado'])->name('solicitudes.checkin-consolidado');

    // CHECK-IN - Módulo de seguimiento y recepción de activos
    Route::resource('checkins', CheckinController::class)->only(['index', 'show']);
    Route::post('/procesos-ingreso/{id}/generar-checkin', [CheckinController::class, 'generar'])->name('checkins.generar');
    Route::get('/checkins/{id}/pdf', [CheckinController::class, 'generarPDF'])->name('checkins.pdf');
    Route::get('/checkins/{id}/confirmado', [CheckinController::class, 'confirmado'])->name('checkins.confirmado');

    // Ruta para obtener jefes por área (AJAX)
    Route::get('/areas/{area}/jefes', [ProcesoIngresoController::class, 'getJefesByArea']);

});

// Rutas públicas - Check-in y confirmación de activos (sin autenticación)
Route::get('/checkin/{codigo}', [CheckinController::class, 'confirmar'])->name('checkin.confirmar');
Route::post('/checkin/{codigo}/procesar', [CheckinController::class, 'procesarConfirmacion'])->name('checkin.procesar');
Route::get('/checkin/{codigo}/confirmado', [CheckinController::class, 'confirmado'])->name('checkin.confirmado');
Route::get('/checkin/{codigo}/estado', [CheckinController::class, 'verificarEstado'])->name('checkin.estado');

// MÓDULO DE FORMACIÓN - Cursos, Asignaciones, Rutas, Reportes y Auditoría
require __DIR__.'/web-formacion.php';

require __DIR__.'/auth.php';
