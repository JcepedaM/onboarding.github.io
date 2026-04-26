@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Encabezado -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Bienvenido al Sistema</h1>
            <p class="text-lg text-gray-600">{{ $usuario->name }}</p>
        </div>

        <!-- Notificación Informativa -->
        <div class="mb-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700 font-semibold">
                        ℹ️ Tu primer acceso del día fue registrado automáticamente
                    </p>
                    <p class="text-sm text-blue-600 mt-1">
                        El botón <strong>"Ingresar al Sistema"</strong> es opcional y permite registrar accesos manuales adicionales si es necesario.
                    </p>
                </div>
            </div>
        </div>

        <!-- Card Principal de Bienvenida -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <!-- Área del Usuario -->
            <div class="mb-8">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-indigo-100 mb-4">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $area->nombre ?? 'Sin Área Asignada' }}</h2>
                    <p class="text-gray-600">Área de Trabajo Asignada</p>
                </div>
            </div>

            <!-- Información del Usuario -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 font-semibold mb-1">Correo Electrónico</p>
                    <p class="text-lg text-gray-800">{{ $usuario->email }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 font-semibold mb-1">Rol en el Sistema</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($usuario->getRoleNames() as $role)
                            <span class="inline-block bg-indigo-100 text-indigo-800 text-sm font-semibold px-3 py-1 rounded-full">
                                {{ $role }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Última Vez que Ingresaste -->
            @if($ultimoAcceso)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                    <p class="text-sm text-blue-600 font-semibold mb-2">Último Acceso</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-blue-600">Fecha</p>
                            <p class="font-semibold text-blue-900">{{ \Carbon\Carbon::parse($ultimoAcceso->fecha_acceso)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-600">Hora</p>
                            <p class="font-semibold text-blue-900">{{ $ultimoAcceso->hora_acceso }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-600">Dispositivo</p>
                            <p class="font-semibold text-blue-900">{{ $ultimoAcceso->dispositivo_tipo ?? 'Desconocido' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Estadísticas Rápidas -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $accesoHoy }}</p>
                    <p class="text-sm text-green-700">Accesos Hoy</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ $ultimosAccesos->count() }}</p>
                    <p class="text-sm text-purple-700">Últimos Registros</p>
                </div>
            </div>

            <!-- Accesos Recientes -->
            @if($ultimosAccesos->count() > 0)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tus Últimos 5 Accesos</h3>
                    <div class="space-y-2">
                        @foreach($ultimosAccesos as $acceso)
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 hover:bg-gray-100 transition">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $acceso->dispositivo_tipo ?? 'Dispositivo' }}</p>
                                    <p class="text-sm text-gray-600">{{ $acceso->navegador ?? 'Navegador desconocido' }} • {{ $acceso->ip_address }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-800">{{ $acceso->fecha_acceso->format('d/m/Y') }}</p>
                                    <p class="text-sm text-gray-600">{{ substr($acceso->hora_acceso, 0, 5) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Botón de Check-in -->
        <div class="flex gap-4">
            <form id="formCheckIn" action="{{ route('checkin-acceso.procesar') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition transform hover:scale-105 flex items-center justify-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Registrar Acceso Adicional (Opcional)
                </button>
            </form>
            
            <a href="{{ route('checkin-acceso.historial') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg transition flex items-center justify-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Ver Historial
            </a>
        </div>

        <!-- Mensaje de Estado -->
        @if(session('success'))
            <div class="mt-6 bg-green-50 border border-green-200 text-green-700 px-4 py-4 rounded-lg relative">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-4 rounded-lg relative">
                <strong class="font-bold">Error</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

<script>
    // Procesar check-in con confirmación
    document.getElementById('formCheckIn').addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Registrando...';
    });
</script>
@endsection
