<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Solicitudes de prematrícula
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Exportar a Excel --}}
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200 mb-4">
                <form method="GET" action="{{ route('admin.prematriculas.exportar') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Período</label>
                        <select name="periodo_id" required class="rounded-md border-gray-300 shadow-sm text-sm focus:border-green-500 focus:ring-green-500">
                            <option value="">Seleccionar período</option>
                            @foreach (\App\Models\Periodo::latest()->get() as $periodo)
                                <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Modalidad</label>
                        <select name="modalidad_id" required class="rounded-md border-gray-300 shadow-sm text-sm focus:border-green-500 focus:ring-green-500">
                            <option value="">Seleccionar modalidad</option>
                            @foreach ($modalidades as $modalidad)
                                <option value="{{ $modalidad->id }}">{{ $modalidad->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-md flex items-center gap-2">
                        📥 Exportar a Excel
                    </button>
                </form>
            </div>

            {{-- Filtros --}}
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200 mb-4">
                <form method="GET" action="{{ route('admin.prematriculas.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Buscar estudiante</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, apellido o cédula"
                            class="rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Modalidad</label>
                        <select name="modalidad_id" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todas</option>
                            @foreach ($modalidades as $modalidad)
                                <option value="{{ $modalidad->id }}" {{ request('modalidad_id') == $modalidad->id ? 'selected' : '' }}>
                                    {{ $modalidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                        <select name="estado" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="aprobada" {{ request('estado') === 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                            <option value="rechazada" {{ request('estado') === 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-md">
                        Filtrar
                    </button>
                    @if (request('buscar') || request('estado') || request('modalidad_id'))
                        <a href="{{ route('admin.prematriculas.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-2 py-2">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>

            {{-- Tabla --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estudiante</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modalidad</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nivel</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sección</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grupo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Encargado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($solicitudes as $s)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $s->codigo }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $s->estudiante->nombre }} {{ $s->estudiante->apellido }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if ($s->modalidad)
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $s->modalidad->nombre }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $s->nivel->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $s->seccion->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $s->grupo_taller ? 'Grupo '.$s->grupo_taller : '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $s->tutor->nombre_completo }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($s->estado === 'pendiente')
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">Pendiente</span>
                                    @elseif ($s->estado === 'aprobada')
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">Aprobada</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">Rechazada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $s->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <a href="{{ route('admin.prematriculas.show', $s) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                        Ver →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-400">
                                    No hay solicitudes registradas todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $solicitudes->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>