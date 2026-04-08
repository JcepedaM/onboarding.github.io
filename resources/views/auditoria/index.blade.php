<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight" style="color: #1B365D;">
                🔒 Panel de Auditoría - Trazabilidad de Movimientos
            </h2>
            <a href="{{ route('auditoria.dashboard') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                📊 Dashboard
            </a>
        </div>
    </x-slot>

    <div class="container mx-auto px-4 py-8">
        {{-- Filtros --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4">🔍 Filtros de Búsqueda</h3>
            <form method="GET" action="{{ route('auditoria.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Búsqueda General --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Búsqueda General</label>
                    <input type="text" name="busqueda" placeholder="Usuario, entidad, acción..." 
                           value="{{ request('busqueda') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                {{-- Acción --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Acción</label>
                    <select name="accion" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Todas las acciones --</option>
                        <option value="create" {{ request('accion') == 'create' ? 'selected' : '' }}>Creación</option>
                        <option value="update" {{ request('accion') == 'update' ? 'selected' : '' }}>Actualización</option>
                        <option value="delete" {{ request('accion') == 'delete' ? 'selected' : '' }}>Eliminación</option>
                        <option value="view" {{ request('accion') == 'view' ? 'selected' : '' }}>Visualización</option>
                        <option value="export" {{ request('accion') == 'export' ? 'selected' : '' }}>Exportación</option>
                        <option value="anular" {{ request('accion') == 'anular' ? 'selected' : '' }}>Anulación</option>
                    </select>
                </div>

                {{-- Entidad --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Entidad</label>
                    <select name="entidad" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Todas las entidades --</option>
                        @foreach($entidades as $entidad)
                            <option value="{{ $entidad }}" {{ request('entidad') == $entidad ? 'selected' : '' }}>{{ $entidad }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Usuario --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                    <select name="usuario_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Todos los usuarios --</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }} ({{ $usuario->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Fecha Desde --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                {{-- Fecha Hasta --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                {{-- Botones --}}
                <div class="flex gap-2 items-end">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold">
                        🔎 Filtrar
                    </button>
                    <a href="{{ route('auditoria.index') }}" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                        ↺ Limpiar
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabla de Registros --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">📋 Registros de Auditoría (Total: {{ $registros->total() }})</h3>
                <form action="{{ route('auditoria.exportar') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="fecha_desde" value="{{ request('fecha_desde') }}">
                    <input type="hidden" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    <input type="hidden" name="accion" value="{{ request('accion') }}">
                    <input type="hidden" name="entidad" value="{{ request('entidad') }}">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-bold">
                        💾 Exportar
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #e5e7eb;">
                            <th class="text-left p-3 text-gray-700 font-bold">Fecha/Hora</th>
                            <th class="text-left p-3 text-gray-700 font-bold">Usuario</th>
                            <th class="text-left p-3 text-gray-700 font-bold">Acción</th>
                            <th class="text-left p-3 text-gray-700 font-bold">Entidad</th>
                            <th class="text-left p-3 text-gray-700 font-bold">ID Entidad</th>
                            <th class="text-left p-3 text-gray-700 font-bold">IP Origin</th>
                            <th class="text-left p-3 text-gray-700 font-bold">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $registro)
                            @if($registro->usuario)
                            <tr style="border-bottom: 1px solid #f0f0f0; hover: background-color: #f8f9fa;">
                                <td class="p-3 text-gray-700 font-medium">{{ $registro->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="p-3 text-gray-700">
                                    <strong>{{ $registro->usuario->name }}</strong>
                                    <br>
                                    <small class="text-gray-500">{{ $registro->usuario->email }}</small>
                                </td>
                                <td class="p-3 text-gray-700">
                                    @switch($registro->accion)
                                        @case('create')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-bold">✓ Creación</span>
                                            @break
                                        @case('update')
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold">✎ Actualización</span>
                                            @break
                                        @case('delete')
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-bold">✕ Eliminación</span>
                                            @break
                                        @case('view')
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-bold">👁️ Visualización</span>
                                            @break
                                        @case('export')
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-bold">📤 Exportación</span>
                                            @break
                                        @default
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-bold">{{ ucfirst($registro->accion) }}</span>
                                    @endswitch
                                </td>
                                <td class="p-3 text-gray-700 font-semibold">{{ $registro->entidad }}</td>
                                <td class="p-3 text-gray-700">{{ $registro->entidad_id }}</td>
                                <td class="p-3 text-gray-700 text-xs">{{ $registro->ip_origin }}</td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('auditoria.show', $registro->id) }}" class="text-blue-600 hover:text-blue-800 font-bold">
                                        Ver Detalles
                                    </a>
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-500">
                                    No se encontraron registros de auditoría
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-6">
                {{ $registros->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
