<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl leading-tight" style="color: #1B365D;">
                👕 Solicitud de Dotación #{{ $solicitude->id }}
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
                </div>

                <!-- Panel Central y Derecho -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6" style="border-left: 4px solid #1B365D;">
                        <h3 class="text-xl font-bold mb-6" style="color: #1B365D;">👕 Especificaciones de Dotación</h3>

                        @if($solicitude->detalleUniforme)
                            <!-- Mostrar Datos Guardados -->
                            <div class="p-4 bg-green-50 rounded border-l-4 border-green-500 mb-6">
                                <h4 class="font-bold text-green-900 mb-3">✅ Especificaciones Guardadas</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-600 uppercase text-xs mb-1">¿Necesita Dotación?</p>
                                        <p class="font-semibold" style="color: #1B365D;">
                                            {{ $solicitude->detalleUniforme->necesita_dotacion ? '✅ SÍ' : '❌ NO' }}
                                        </p>
                                    </div>

                                    @if($solicitude->detalleUniforme->necesita_dotacion)
                                        <div>
                                            <p class="text-gray-600 uppercase text-xs mb-1">Género</p>
                                            <p class="font-semibold" style="color: #1B365D;">{{ $solicitude->detalleUniforme->genero }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 uppercase text-xs mb-1">Talla Pantalón</p>
                                            <p class="font-semibold" style="color: #1B365D;">{{ $solicitude->detalleUniforme->talla_pantalon }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 uppercase text-xs mb-1">Talla Camiseta</p>
                                            <p class="font-semibold" style="color: #1B365D;">{{ $solicitude->detalleUniforme->talla_camiseta }}</p>
                                        </div>
                                    @else
                                        <div class="md:col-span-2">
                                            <p class="text-gray-600 uppercase text-xs mb-1">Justificación</p>
                                            <p class="font-semibold text-gray-700">{{ $solicitude->detalleUniforme->justificacion_no_dotacion }}</p>
                                        </div>
                                    @endif
                                </div>

                                @if(Auth::user()->getRoleNames()->contains(fn($role) => strpos($role, 'Jefe') !== false) || Auth::user()->hasRole('Root'))
                                    <div class="mt-4">
                                        <button onclick="document.getElementById('formulario-dotacion').style.display = 'block'" class="btn-secondary">
                                            ✏️ Editar Especificaciones
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-600 mb-6">No se han especificado los requerimientos. Completa el formulario:</p>
                        @endif

                        <!-- Formulario -->
                        <div id="formulario-dotacion" style="display: {{ $solicitude->detalleUniforme ? 'none' : 'block' }};">
                            <form action="{{ route('solicitudes.guardar-dotacion', $solicitude->id) }}" method="POST" class="space-y-6">
                                @csrf

                                <!-- ¿Necesita Dotación? -->
                                <div>
                                    <label class="block text-sm font-bold mb-3" style="color: #1B365D;">
                                        👕 ¿Necesita Dotación?
                                    </label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="necesita_dotacion" value="1" class="mr-2"
                                                   onchange="document.getElementById('seccion-tallas').style.display = 'block'; document.getElementById('seccion-justificacion').style.display = 'none'"
                                                   {{ $solicitude->detalleUniforme?->necesita_dotacion ? 'checked' : '' }}>
                                            <span class="text-sm">✅ Sí</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="necesita_dotacion" value="0" class="mr-2"
                                                   onchange="document.getElementById('seccion-tallas').style.display = 'none'; document.getElementById('seccion-justificacion').style.display = 'block'"
                                                   {{ !$solicitude->detalleUniforme?->necesita_dotacion || !$solicitude->detalleUniforme ? 'checked' : '' }}>
                                            <span class="text-sm">❌ No</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Sección Tallas (Condicional) -->
                                <div id="seccion-tallas" style="display: {{ $solicitude->detalleUniforme?->necesita_dotacion ? 'block' : 'none' }};" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">
                                            👥 Género
                                        </label>
                                        <select name="genero" class="w-full border rounded px-4 py-2" style="border-color: #1B365D;">
                                            <option value="">Selecciona género...</option>
                                            <option value="Masculino" {{ $solicitude->detalleUniforme?->genero === 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                            <option value="Femenino" {{ $solicitude->detalleUniforme?->genero === 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                            <option value="Otro" {{ $solicitude->detalleUniforme?->genero === 'Otro' ? 'selected' : '' }}>Otro</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">
                                                👖 Talla Pantalón
                                            </label>
                                            <input type="text" name="talla_pantalon" class="w-full border rounded px-4 py-2"
                                                   placeholder="Ej: 32, M, XL" style="border-color: #1B365D;"
                                                   value="{{ $solicitude->detalleUniforme?->talla_pantalon }}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">
                                                👕 Talla Camiseta
                                            </label>
                                            <input type="text" name="talla_camiseta" class="w-full border rounded px-4 py-2"
                                                   placeholder="Ej: M, L, XL" style="border-color: #1B365D;"
                                                   value="{{ $solicitude->detalleUniforme?->talla_camiseta }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sección Justificación (Condicional) -->
                                <div id="seccion-justificacion" style="display: {{ !$solicitude->detalleUniforme?->necesita_dotacion && $solicitude->detalleUniforme ? 'block' : 'none' }};">
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B365D;">
                                        📝 Justificación (¿Por qué no necesita dotación?)
                                    </label>
                                    <textarea name="justificacion_no_dotacion" rows="4" class="w-full border rounded px-4 py-2"
                                              style="border-color: #1B365D;"
                                              placeholder="Explica la razón por la que no requiere dotación uniforme">{{ $solicitude->detalleUniforme?->justificacion_no_dotacion }}</textarea>
                                </div>

                                <!-- Botones -->
                                <div class="flex gap-4 pt-4 border-t">
                                    <button type="submit" class="btn-primary">
                                        ✅ Guardar Especificaciones
                                    </button>
                                    @if($solicitude->detalleUniforme)
                                        <button type="button" onclick="document.getElementById('formulario-dotacion').style.display = 'none'" class="btn-outline-primary">
                                            ❌ Cancelar
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Cambiar Estado -->
                    @if(Auth::user()->getRoleNames()->contains(fn($role) => strpos($role, 'Dotación') !== false || strpos($role, 'Admin') !== false) || Auth::user()->hasRole('Root'))
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
