<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl leading-tight" style="color: #1B365D;">
                📦 Solicitud de Bienes y Servicios #{{ $solicitude->id }}
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

                    <div class="bg-white rounded-lg shadow p-6" style="border-left: 4px solid #C59D42;">
                        <h3 class="text-lg font-bold mb-4" style="color: #C59D42;">📊 Estado</h3>
                        <p class="text-2xl font-bold text-center" style="color: #C59D42;">{{ $solicitude->estado }}</p>
                        @if($solicitude->estado === 'Finalizada' && $solicitude->fecha_finalizacion)
                            <p class="text-sm text-gray-600 text-center mt-3">{{ $solicitude->fecha_finalizacion?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                        @else
                            <p class="text-sm text-gray-600 text-center mt-3">{{ $solicitude->fecha_limite?->format('d/m/Y') ?? 'N/A' }}</p>
                        @endif
                    </div>
                </div>

                <!-- Panel Central y Derecho -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6" style="border-left: 4px solid #1B365D;">
                        <h3 class="text-xl font-bold mb-6" style="color: #1B365D;">📦 Bienes y Servicios</h3>

                        @php
                            $bienes = [
                                'silla' => ['nombre' => 'Silla Ergonómica', 'icon' => '🪑'],
                                'escritorio' => ['nombre' => 'Escritorio', 'icon' => '🪑'],
                                'papelera' => ['nombre' => 'Papelera', 'icon' => '🗑️'],
                                'organizador' => ['nombre' => 'Organizador de Escritorio', 'icon' => '📦'],
                                'cuadernos' => ['nombre' => 'Cuadernos', 'icon' => '📓'],
                                'lapiceros' => ['nombre' => 'Lapiceros y Lápices', 'icon' => '✏️'],
                                'post_it' => ['nombre' => 'Post-Its', 'icon' => '📌'],
                                'archivador' => ['nombre' => 'Archivador', 'icon' => '🗄️'],
                                'mouse_pad' => ['nombre' => 'Mouse Pad', 'icon' => '🖱️'],
                                'cable_cargador' => ['nombre' => 'Cables y Cargadores', 'icon' => '🔌'],
                            ];

                            $bienesRaw = $solicitude->detalleBienes?->bienes_requeridos;
                            if (is_array($bienesRaw)) {
                                $bienesSeleccionados = $bienesRaw;
                            } elseif (is_string($bienesRaw)) {
                                $bienesSeleccionados = json_decode($bienesRaw, true) ?: [];
                            } else {
                                $bienesSeleccionados = [];
                            }
                        @endphp

                        @if(!empty($bienesSeleccionados))
                            <!-- Mostrar Bienes Seleccionados -->
                            <div class="p-4 bg-green-50 rounded border-l-4 border-green-500 mb-6">
                                <h4 class="font-bold text-green-900 mb-4">✅ Bienes Seleccionados</h4>
                                
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    @foreach($bienesSeleccionados as $bien)
                                        @if(isset($bienes[$bien]))
                                            <div class="p-3 border rounded bg-white text-center">
                                                <div class="text-2xl mb-1">{{ $bienes[$bien]['icon'] }}</div>
                                                <p class="text-sm font-semibold" style="color: #1B365D;">{{ $bienes[$bien]['nombre'] }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                @if(Auth::user()->getRoleNames()->contains(fn($role) => strpos($role, 'Jefe') !== false) || Auth::user()->hasRole('Root'))
                                    <div class="mt-4">
                                        <button onclick="document.getElementById('formulario-bienes').style.display = 'block'" class="btn-secondary">
                                            ✏️ Modificar Selección
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-600 mb-6">Selecciona los bienes que necesita el empleado:</p>
                        @endif

                        <!-- Formulario -->
                        <div id="formulario-bienes" style="display: {{ !empty($bienesSeleccionados) ? 'none' : 'block' }};">
                            <form action="{{ route('solicitudes.guardar-bienes', $solicitude->id) }}" method="POST" class="space-y-6">
                                @csrf

                                <div>
                                    <label class="block text-sm font-bold mb-4" style="color: #1B365D;">
                                        📦 Bienes y Servicios Disponibles
                                    </label>

                                    <p class="text-sm text-gray-600 mb-4">
                                        Selecciona palomeando los bienes que necesita:
                                    </p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4 bg-gray-50 rounded border"
                                         style="border-color: #1B365D;">
                                        @foreach($bienes as $key => $bien)
                                            <label class="flex items-start p-4 border rounded bg-white hover:bg-blue-50 cursor-pointer transition">
                                                <input type="checkbox" name="bienes[]" value="{{ $key }}" 
                                                       class="mt-1 mr-3 w-5 h-5"
                                                       {{ in_array($key, $bienesSeleccionados) ? 'checked' : '' }}>
                                                <div class="flex-1">
                                                    <div class="text-2xl mb-2">{{ $bien['icon'] }}</div>
                                                    <div class="font-semibold text-sm" style="color: #1B365D;">
                                                        {{ $bien['nombre'] }}
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    <p class="text-xs text-gray-600 mt-3">
                                        ℹ️ Marca los bienes que debe recibir el empleado para realizar sus tareas.
                                    </p>
                                </div>

                                <!-- Observaciones Adicionales -->
                                <div>
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">
                                        📝 Observaciones Adicionales (Opcional)
                                    </label>
                                    <textarea name="observaciones_bienes" rows="3" class="w-full border rounded px-4 py-2"
                                              style="border-color: #1B365D;"
                                              placeholder="Especifica cantidades, modelos o detalles específicos...">{{ $solicitude->detalleBienes?->observaciones }}</textarea>
                                </div>

                                <!-- Botones -->
                                <div class="flex gap-4 pt-4 border-t">
                                    <button type="submit" class="btn-primary">
                                        ✅ Guardar Selección
                                    </button>
                                    @if(!empty($bienesSeleccionados))
                                        <button type="button" onclick="document.getElementById('formulario-bienes').style.display = 'none'" class="btn-outline-primary">
                                            ❌ Cancelar
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Cambiar Estado -->
                    @if(Auth::user()->getRoleNames()->contains(fn($role) => strpos($role, 'Bienes') !== false || strpos($role, 'Admin') !== false) || Auth::user()->hasRole('Root'))
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
