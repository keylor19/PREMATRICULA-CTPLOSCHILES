<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard — Prematrícula en tiempo real
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros --}}
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Período</label>
                        <select name="periodo_id" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">Seleccionar período</option>
                            @foreach ($periodos as $p)
                                <option value="{{ $p->id }}" {{ $periodo?->id == $p->id ? 'selected' : '' }}>
                                    {{ $p->nombre }} {{ $p->activo ? '✓' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Modalidad</label>
                        <select name="modalidad_id" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">Todas las modalidades</option>
                            @foreach ($modalidades as $m)
                                <option value="{{ $m->id }}" {{ $modalidadSeleccionada?->id == $m->id ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium px-4 py-2 rounded-md">
                        Actualizar
                    </button>
                    @if (request('periodo_id') || request('modalidad_id'))
                        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>

            @if ($periodo)
                <div class="p-3 {{ $periodoActivo?->id == $periodo->id ? 'bg-green-50 border-green-200 text-green-700' : 'bg-gray-50 border-gray-200 text-gray-600' }} border rounded-lg text-sm">
                    📅 <strong>{{ $periodo->nombre }}</strong>
                    — {{ $periodo->fecha_inicio->format('d/m/Y') }} al {{ $periodo->fecha_fin->format('d/m/Y') }}
                    @if ($periodoActivo?->id == $periodo->id && $periodo->estaAbierto())
                        — <span class="font-medium text-green-700">Abierto</span>
                    @elseif ($periodoActivo?->id == $periodo->id)
                        — <span class="font-medium text-yellow-700">Activo pero fuera de fechas</span>
                    @else
                        — <span class="font-medium text-gray-500">Cerrado</span>
                    @endif
                    @if ($modalidadSeleccionada)
                        · Modalidad: <strong>{{ $modalidadSeleccionada->nombre }}</strong>
                    @endif
                </div>

                {{-- Tarjetas de resumen --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-200 text-center">
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-400 mt-1 uppercase">Total registradas</p>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-5 border border-yellow-200 text-center bg-yellow-50">
                        <p class="text-3xl font-bold text-yellow-700">{{ $stats['pendiente'] }}</p>
                        <p class="text-xs text-yellow-500 mt-1 uppercase">Pendientes</p>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-5 border border-green-200 text-center bg-green-50">
                        <p class="text-3xl font-bold text-green-700">{{ $stats['aprobada'] }}</p>
                        <p class="text-xs text-green-500 mt-1 uppercase">Aprobadas</p>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-5 border border-red-200 text-center bg-red-50">
                        <p class="text-3xl font-bold text-red-700">{{ $stats['rechazada'] }}</p>
                        <p class="text-xs text-red-500 mt-1 uppercase">Rechazadas</p>
                    </div>
                </div>

                {{-- Bloques por modalidad --}}
                @forelse ($bloques as $bloque)
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-semibold text-gray-900">{{ $bloque['modalidad'] }}</h3>
                            <span class="text-xs text-gray-400">({{ $bloque['total'] }} registradas)</span>
                        </div>

                        @if ($bloque['esDiurna'])
                            @if (count($bloque['porSeccion']) > 0)
                                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nivel</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sección</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-blue-600 uppercase">Grupo A</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-blue-600 uppercase">Taller A</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-blue-600 uppercase">Cupo A</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-purple-600 uppercase">Grupo B</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-purple-600 uppercase">Taller B</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-purple-600 uppercase">Cupo B</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($bloque['porSeccion'] as $fila)
                                                @php
                                                    $llenaA = $fila['cupo_a'] > 0 && $fila['matricula_a'] >= $fila['cupo_a'];
                                                    $llenaB = $fila['cupo_b'] > 0 && $fila['matricula_b'] >= $fila['cupo_b'];
                                                    $pctA = $fila['cupo_a'] > 0 ? round(($fila['matricula_a'] / $fila['cupo_a']) * 100) : 0;
                                                    $pctB = $fila['cupo_b'] > 0 ? round(($fila['matricula_b'] / $fila['cupo_b']) * 100) : 0;
                                                @endphp
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $fila['nivel'] }}</td>
                                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $fila['seccion'] }}</td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="text-sm font-bold {{ $llenaA ? 'text-red-600' : 'text-blue-700' }}">{{ $fila['matricula_a'] }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $fila['taller_a'] }}</td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="text-xs {{ $llenaA ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                                            {{ $fila['matricula_a'] }}/{{ $fila['cupo_a'] }} @if ($llenaA) 🔴 @endif
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="text-sm font-bold {{ $llenaB ? 'text-red-600' : 'text-purple-700' }}">{{ $fila['matricula_b'] }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $fila['taller_b'] }}</td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="text-xs {{ $llenaB ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                                            {{ $fila['matricula_b'] }}/{{ $fila['cupo_b'] }} @if ($llenaB) 🔴 @endif
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="text-sm font-bold text-gray-900">{{ $fila['total'] }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            @if (count($bloque['porCarrera']) > 0)
                                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nivel</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Carrera técnica</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Matriculados</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Capacidad</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Disponibles</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($bloque['porCarrera'] as $fila)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $fila['nivel'] }}</td>
                                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $fila['carrera'] }}</td>
                                                    <td class="px-4 py-3 text-center text-sm font-bold {{ $fila['llena'] ? 'text-red-600' : 'text-blue-700' }}">
                                                        {{ $fila['matriculados'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $fila['capacidad'] }}</td>
                                                    <td class="px-4 py-3 text-center text-sm {{ $fila['disponibles'] === 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                                                        {{ $fila['disponibles'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @if ($fila['llena'])
                                                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">🔴 Llena</span>
                                                        @elseif ($fila['porcentaje'] > 75)
                                                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">⚠️ Casi llena</span>
                                                        @else
                                                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">✓ Disponible</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif
                    </div>
                @empty
                    <div class="bg-white shadow-sm rounded-lg p-8 border border-gray-200 text-center">
                        <p class="text-sm text-gray-400">No hay prematrículas registradas para este período todavía.</p>
                    </div>
                @endforelse

                <div class="text-center">
                    <p class="text-xs text-gray-400">
                        La página se actualiza automáticamente cada 60 segundos.
                        <button onclick="location.reload()" class="text-blue-600 hover:underline ml-1">Actualizar ahora</button>
                    </p>
                </div>

            @else
                <div class="bg-white shadow-sm rounded-lg p-8 border border-gray-200 text-center">
                    <p class="text-sm text-gray-400">Seleccioná un período para ver las estadísticas.</p>
                </div>
            @endif

        </div>
    </div>

    <script>
        setTimeout(function() {
            location.reload();
        }, 60000);
    </script>
</x-app-layout>