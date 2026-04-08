<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight" style="color: #1B365D;">
            📊 Actividad por Entidad
        </h2>
    </x-slot>

    <div class="container mx-auto px-4 py-8">
        {{-- Selector de Entidad --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4">🔍 Filtrar por Entidad</h3>
            <form method="GET" action="{{ route('auditoria.actividad-entidad') }}" class="flex gap-4">
                <select name="entidad" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">-- Selecciona una entidad --</option>
                    @foreach($entidades as $entidad)
                        <option value="{{ $entidad }}" {{ $entidadSeleccionada == $entidad ? 'selected' : '' }}>{{ $entidad }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold">
                    🔎 Filtrar
                </button>
            </form>
        </div>

        {{-- Tabla de Registros --}}
        @if($entidadSeleccionada)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    📋 Registros de: <span class="text-blue-600">{{ $entidadSeleccionada }}</span> (Total: {{ $registros->total() }})
                </h3>

                <div class="overflow-x-auto mb-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #e5e7eb;">
                                <th class="text-left p-3 text-gray-700 font-bold">Fecha/Hora</th>
                                <th class="text-left p-3 text-gray-700 font-bold">Usuario</th>
                                <th class="text-left p-3 text-gray-700 font-bold">Acción</th>
                                <th class="text-left p-3 text-gray-700 font-bold">ID Entidad</th>
                                <th class="text-left p-3 text-gray-700 font-bold">Motivo</th>
                                <th class="text-center p-3 text-gray-700 font-bold">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $registro)
                                @if($registro->usuario)
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td class="p-3 text-gray-700 font-medium">{{ $registro->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="p-3 text-gray-700">
                                        <strong>{{ $registro->usuario->name }}</strong>
                                        <br>
                                        <small class="text-gray-500">{{ $registro->usuario->email }}</small>
                                    </td>
                                    <td class="p-3">
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
                                            @default
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-bold">{{ ucfirst($registro->accion) }}</span>
                                        @endswitch
                                    </td>
                                    <td class="p-3 text-gray-700">{{ $registro->entidad_id }}</td>
                                    <td class="p-3 text-gray-700 text-xs">{{ $registro->motivo ?? '-' }}</td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('auditoria.show', $registro->id) }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-gray-500">
                                        No hay registros para esta entidad
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
        @else
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
                <p class="text-blue-800 font-semibold">👆 Selecciona una entidad para ver su actividad</p>
            </div>
        @endif

        <div class="mt-8">
            <a href="{{ route('auditoria.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                ← Volver al Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
