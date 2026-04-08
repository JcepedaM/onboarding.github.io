<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight" style="color: #1B365D;">
                🔍 Detalles de Auditoría
            </h2>
            <a href="{{ route('auditoria.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        {{-- Información General --}}
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-blue-600">📋 Información General</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                {{-- Usuario --}}
                @if($registro->usuario)
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">👤 Usuario</p>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="font-bold text-blue-900">{{ $registro->usuario->name }}</p>
                        <p class="text-sm text-blue-700">{{ $registro->usuario->email }}</p>
                        <p class="text-xs text-blue-600 mt-2">Área: 
                            @if($registro->usuario->area)
                                {{ $registro->usuario->area->nombre }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
                @else
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">👤 Usuario</p>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-600">Usuario no disponible</p>
                    </div>
                </div>
                @endif

                {{-- Fecha y Hora --}}
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">🕐 Fecha y Hora</p>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="font-bold text-green-900">{{ $registro->created_at->format('d/m/Y') }}</p>
                        <p class="text-lg text-green-700">{{ $registro->created_at->format('H:i:s') }}</p>
                        <p class="text-xs text-green-600 mt-2">{{ $registro->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                {{-- Acción --}}
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">⚡ Acción</p>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        @switch($registro->accion)
                            @case('create')
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-bold text-sm">✓ CREACIÓN</span>
                                @break
                            @case('update')
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded font-bold text-sm">✎ ACTUALIZACIÓN</span>
                                @break
                            @case('delete')
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded font-bold text-sm">✕ ELIMINACIÓN</span>
                                @break
                            @case('view')
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded font-bold text-sm">👁️ VISUALIZACIÓN</span>
                                @break
                            @case('export')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded font-bold text-sm">📤 EXPORTACIÓN</span>
                                @break
                            @default
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded font-bold text-sm">{{ strtoupper($registro->accion) }}</span>
                        @endswitch
                    </div>
                </div>

                {{-- Información de Entidad --}}
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">📦 Entidad</p>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <p class="font-bold text-purple-900">{{ $registro->entidad }}</p>
                        <p class="text-sm text-purple-700">ID: {{ $registro->entidad_id }}</p>
                    </div>
                </div>
            </div>

            {{-- Información de Red --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- IP Origin --}}
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">🌐 IP de Origen</p>
                    <p class="font-mono bg-gray-100 p-3 rounded-lg text-gray-800">{{ $registro->ip_origin ?? 'No disponible' }}</p>
                </div>

                {{-- User Agent --}}
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-1">🖥️ Navegador/Dispositivo</p>
                    <p class="text-xs text-gray-700 bg-gray-100 p-3 rounded-lg break-words">{{ $registro->user_agent ?? 'No disponible' }}</p>
                </div>
            </div>

            {{-- Motivo (si existe) --}}
            @if($registro->motivo)
                <div class="mt-6 border-t pt-6">
                    <p class="text-sm font-semibold text-gray-600 mb-2">📝 Motivo/Descripción</p>
                    <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-600">
                        <p class="text-gray-800">{{ $registro->motivo }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Cambios Registrados --}}
        @if($registro->accion === 'update' && count($cambios) > 0)
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-blue-600">🔄 Cambios Registrados</h3>
                
                <div class="space-y-4">
                    @foreach($cambios as $campo => $cambio)
                        <div class="border rounded-lg p-4" style="border-left: 4px solid #3b82f6;">
                            <p class="font-bold text-gray-800 mb-3">{{ $campo }}</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Valor Anterior --}}
                                <div>
                                    <p class="text-xs font-semibold text-red-600 mb-2">❌ VALOR ANTERIOR</p>
                                    <div class="bg-red-50 p-3 rounded border border-red-200 font-mono text-sm text-red-900 break-words">
                                        @if(is_array($cambio['anterior']) || is_object($cambio['anterior']))
                                            {{ json_encode($cambio['anterior'], JSON_PRETTY_PRINT) }}
                                        @else
                                            {{ $cambio['anterior'] ?? '(vacío)' }}
                                        @endif
                                    </div>
                                </div>

                                {{-- Valor Nuevo --}}
                                <div>
                                    <p class="text-xs font-semibold text-green-600 mb-2">✓ VALOR NUEVO</p>
                                    <div class="bg-green-50 p-3 rounded border border-green-200 font-mono text-sm text-green-900 break-words">
                                        @if(is_array($cambio['nuevo']) || is_object($cambio['nuevo']))
                                            {{ json_encode($cambio['nuevo'], JSON_PRETTY_PRINT) }}
                                        @else
                                            {{ $cambio['nuevo'] ?? '(vacío)' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($registro->accion === 'create')
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-green-600">✓ Datos Creados</h3>
                
                @if($registro->valores_nuevos)
                    <div class="bg-green-50 p-6 rounded-lg border-l-4 border-green-600">
                        <pre class="text-sm text-green-900 font-mono overflow-x-auto">{{ json_encode($registro->valores_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @else
                    <p class="text-gray-600">No hay datos disponibles</p>
                @endif
            </div>
        @elseif($registro->accion === 'delete')
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6 pb-4 border-b-2 border-red-600">✕ Datos Eliminados</h3>
                
                @if($registro->valores_anteriores)
                    <div class="bg-red-50 p-6 rounded-lg border-l-4 border-red-600">
                        <pre class="text-sm text-red-900 font-mono overflow-x-auto">{{ json_encode($registro->valores_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @else
                    <p class="text-gray-600">No hay datos disponibles</p>
                @endif
            </div>
        @endif

        {{-- Acciones --}}
        <div class="flex gap-4 mb-8">
            <a href="{{ route('auditoria.index') }}" class="flex-1 px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-bold text-center">
                ← Volver a Registros
            </a>
            <a href="{{ route('auditoria.dashboard') }}" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold text-center">
                📊 Ver Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
