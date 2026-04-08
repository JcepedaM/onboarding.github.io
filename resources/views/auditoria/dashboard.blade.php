<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight" style="color: #1B365D;">
                📊 Panel de Auditoría - Dashboard
            </h2>
            <a href="{{ route('auditoria.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Ver Registros
            </a>
        </div>
    </x-slot>

    <div class="container mx-auto px-4 py-8">
        {{-- Tarjetas de estadísticas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total de Registros --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold">Total de Registros</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalRegistros }}</p>
                    </div>
                    <svg class="w-12 h-12 text-blue-100" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H3a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V6a1 1 0 00-1-1h-3a1 1 0 000-2H6a2 2 0 00-2 2zm9.35 5.7a1 1 0 00-1.42 1.42l2 2a1 1 0 001.42 0l5-5a1 1 0 00-1.42-1.42L15 9.58l-2.65-2.65z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            {{-- Registros Hoy --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold">Registros Hoy</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $registrosHoy }}</p>
                    </div>
                    <svg class="w-12 h-12 text-green-100" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            {{-- Registros Esta Semana --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold">Esta Semana</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $registrosEstaSemanagento }}</p>
                    </div>
                    <svg class="w-12 h-12 text-yellow-100" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L11 9.414V13H5.5z"></path>
                    </svg>
                </div>
            </div>

            {{-- Registros Este Mes --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold">Este Mes</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ $registrosEsteMes }}</p>
                    </div>
                    <svg class="w-12 h-12 text-purple-100" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Gráficos y tablas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            {{-- Acciones Más Frecuentes --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">🔍 Acciones Más Frecuentes</h3>
                <div class="space-y-3">
                    @foreach($accionesFrequentes as $accion)
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">{{ $accion->accion }}</p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(($accion->total / $totalRegistros) * 100, 100) }}%"></div>
                                </div>
                            </div>
                            <span class="ml-4 text-sm font-bold text-gray-800">{{ $accion->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Entidades Más Modificadas --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">📝 Entidades Más Modificadas</h3>
                <div class="space-y-3">
                    @foreach($entidadesModificadas as $entidad)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-700">{{ $entidad->entidad }}</span>
                            <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-bold">{{ $entidad->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Usuarios --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">👥 Top 10 Usuarios Más Activos</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="border-bottom: 2px solid #e5e7eb;">
                                <th class="text-left p-3 text-gray-700 font-bold">#</th>
                                <th class="text-left p-3 text-gray-700 font-bold">Usuario</th>
                                <th class="text-left p-3 text-gray-700 font-bold">Registros</th>
                                <th class="text-left p-3 text-gray-700 font-bold">Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuariosActivos as $key => $usuario)
                                @if($usuario->usuario)
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td class="p-3 text-gray-700">{{ $key + 1 }}</td>
                                    <td class="p-3 text-gray-700">
                                        <strong>{{ $usuario->usuario->name }}</strong>
                                        <br>
                                        <small class="text-gray-500">{{ $usuario->usuario->email }}</small>
                                    </td>
                                    <td class="p-3 text-gray-700 font-semibold">{{ $usuario->total }}</td>
                                    <td class="p-3 text-gray-700">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($usuario->total / $totalRegistros) * 100 }}%"></div>
                                            </div>
                                            <span>{{ round(($usuario->total / $totalRegistros) * 100, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 h-fit">
                <h3 class="text-lg font-bold text-gray-800 mb-4">⚡ Acceso Rápido</h3>
                <div class="space-y-2">
                    <a href="{{ route('auditoria.index') }}" class="block p-3 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-600 font-medium transition">
                        📋 Ver Todos los Registros
                    </a>
                    <a href="{{ route('auditoria.actividad-usuario') }}" class="block p-3 bg-green-50 hover:bg-green-100 rounded-lg text-green-600 font-medium transition">
                        👤 Actividad por Usuario
                    </a>
                    <a href="{{ route('auditoria.actividad-entidad') }}" class="block p-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg text-yellow-600 font-medium transition">
                        📊 Actividad por Entidad
                    </a>
                    <a href="{{ route('auditoria.timeline') }}" class="block p-3 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 font-medium transition">
                        ⏱️ Timeline
                    </a>
                </div>
            </div>
        </div>

        {{-- Últimos Registros --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📌 Últimos Registros</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th class="text-left p-3 text-gray-700 font-bold">Usuario</th>
                            <th class="text-left p-3 text-gray-700 font-bold">Acción</th>
                            <th class="text-left p-3 text-gray-700 font-bold">Entidad</th>
                            <th class="text-left p-3 text-gray-700 font-bold">Fecha</th>
                            <th class="text-left p-3 text-gray-700 font-bold">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimosRegistros as $registro)
                            @if($registro->usuario)
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td class="p-3 text-gray-700">{{ $registro->usuario->name }}</td>
                                <td class="p-3 text-gray-700">
                                    @switch($registro->accion)
                                        @case('create')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-bold">Creación</span>
                                            @break
                                        @case('update')
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold">Actualización</span>
                                            @break
                                        @case('delete')
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-bold">Eliminación</span>
                                            @break
                                        @default
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-bold">{{ ucfirst($registro->accion) }}</span>
                                    @endswitch
                                </td>
                                <td class="p-3 text-gray-700 font-semibold">{{ $registro->entidad }}</td>
                                <td class="p-3 text-gray-600">{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('auditoria.show', $registro->id) }}" class="text-blue-600 hover:text-blue-800 font-bold">Ver</a>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
