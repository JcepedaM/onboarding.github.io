<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl">Detalles de la Solicitud #{{ $solicitude->id }}</h2>
            <a href="{{ route('solicitudes.index') }}" class="text-blue-600 hover:text-blue-800">Volver</a>
        </div>
    </x-slot>

    <div class="p-6 max-w-4xl">

        {{-- Mensajes --}}
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Información principal --}}
        <div class="grid grid-cols-2 gap-6 mb-6">
            @if ($solicitude->proceso)
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">Información del Proceso</h3>
                <p><strong>Código:</strong> {{ $solicitude->proceso->codigo }}</p>
                <p><strong>Empleado:</strong> {{ $solicitude->proceso->nombre_completo }}</p>
                <p><strong>Cargo:</strong> {{ optional($solicitude->proceso->cargo)->nombre ?? '—' }}</p>
                <p><strong>Jefe Inmediato:</strong> {{ $solicitude->proceso->jefeCargo?->nombre ?? $solicitude->proceso->cargo?->jefeInmediato?->nombre ?? '—' }}</p>
                <p><strong>Área de Ingreso:</strong> {{ optional($solicitude->proceso->area)->nombre ?? '—' }}</p>
            </div>
            @else
            <div class="bg-yellow-50 border border-yellow-200 p-6 rounded">
                <p class="text-yellow-700"><strong>⚠️ Información del Proceso no disponible</strong></p>
            </div>
            @endif

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">Información de la Solicitud</h3>
                <p><strong>Área Responsable:</strong> {{ optional($solicitude->area)->nombre ?? '—' }}</p>
                <p><strong>Tipo de Solicitud:</strong> {{ $solicitude->tipo }}</p>
                @if($solicitude->estado === 'Finalizada' && $solicitude->fecha_finalizacion)
                    <p><strong>Finalizada el:</strong> {{ optional($solicitude->fecha_finalizacion)->format('d/m/Y H:i') ?? '—' }}</p>
                @else
                    <p><strong>Fecha Límite:</strong> {{ optional($solicitude->fecha_limite)->format('d/m/Y') ?? '—' }}</p>
                @endif
                <p>
                    <strong>Estado:</strong> 
                    <span class="px-3 py-1 rounded text-white text-sm font-bold
                        @if ($solicitude->estado === 'Pendiente') bg-yellow-500
                        @elseif ($solicitude->estado === 'En Proceso') bg-blue-500
                        @else bg-green-500 @endif">
                        {{ $solicitude->estado }}
                    </span>
                </p>
            </div>
        </div>

        {{-- Detalles técnicos --}}
        @if ($solicitude->tipo === 'Tecnología' && $solicitude->proceso && auth()->user()->hasRole('Jefe'))
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-lg font-bold mb-4">📱 Requerimientos de Tecnología</h3>
                
                @if ($solicitude->detalleTecnologia)
                    <div class="grid grid-cols-2 gap-4">
                        <p><strong>Tipo Computador:</strong> {{ $solicitude->detalleTecnologia->tipo_computador }}</p>
                        <p><strong>Marca/Modelo:</strong> {{ $solicitude->detalleTecnologia->marca_computador }}</p>
                        <p><strong>Especificaciones:</strong> {{ $solicitude->detalleTecnologia->especificaciones }}</p>
                        <p><strong>Software:</strong> {{ $solicitude->detalleTecnologia->software_requerido }}</p>
                        <p><strong>Monitor Adicional:</strong> {{ $solicitude->detalleTecnologia->monitor_adicional ? '✓ Sí' : '✗ No' }}</p>
                        <p><strong>Mouse/Teclado:</strong> {{ $solicitude->detalleTecnologia->mouse_teclado ? '✓ Sí' : '✗ No' }}</p>
                    </div>
                    <a href="{{ route('solicitudes.especificar-ti', $solicitude) }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        ✏️ Editar
                    </a>
                @else
                    <p class="text-gray-600 mb-4">No se han especificado los requerimientos técnicos</p>
                    <a href="{{ route('solicitudes.especificar-ti', $solicitude) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        ➕ Especificar Requerimientos
                    </a>
                @endif
            </div>
        @endif

        {{-- Tallas de uniformes --}}
        @if ($solicitude->tipo === 'Dotación' && $solicitude->proceso && auth()->user()->hasRole('Jefe'))
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-lg font-bold mb-4">👕 Tallas de Uniformes</h3>
                
                @if ($solicitude->detalleUniforme)
                    <div class="grid grid-cols-2 gap-4">
                        <p><strong>Género:</strong> {{ $solicitude->detalleUniforme->genero }}</p>
                        <p><strong>Cantidad de Uniformes:</strong> {{ $solicitude->detalleUniforme->cantidad_uniformes }}</p>
                        <p><strong>Talla Camisa:</strong> {{ $solicitude->detalleUniforme->talla_camisa }}</p>
                        <p><strong>Talla Pantalón:</strong> {{ $solicitude->detalleUniforme->talla_pantalon }}</p>
                        <p><strong>Talla Zapatos:</strong> {{ $solicitude->detalleUniforme->talla_zapatos }}</p>
                        @if ($solicitude->detalleUniforme->observaciones)
                            <p><strong>Observaciones:</strong> {{ $solicitude->detalleUniforme->observaciones }}</p>
                        @endif
                    </div>
                    <a href="{{ route('solicitudes.especificar-tallas', $solicitude) }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        ✏️ Editar
                    </a>
                @else
                    <p class="text-gray-600 mb-4">No se han especificado las tallas</p>
                    <a href="{{ route('solicitudes.especificar-tallas', $solicitude) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        ➕ Especificar Tallas
                    </a>
                @endif
            </div>
        @endif
        {{-- Cursos de Inducción --}}
        @if ($solicitude->tipo === 'Formación')
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-lg font-bold mb-4">📚 Cursos de Inducción para {{ optional($solicitude->proceso->cargo)->nombre }}</h3>
                
                <div class="bg-blue-50 p-4 rounded border-l-4 border-blue-500 mb-4">
                    <p class="text-gray-700">
                        <strong>Cargo:</strong> {{ optional($solicitude->proceso->cargo)->nombre ?? 'N/A' }}<br>
                        <strong>Área:</strong> {{ optional(optional($solicitude->proceso)->cargo)->area->nombre ?? 'N/A' }}
                    </p>
                </div>

                <div class="bg-yellow-50 p-4 rounded border-l-4 border-yellow-500 mb-4">
                    <p class="text-gray-700 font-semibold mb-2">📋 Módulos de Inducción Estándar:</p>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <span class="text-purple-600 mr-3 font-bold">✓</span>
                            <span class="text-gray-700">Bienvenida e Inducción Corporativa</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-purple-600 mr-3 font-bold">✓</span>
                            <span class="text-gray-700">Políticas y Procedimientos Empresa</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-purple-600 mr-3 font-bold">✓</span>
                            <span class="text-gray-700">Seguridad y Salud en el Trabajo</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-purple-600 mr-3 font-bold">✓</span>
                            <span class="text-gray-700">Ética Empresarial y Código de Conducta</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-purple-600 mr-3 font-bold">✓</span>
                            <span class="text-gray-700">Sistema de Gestión Documentos</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-purple-600 mr-3 font-bold">✓</span>
                            <span class="text-gray-700">Introducción al Rol y Responsabilidades</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-green-50 p-4 rounded border-l-4 border-green-500">
                    <p class="text-gray-700 mb-2">
                        <strong>Duración Estimada:</strong> 40 horas
                    </p>
                    <p class="text-gray-600 text-sm italic">
                        El plan de capacitación será coordinado y ejecutado por el área de Formación y Capacitación.
                    </p>
                </div>
            </div>
        @endif

        {{-- Actualizar estado (operadores) --}}
        @php
            $isOperador = auth()->user()->getRoleNames()->contains(fn($role) => str_contains($role, 'Operador'));
        @endphp
        @if ($isOperador || auth()->user()->hasRole('Admin'))
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">Cambiar Estado</h3>

                <form method="POST" action="{{ route('solicitudes.cambiar-estado', $solicitude) }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-bold mb-2">Estado *</label>
                        <select name="estado" class="w-full p-2 border rounded" required>
                            <option value="">Seleccione estado</option>
                            <option value="Pendiente" {{ $solicitude->estado === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="En Proceso" {{ $solicitude->estado === 'En Proceso' ? 'selected' : '' }}>En Proceso</option>
                            <option value="Finalizada" {{ $solicitude->estado === 'Finalizada' ? 'selected' : '' }}>Finalizada</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block font-bold mb-2">Observaciones</label>
                        <textarea name="observaciones" class="w-full p-2 border rounded" rows="3">{{ $solicitude->observaciones }}</textarea>
                    </div>

                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        ✓ Actualizar Estado
                    </button>
                </form>
            </div>
        @endif

    </div>
</x-app-layout>

