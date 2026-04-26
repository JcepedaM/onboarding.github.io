<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl">🏢 Asignación de Puestos de Trabajo</h2>
                <p class="text-sm text-gray-600 mt-1">
                    Selecciona un puesto disponible del mapa interactivo
                </p>
            </div>
            <a href="{{ route('solicitudes.index') }}" class="text-blue-600 hover:text-blue-800 whitespace-nowrap">← Volver a Solicitudes</a>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Mapa de Puestos (Izquierda) --}}
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded shadow">
                    <h3 class="font-bold text-lg mb-4">Mapa de Puestos - Piso {{ $piso }}</h3>

                    {{-- Legenda --}}
                    <div class="grid grid-cols-3 gap-2 mb-6 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-green-500 rounded border border-green-700"></div>
                            <span>Disponible</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-red-500 rounded border border-red-700"></div>
                            <span>Ocupado</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-gray-400 rounded border border-gray-600"></div>
                            <span>Mantenimiento</span>
                        </div>
                    </div>

                    {{-- Mapa de Puestos (Grid Visual) --}}
                    <div class="bg-gray-100 p-4 rounded border-2 border-gray-300">
                        <div class="grid grid-cols-4 gap-3">
                            @foreach($puestos as $puesto)
                                <button 
                                    onclick="seleccionarPuesto({{ $puesto->id }}, '{{ $puesto->numero_puesto }}')"
                                    class="p-4 rounded font-semibold text-white transition transform hover:scale-105 cursor-pointer
                                    @if($puesto->estado === 'En Mantenimiento')
                                        bg-gray-400 cursor-not-allowed opacity-60
                                    @elseif($puesto->estaDisponible())
                                        bg-green-500 hover:bg-green-600
                                    @else
                                        bg-red-500 cursor-not-allowed opacity-75
                                    @endif"
                                    @if($puesto->estado === 'En Mantenimiento' || !$puesto->estaDisponible()) disabled @endif
                                    title="@if($puesto->estaDisponible())Disponible @else Ocupado - {{ $puesto->empleadoActual()?->solicitud?->proceso?->nombre_completo }} @endif">
                                    {{ $puesto->numero_puesto }}
                                    @if($puesto->descripcion)
                                        <div class="text-xs mt-1">{{ $puesto->descripcion }}</div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filtro de Pisos --}}
                    <div class="mt-6 flex gap-2">
                        @php
                            $pisos = $puestos->pluck('piso')->unique()->sort();
                        @endphp
                        @foreach($pisos as $p)
                            <a href="?piso={{ $p }}" 
                               class="px-4 py-2 rounded font-semibold transition
                               @if($p === $piso)
                                   bg-blue-600 text-white
                               @else
                                   bg-gray-300 text-gray-800 hover:bg-gray-400
                               @endif">
                                Piso {{ $p }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Panel de Información (Derecha) --}}
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded shadow sticky top-20">
                    <h3 class="font-bold text-lg mb-4">Información del Empleado</h3>

                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-gray-600 font-semibold">Empleado:</p>
                            <p class="text-gray-800">{{ $solicitud->proceso->nombre_completo }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600 font-semibold">Cargo:</p>
                            <p class="text-gray-800">{{ $solicitud->proceso->cargo->nombre }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600 font-semibold">Fecha de Ingreso:</p>
                            <p class="text-gray-800">{{ $solicitud->proceso->fecha_ingreso->format('d/m/Y') }}</p>
                        </div>

                        <hr class="my-4">

                        <div>
                            <p class="text-gray-600 font-semibold mb-2">Puesto Seleccionado:</p>
                            <div id="puesto-seleccionado" class="p-3 bg-blue-50 rounded border-l-4 border-blue-500">
                                <p class="text-gray-600 text-xs italic">Selecciona un puesto del mapa</p>
                            </div>
                        </div>

                        <form id="form-asignar" method="POST" action="{{ route('servicios-generales.asignar-puesto', $solicitud->id) }}" style="display: none;">
                            @csrf
                            <input type="hidden" name="puesto_trabajo_id" id="puesto_trabajo_id">
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-semibold">
                                ✓ Asignar Puesto
                            </button>
                        </form>
                    </div>

                    {{-- Info Gráfica --}}
                    <div class="mt-6 space-y-2 text-xs">
                        <div class="flex justify-between p-2 bg-green-50 rounded">
                            <span>Disponibles:</span>
                            <span class="font-bold">{{ $puestos->where('estado', 'Disponible')->count() }}</span>
                        </div>
                        <div class="flex justify-between p-2 bg-red-50 rounded">
                            <span>Ocupados:</span>
                            <span class="font-bold">{{ $puestos->where('estado', 'Asignado')->count() }}</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span>Total:</span>
                            <span class="font-bold">{{ $puestos->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        function seleccionarPuesto(puestoId, numeroPuesto) {
            document.getElementById('puesto_trabajo_id').value = puestoId;
            document.getElementById('puesto-seleccionado').innerHTML = `
                <p class="font-semibold text-blue-700">Puesto: <span class="text-lg">${numeroPuesto}</span></p>
                <p class="text-xs text-gray-600 mt-1">Listo para asignar al empleado</p>
            `;
            document.getElementById('form-asignar').style.display = 'block';
        }
    </script>
</x-app-layout>
