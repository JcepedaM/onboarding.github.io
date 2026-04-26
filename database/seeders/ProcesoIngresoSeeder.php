<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProcesoIngreso;
use App\Models\Solicitud;
use App\Models\Cargo;
use App\Models\User;
use App\Models\PlantillaSolicitud;
use Carbon\Carbon;

class ProcesoIngresoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener cargos, áreas y jefes
        $cargos = Cargo::with('area')->get();
        
        // Obtener jefe de RRHH para los procesos
        $jefeRRHH = User::where('email', 'jefe.rrhh@example.com')->first();
        
        if (!$jefeRRHH) {
            $this->command->info('No se encontró jefe de RRHH. Abortando seeder de procesos de ingreso.');
            return;
        }

        // Crear 3 procesos de ingreso de prueba
        $empleados = [
            [
                'nombre' => 'Carlos Alberto López',
                'documento' => '1001234567',
                'cargo_id' => 2, // Desarrollador
            ],
            [
                'nombre' => 'María González Ruiz',
                'documento' => '1002345678',
                'cargo_id' => 1, // Analista de RRHH
            ],
            [
                'nombre' => 'Juan Carlos Martínez',
                'documento' => '1003456789',
                'cargo_id' => 4, // Instructor
            ],
        ];

        foreach ($empleados as $empleado) {
            $cargo = Cargo::with('area')->find($empleado['cargo_id']);
            
            // Crear proceso de ingreso
            $proceso = ProcesoIngreso::create([
                'codigo' => 'ING-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'nombre_completo' => $empleado['nombre'],
                'tipo_documento' => 'Cédula',
                'documento' => $empleado['documento'],
                'cargo_id' => $cargo->id,
                'area_id' => $cargo->area_id,
                'fecha_ingreso' => Carbon::now()->addDays(7),
                'jefe_id' => $jefeRRHH->id,
                'estado' => 'Pendiente',
            ]);

            // Crear solicitudes automáticas basadas en plantillas
            $plantillas = PlantillaSolicitud::where('cargo_id', $cargo->id)->get();

            foreach ($plantillas as $plantilla) {
                // Estrategia: Usar la fecha más segura (máximo entre: fecha_ingreso - días y hoy + 2 días)
                // Esto previene solicitudes vencidas si el plazo de ingreso es muy corto
                $fecha_ingreso = Carbon::parse($proceso->fecha_ingreso);
                $fecha_limite_ideal = $fecha_ingreso->copy()->subDays($plantilla->dias_maximos);
                $fecha_limite_minima = now()->addDays(2);
                $fecha_limite = $fecha_limite_ideal->lt($fecha_limite_minima) ? $fecha_limite_minima : $fecha_limite_ideal;
                
                Solicitud::create([
                    'proceso_ingreso_id' => $proceso->id,
                    'area_id' => $plantilla->area_id,
                    'tipo' => $plantilla->tipo_solicitud,
                    'fecha_limite' => $fecha_limite,
                    'estado' => 'Pendiente',
                ]);
            }

            $this->command->info("Proceso de ingreso creado: {$proceso->codigo} - {$empleado['nombre']}");
        }

        $this->command->info('Procesos de ingreso y solicitudes creados exitosamente.');
    }
}
