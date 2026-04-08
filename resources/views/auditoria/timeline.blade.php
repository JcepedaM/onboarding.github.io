<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight" style="color: #1B365D;">
            ⏱️ Timeline de Auditoría
        </h2>
    </x-slot>

    <div class="container mx-auto px-4 py-8 max-w-3xl">
        {{-- Selector de Rango de Días --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4">🔍 Filtrar por Período</h3>
            <form method="GET" action="{{ route('auditoria.timeline') }}" class="flex gap-4">
                <select name="dias" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="7" {{ $dias == 7 ? 'selected' : '' }}>Últimos 7 días</option>
                    <option value="14" {{ $dias == 14 ? 'selected' : '' }}>Últimos 14 días</option>
                    <option value="30" {{ $dias == 30 ? 'selected' : '' }}>Últimos 30 días</option>
                    <option value="60" {{ $dias == 60 ? 'selected' : '' }}>Últimos 60 días</option>
                    <option value="90" {{ $dias == 90 ? 'selected' : '' }}>Últimos 90 días</option>
                </select>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold">
                    🔎 Aplicar
                </button>
            </form>
        </div>

        {{-- Timeline --}}
        <div class="bg-white rounded-lg shadow-md p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-8">
                Últimos {{ $dias }} días ({{ $registros->total() }} eventos)
            </h3>

            <div class="space-y-6 relative">
                {{-- Línea vertical --}}
                <div class="absolute left-0 md:left-1/2 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-600 to-purple-600 transform md:-translate-x-1/2"></div>

                @forelse($registros as $key => $registro)
                    @if($registro->usuario)
                    <div class="md:grid md:grid-cols-2 gap-8">
                        {{-- Lado Izquierdo (Alternado) --}}
                        @if($key % 2 == 0)
                            <div class="pr-8 md:pr-0"></div>
                        @else
                            <div class="pr-8 md:pr-0 md:pl-8 md:text-right"></div>
                        @endif

                        {{-- Contenido del Evento --}}
                        <div class="relative">
                            <div class="absolute left-0 md:left-auto md:right-0 top-5 transform translate-x-2 md:-translate-x-2 w-5 h-5 bg-white border-4 border-blue-600 rounded-full"></div>

                            <div class="pl-8 md:pl-0 bg-gray-50 p-6 rounded-lg border-l-4 md:border-l-0 md:border-r-4 border-blue-600">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $registro->usuario->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $registro->usuario->email }}</p>
                                    </div>
                                    @switch($registro->accion)
                                        @case('create')
                                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded text-xs font-bold whitespace-nowrap ml-4">✓ Creación</span>
                                            @break
                                        @case('update')
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold whitespace-nowrap ml-4">✎ Actualización</span>
                                            @break
                                        @case('delete')
                                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded text-xs font-bold whitespace-nowrap ml-4">✕ Eliminación</span>
                                            @break
                                        @default
                                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded text-xs font-bold whitespace-nowrap ml-4">{{ ucfirst($registro->accion) }}</span>
                                    @endswitch
                                </div>

                                <div class="mb-3 pb-3 border-b border-gray-300">
                                    <p class="text-sm font-semibold text-gray-700">
                                        <span class="font-bold">{{ $registro->entidad }}</span>
                                        <span class="text-gray-500">(ID: {{ $registro->entidad_id }})</span>
                                    </p>
                                </div>

                                <div class="flex items-center justify-between text-xs text-gray-600">
                                    <span>🕐 {{ $registro->created_at->format('d/m/Y H:i:s') }}</span>
                                    <span>{{ $registro->created_at->diffForHumans() }}</span>
                                </div>

                                @if($registro->ip_origin)
                                    <p class="text-xs text-gray-500 mt-2">🌐 IP: {{ $registro->ip_origin }}</p>
                                @endif

                                <div class="mt-4 pt-3 border-t border-gray-300">
                                    <a href="{{ route('auditoria.show', $registro->id) }}" class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                        Ver Detalles →
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Lado Derecho (Alternado) --}}
                        @if($key % 2 != 0)
                            <div class="pr-8 md:pr-0"></div>
                        @else
                            <div class="pr-8 md:pr-0 md:pl-8 md:text-right"></div>
                        @endif
                    </div>
                    @endif
                @empty
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center col-span-full">
                        <p class="text-blue-800">No hay eventos en este período</p>
                    </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            <div class="mt-8">
                {{ $registros->links() }}
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('auditoria.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                ← Volver al Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
