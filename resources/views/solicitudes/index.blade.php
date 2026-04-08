<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl">📋 Panel de Solicitudes por Área</h2>
                <p class="text-sm text-gray-600 mt-1">
                    Área: <strong>{{ Auth::user()->area->nombre ?? 'Sistema General' }}</strong>
                </p>
            </div>
            <a href="{{ route('procesos-ingreso.index') }}" class="text-blue-600 hover:text-blue-800 whitespace-nowrap">Ir a Procesos</a>
        </div>
    </x-slot>

    <div class="p-4 sm:p-6">

        {{-- Mensajes --}}
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded flex items-center gap-2">
                <span>❌</span> {{ $errors->first() }}
            </div>
        @endif

        {{-- Panel de Estadísticas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded shadow">
                <p class="text-gray-600 text-sm font-semibold">Pendientes</p>
                <p class="text-3xl font-bold text-yellow-600">{{ $solicitudes->where('estado', 'Pendiente')->count() }}</p>
            </div>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow">
                <p class="text-gray-600 text-sm font-semibold">En Proceso</p>
                <p class="text-3xl font-bold text-blue-600">{{ $solicitudes->where('estado', 'En Proceso')->count() }}</p>
            </div>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow">
                <p class="text-gray-600 text-sm font-semibold">Entregados</p>
                <p class="text-3xl font-bold text-green-600">{{ $solicitudes->where('estado', 'Entregado')->count() }}</p>
            </div>
            <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded shadow">
                <p class="text-gray-600 text-sm font-semibold">Completados</p>
                <p class="text-3xl font-bold text-purple-600">{{ $solicitudes->where('estado', 'Completado')->count() }}</p>
            </div>
        </div>

        {{-- Tabla de solicitudes (Desktop) --}}
        <div class="hidden md:block bg-white rounded shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm">Código Proceso</th>
                        <th class="px-4 py-3 text-left text-sm">Empleado</th>
                        <th class="px-4 py-3 text-left text-sm">Tipo Solicitud</th>
                        <th class="px-4 py-3 text-left text-sm">Área</th>
                        <th class="px-4 py-3 text-left text-sm">Fecha Límite</th>
                        <th class="px-4 py-3 text-left text-sm">Estado</th>
                        <th class="px-4 py-3 text-center text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($solicitudes as $solicitud)
                        <tr class="border-b hover:bg-gray-50 @if($solicitud->fecha_limite < now()->addDays(3)) bg-red-50 @endif">
                            <td class="px-4 py-3 font-bold text-sm">{{ $solicitud->proceso?->codigo ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $solicitud->proceso?->nombre_completo ?? 'Sin proceso' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                                    {{ $solicitud->tipo }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $solicitud->area->nombre }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="@if($solicitud->fecha_limite < now()) text-red-600 font-bold @endif">
                                    {{ $solicitud->fecha_limite->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-3 py-1 rounded text-white text-xs font-bold
                                    @if ($solicitud->estado === 'Pendiente') bg-yellow-500
                                    @elseif ($solicitud->estado === 'En Proceso') bg-blue-500
                                    @elseif ($solicitud->estado === 'Entregado') bg-green-500
                                    @else bg-purple-500 @endif">
                                    {{ $solicitud->estado }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm flex justify-center gap-2">
                                <a href="{{ route('solicitudes.show', $solicitud->id) }}" 
                                   title="Ver detalles"
                                   class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                    👁️
                                </a>
                                
                                {{-- Botón FINALIZAR TODO (solo Root y Jefe RRHH) --}}
                                @if(auth()->user()->hasRole(['Root', 'Jefe RRHH']))
                                    <form method="POST" action="{{ route('solicitudes.finalizar-todas') }}" style="display: inline;" 
                                          onsubmit="return confirm('¿Estás seguro de que deseas finalizar TODAS las solicitudes de este proceso?');">
                                        @csrf
                                        <input type="hidden" name="proceso_ingreso_id" value="{{ $solicitud->proceso_ingreso_id }}">
                                        <button type="submit" title="Finalizar TODAS las solicitudes del proceso"
                                                class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 font-bold">
                                            FINALIZAR TODO
                                        </button>
                                    </form>
                                @endif
                                
                                @if($solicitud->estado === 'Pendiente')
                                    <form method="POST" action="{{ route('solicitudes.cambiar-estado', $solicitud->id) }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="estado" value="En Proceso">
                                        <button type="submit" title="Marcar como en proceso"
                                                class="bg-orange-600 text-white px-3 py-1 rounded text-sm hover:bg-orange-700">
                                            ⏳
                                        </button>
                                    </form>
                                @elseif($solicitud->estado === 'En Proceso')
                                    <form method="POST" action="{{ route('solicitudes.cambiar-estado', $solicitud->id) }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="estado" value="Entregado">
                                        <button type="submit" title="Marcar como entregado"
                                                class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                            ✓
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                No hay solicitudes asignadas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tarjetas de solicitudes (Mobile) --}}
        <div class="md:hidden space-y-4">
            @forelse ($solicitudes as $solicitud)
                <div class="bg-white rounded shadow p-4 border-l-4 
                    @if ($solicitud->estado === 'Pendiente') border-yellow-500
                    @elseif ($solicitud->estado === 'En Proceso') border-blue-500
                    @elseif ($solicitud->estado === 'Entregado') border-green-500
                    @else border-purple-500 @endif">
                    
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="font-bold text-lg text-gray-800">{{ $solicitud->proceso?->codigo ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ $solicitud->proceso?->nombre_completo ?? 'Sin proceso' }}</p>
                        </div>
                        <span class="px-3 py-1 rounded text-white text-xs font-bold
                            @if ($solicitud->estado === 'Pendiente') bg-yellow-500
                            @elseif ($solicitud->estado === 'En Proceso') bg-blue-500
                            @elseif ($solicitud->estado === 'Entregado') bg-green-500
                            @else bg-purple-500 @endif">
                            {{ $solicitud->estado }}
                        </span>
                    </div>
                    
                    <div class="space-y-2 mb-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tipo:</span>
                            <span class="font-semibold text-gray-800">{{ $solicitud->tipo }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Área:</span>
                            <span class="font-semibold text-gray-800">{{ $solicitud->area->nombre }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Fecha Límite:</span>
                            <span class="font-semibold @if($solicitud->fecha_limite < now()) text-red-600 font-bold @else text-gray-800 @endif">
                                {{ $solicitud->fecha_limite->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="{{ route('solicitudes.show', $solicitud->id) }}" 
                           class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700 text-center font-semibold">
                            👁️ Ver
                        </a>
                        
                        {{-- Botón FINALIZAR TODO (solo Root y Jefe RRHH) --}}
                        @if(auth()->user()->hasRole(['Root', 'Jefe RRHH']))
                            <form method="POST" action="{{ route('solicitudes.finalizar-todas') }}" class="flex-1"
                                  onsubmit="return confirm('¿Estás seguro de que deseas finalizar TODAS las solicitudes de este proceso?');">
                                @csrf
                                <input type="hidden" name="proceso_ingreso_id" value="{{ $solicitud->proceso_ingreso_id }}">
                                <button type="submit" class="w-full bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700 font-semibold">
                                    FINALIZAR TODO
                                </button>
                            </form>
                        @endif
                        
                        @if($solicitud->estado === 'Pendiente')
                            <form method="POST" action="{{ route('solicitudes.cambiar-estado', $solicitud->id) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="estado" value="En Proceso">
                                <button type="submit" class="w-full bg-orange-600 text-white px-3 py-2 rounded text-sm hover:bg-orange-700 font-semibold">
                                    ⏳ Procesar
                                </button>
                            </form>
                        @elseif($solicitud->estado === 'En Proceso')
                            <form method="POST" action="{{ route('solicitudes.cambiar-estado', $solicitud->id) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="estado" value="Entregado">
                                <button type="submit" class="w-full bg-green-600 text-white px-3 py-2 rounded text-sm hover:bg-green-700 font-semibold">
                                    ✓ Entregar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded shadow p-6 text-center text-gray-500">
                    📭 No hay solicitudes asignadas
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        @if ($solicitudes instanceof \Illuminate\Pagination\Paginator)
            <div class="mt-6">
                {{ $solicitudes->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
