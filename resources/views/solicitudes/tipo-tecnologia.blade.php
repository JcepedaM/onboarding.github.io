<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl leading-tight" style="color: #1B365D;">
                📱 Solicitud de Tecnología #{{ $solicitude->id }}
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

                <!-- Panel Izquierdo: Información General -->
                <div class="lg:col-span-1 space-y-4">
                    <!-- Información del Proceso -->
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

                    <!-- Estado de la Solicitud -->
                    <div class="bg-white rounded-lg shadow p-6" style="border-left: 4px solid #428FFF;">
                        <h3 class="text-lg font-bold mb-4" style="color: #428FFF;">📊 Estado</h3>
                        <div class="text-center mb-4">
                            <p class="text-sm text-gray-600 uppercase mb-2">Estado Actual</p>
                            <p class="text-2xl font-bold" style="color: #428FFF;">{{ $solicitude->estado }}</p>
                        </div>
                        <div class="text-sm text-gray-600 space-y-2">
                            @if($solicitude->estado === 'Finalizada' && $solicitude->fecha_finalizacion)
                                <p><strong>Finalizada el:</strong> {{ $solicitude->fecha_finalizacion?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                            @else
                                <p><strong>Fecha Límite:</strong> {{ $solicitude->fecha_limite?->format('d/m/Y') ?? 'N/A' }}</p>
                            @endif
                            <p><strong>Creada:</strong> {{ $solicitude->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Panel Central y Derecho: Formulario -->
                <div class="lg:col-span-2">

                    <!-- Sección: ¿Necesita Computador? -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6" style="border-left: 4px solid #1B365D;">
                        <h3 class="text-xl font-bold mb-6" style="color: #1B365D;">💻 Requerimientos de Tecnología</h3>

                        @if($solicitude->detalleTecnologia)
                            <!-- Mostrar Detalles Guardados -->
                            <div class="space-y-4">
                                <div class="p-4 bg-green-50 rounded border-l-4 border-green-500">
                                    <h4 class="font-bold text-green-900 mb-3">✅ Especificaciones Guardadas</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-600 uppercase text-xs mb-1">¿Necesita Computador?</p>
                                            <p class="font-semibold" style="color: #1B365D;">
                                                {{ $solicitude->detalleTecnologia->necesita_computador ? '✅ SÍ' : '❌ NO' }}
                                            </p>
                                        </div>

                                        @if($solicitude->detalleTecnologia->necesita_computador)
                                            <div>
                                                <p class="text-gray-600 uppercase text-xs mb-1">Gama del Computador</p>
                                                <p class="font-semibold" style="color: #1B365D;">
                                                    {{ $solicitude->detalleTecnologia->gama_computador }}
                                                </p>
                                            </div>

                                            <div class="md:col-span-2">
                                                <p class="text-gray-600 uppercase text-xs mb-1">Credenciales y Plataformas Requeridas</p>
                                                <p class="font-semibold text-gray-700 whitespace-pre-wrap">
                                                    {{ $solicitude->detalleTecnologia->credenciales_plataformas }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    @if(Auth::user()->getRoleNames()->contains(fn($role) => strpos($role, 'Jefe') !== false) || Auth::user()->hasRole('Root'))
                                        <div class="mt-4">
                                            <button onclick="document.getElementById('formulario-tecnologia').style.display = 'block'" class="btn-secondary">
                                                ✏️ Editar Especificaciones
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Mostrar Formulario para Guardar -->
                            <p class="text-gray-600 mb-6">No se han especificado los requerimientos. Completa el formulario a continuación:</p>
                        @endif

                        <!-- Formulario (oculto por defecto si ya hay datos) -->
                        <div id="formulario-tecnologia" style="display: {{ $solicitude->detalleTecnologia ? 'none' : 'block' }};">
                            <form action="{{ route('solicitudes.guardar-tecnologia', $solicitude->id) }}" method="POST" class="space-y-6">
                                @csrf

                                <!-- ¿Necesita Computador? -->
                                <div>
                                    <label class="block text-sm font-bold mb-3" style="color: #1B365D;">
                                        💻 ¿Necesita Computador?
                                    </label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="necesita_computador" value="1" class="mr-2"
                                                   onchange="document.getElementById('seccion-gama').style.display = 'block'"
                                                   {{ $solicitude->detalleTecnologia?->necesita_computador ? 'checked' : '' }}>
                                            <span class="text-sm">✅ Sí</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="necesita_computador" value="0" class="mr-2"
                                                   onchange="document.getElementById('seccion-gama').style.display = 'none'"
                                                   {{ !$solicitude->detalleTecnologia?->necesita_computador || !$solicitude->detalleTecnologia ? 'checked' : '' }}>
                                            <span class="text-sm">❌ No</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Gama del Computador (Condicional) -->
                                <div id="seccion-gama" style="display: {{ $solicitude->detalleTecnologia?->necesita_computador ? 'block' : 'none' }};">
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">
                                        📊 Gama del Computador
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        @foreach(['Básica' => 'Básica (Office, navegación)', 'Media' => 'Media (Programación, diseño ligero)', 'Premium' => 'Premium (Diseño CAD, edición)'] as $value => $label)
                                            <label class="flex items-start p-4 border rounded cursor-pointer transition hover:bg-blue-50"
                                                   style="border-color: #1B365D;">
                                                <input type="radio" name="gama_computador" value="{{ $value }}" class="mt-1 mr-3"
                                                       {{ $solicitude->detalleTecnologia?->gama_computador === $value ? 'checked' : '' }}>
                                                <div>
                                                    <div class="font-semibold text-sm" style="color: #1B365D;">{{ $value }}</div>
                                                    <div class="text-xs text-gray-600">{{ $label }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Credenciales y Plataformas -->
                                <div>
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">
                                        🔑 Credenciales de Plataformas Requeridas
                                    </label>
                                    <textarea name="credenciales_plataformas" rows="5" class="w-full border rounded px-4 py-2"
                                              style="border-color: #1B365D;"
                                              placeholder="Ejemplo:&#10;- Email corporativo: usuario@sinergia.coop&#10;- Sistema Core: Credencial autogenerada&#10;- Slack: workspace sinergia-coop&#10;- Bases de datos: acceso a BD_Operativa">{{ $solicitude->detalleTecnologia?->credenciales_plataformas }}</textarea>
                                    <p class="text-xs text-gray-600 mt-2">
                                        Especifica qué plataformas, sistemas y credenciales necesita el empleado
                                    </p>
                                </div>

                                <!-- Botones -->
                                <div class="flex gap-4 pt-4 border-t">
                                    <button type="submit" class="btn-primary">
                                        ✅ Guardar Especificaciones
                                    </button>
                                    @if($solicitude->detalleTecnologia)
                                        <button type="button" onclick="document.getElementById('formulario-tecnologia').style.display = 'none'" class="btn-outline-primary">
                                            ❌ Cancelar
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Cambiar Estado (Solo para operadores de TI o Jefes) -->
                    @if(Auth::user()->getRoleNames()->contains(fn($role) => strpos($role, 'Tecnología') !== false || strpos($role, 'Admin') !== false) || Auth::user()->hasRole('Root'))
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-bold mb-4" style="color: #1B365D;">🔧 Cambiar Estado</h3>
                            
                            <form action="{{ route('solicitudes.cambiar-estado', $solicitude->id) }}" method="POST" class="space-y-4">
                                @csrf
                                
                                <div>
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">
                                        Nuevo Estado
                                    </label>
                                    <select name="estado" class="w-full border rounded px-4 py-2" style="border-color: #1B365D;">
                                        <option value="Pendiente" {{ $solicitude->estado === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="En Proceso" {{ $solicitude->estado === 'En Proceso' ? 'selected' : '' }}>En Proceso</option>
                                        <option value="Finalizada" {{ $solicitude->estado === 'Finalizada' ? 'selected' : '' }}>Finalizada</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">
                                        Observaciones (Opcional)
                                    </label>
                                    <textarea name="observaciones" rows="3" class="w-full border rounded px-4 py-2"
                                              placeholder="Agregar comentarios..."
                                              style="border-color: #1B365D;"></textarea>
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
