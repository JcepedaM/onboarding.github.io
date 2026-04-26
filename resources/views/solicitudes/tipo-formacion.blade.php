<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl leading-tight" style="color: #1B365D;">
                📚 Solicitud de Formación #{{ $solicitude->id }}
            </h2>
            <a href="{{ route('solicitudes.index') }}" class="btn-outline-primary">← Volver</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                    <p class="text-green-700 font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Panel Izquierdo -->
                <div class="lg:col-span-1 space-y-4">
                    @if ($solicitude->proceso)
                        <div class="bg-white rounded-lg shadow p-6" style="border-left: 4px solid #1B365D;">
                            <h3 class="text-lg font-bold mb-4" style="color: #1B365D;">👤 Empleado</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-600 uppercase">Nombre</p>
                                    <p class="font-semibold text-sm" style="color: #1B365D;">{{ $solicitude->proceso->nombre_completo }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600 uppercase">Cargo</p>
                                    <p class="font-semibold text-sm" style="color: #1B365D;">{{ $solicitude->proceso->cargo->nombre }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600 uppercase">Área</p>
                                    <p class="font-semibold text-sm" style="color: #28A745;">{{ $solicitude->proceso->area->nombre }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white rounded-lg shadow p-6" style="border-left: 4px solid #28A745;">
                        <h3 class="text-lg font-bold mb-4" style="color: #28A745;">📊 Estado</h3>
                        <p class="text-2xl font-bold text-center" style="color: #28A745;">{{ $solicitude->estado }}</p>
                        @if($solicitude->estado === 'Finalizada' && $solicitude->fecha_finalizacion)
                            <p class="text-sm text-gray-600 text-center mt-3">{{ $solicitude->fecha_finalizacion?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                        @else
                            <p class="text-sm text-gray-600 text-center mt-3">{{ $solicitude->fecha_limite?->format('d/m/Y') ?? 'N/A' }}</p>
                        @endif
                    </div>

                    @if($solicitude->cursos->count() > 0)
                        <div class="bg-white rounded-lg shadow p-6" style="border-left: 4px solid #C59D42;">
                            <h3 class="text-lg font-bold mb-4" style="color: #C59D42;">📚 Cursos</h3>
                            <p class="text-3xl font-bold text-center" style="color: #C59D42;">{{ $solicitude->cursos->count() }}</p>
                            <p class="text-xs text-gray-600 text-center mt-2">cursos asignados</p>
                        </div>
                    @endif
                </div>

                <!-- Panel Central y Derecho -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6" style="border-left: 4px solid #1B365D;">
                        <h3 class="text-xl font-bold mb-6" style="color: #1B365D;">📚 Plan de Formación</h3>

                        @if($solicitude->cursos->count() > 0)
                            <!-- Mostrar Cursos Asignados -->
                            <div class="p-4 bg-green-50 rounded border-l-4 border-green-500 mb-6">
                                <h4 class="font-bold text-green-900 mb-4">✅ Cursos Asignados</h4>
                                
                                <div class="space-y-3">
                                    @foreach($solicitude->cursos as $curso)
                                        <div class="p-3 border rounded bg-white">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <p class="font-semibold" style="color: #1B365D;">{{ $curso->nombre }}</p>
                                                    <p class="text-xs text-gray-600">{{ $curso->duracion_horas }}h - {{ $curso->modalidad }}</p>
                                                </div>
                                                <span class="px-2 py-1 text-xs rounded" style="background-color: #E8F5E9; color: #28A745;">
                                                    ✅ Asignado
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if(Auth::user()->hasRole(['Jefe RRHH', 'Root']))
                                    <div class="mt-4">
                                        <button onclick="document.getElementById('formulario-cursos').style.display = 'block'" class="btn-secondary">
                                            ✏️ Modificar Cursos
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-600 mb-6">No se han asignado cursos. Selecciona a continuación:</p>
                        @endif

                        <!-- Formulario -->
                        <div id="formulario-cursos" style="display: {{ $solicitude->cursos->count() > 0 ? 'none' : 'block' }};">
                            <form action="{{ route('solicitudes.guardar-formacion', $solicitude->id) }}" method="POST" class="space-y-6">
                                @csrf

                                <div>
                                    <label class="block text-sm font-bold mb-3" style="color: #1B365D;">
                                        📚 Selecciona Cursos para el Plan de Formación
                                    </label>

                                    @php
                                        $todosCursos = \App\Models\Curso::activos()->orderBy('nombre')->get();
                                        $cursosAsignados = $solicitude->cursos->pluck('id')->toArray();
                                    @endphp

                                    @if($todosCursos->count() > 0)
                                        <div class="space-y-2 max-h-96 overflow-y-auto p-4 bg-gray-50 rounded border"
                                             style="border-color: #1B365D;">
                                            @foreach($todosCursos as $curso)
                                                <label class="flex items-start p-3 border rounded bg-white hover:bg-blue-50 cursor-pointer transition">
                                                    <input type="checkbox" name="curso_ids[]" value="{{ $curso->id }}" 
                                                           class="mt-1 mr-3"
                                                           {{ in_array($curso->id, $cursosAsignados) ? 'checked' : '' }}>
                                                    <div class="flex-1">
                                                        <div class="font-semibold text-sm" style="color: #1B365D;">{{ $curso->nombre }}</div>
                                                        <div class="text-xs text-gray-600 mt-1">
                                                            <span>⏱️ {{ $curso->duracion_horas }}h</span>
                                                            <span class="mx-1">|</span>
                                                            <span>🎓 {{ $curso->modalidad }}</span>
                                                            @if($curso->categoria)
                                                                <span class="mx-1">|</span>
                                                                <span>📂 {{ $curso->categoria }}</span>
                                                            @endif
                                                        </div>
                                                        @if($curso->descripcion)
                                                            <p class="text-xs text-gray-600 mt-1 italic">{{ Str::limit($curso->descripcion, 100) }}</p>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
                                            <p class="text-yellow-700">
                                                ⚠️ No hay cursos disponibles en este momento.
                                            </p>
                                        </div>
                                    @endif

                                    <p class="text-xs text-gray-600 mt-3">
                                        ℹ️ Selecciona todos los cursos que el empleado debe completar según su cargo.
                                    </p>
                                </div>

                                @if($todosCursos->count() > 0)
                                    <!-- Botones -->
                                    <div class="flex gap-4 pt-4 border-t">
                                        <button type="submit" class="btn-primary">
                                            ✅ Asignar Cursos
                                        </button>
                                        @if($solicitude->cursos->count() > 0)
                                            <button type="button" onclick="document.getElementById('formulario-cursos').style.display = 'none'" class="btn-outline-primary">
                                                ❌ Cancelar
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Cambiar Estado -->
                    @if(Auth::user()->hasRole(['Jefe RRHH', 'Root']))
                        <div class="bg-white rounded-lg shadow p-6 mt-6">
                            <h3 class="text-lg font-bold mb-4" style="color: #1B365D;">🔧 Cambiar Estado</h3>
                            
                            <form action="{{ route('solicitudes.cambiar-estado', $solicitude->id) }}" method="POST" class="space-y-4">
                                @csrf
                                
                                <div>
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">Nuevo Estado</label>
                                    <select name="estado" class="w-full border rounded px-4 py-2" style="border-color: #1B365D;">
                                        <option value="Pendiente" {{ $solicitude->estado === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="En Proceso" {{ $solicitude->estado === 'En Proceso' ? 'selected' : '' }}>En Proceso</option>
                                        <option value="Finalizada" {{ $solicitude->estado === 'Finalizada' ? 'selected' : '' }}>Finalizada</option>
                                    </select>
                                </div>

                                <button type="submit" class="w-full btn-secondary">
                                    🔄 Actualizar Estado
                                </button>
                            </form>
                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
