<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Períodos de prematrícula
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Período activo --}}
            @if ($periodoActivo)
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-green-800">✓ Período activo: {{ $periodoActivo->nombre }}</p>
                            <p class="text-xs text-green-600 mt-1">
                                {{ $periodoActivo->fecha_inicio->format('d/m/Y') }} al {{ $periodoActivo->fecha_fin->format('d/m/Y') }}
                                —
                                @if ($periodoActivo->estaAbierto())
                                    <span class="font-medium">Abierto para prematrículas</span>
                                @else
                                    <span class="font-medium text-yellow-700">Fuera de fechas (cerrado)</span>
                                @endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.periodos.cerrar', $periodoActivo) }}">
                            @csrf
                            <button type="submit" class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1.5 rounded-md font-medium">
                                Cerrar período
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700">
                    ⚠️ No hay ningún período activo. Los docentes no podrán registrar prematrículas hasta que actives uno.
                </div>
            @endif

            {{-- Crear período --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Crear nuevo período</h3>
                <form method="POST" action="{{ route('admin.periodos.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Año lectivo *</label>
                            <input type="number" name="anio" value="{{ now()->year + 1 }}" min="2024" max="2100" required
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del período *</label>
                            <input type="text" name="nombre" value="Prematrícula {{ now()->year + 1 }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de apertura *</label>
                            <input type="date" name="fecha_inicio" required
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de cierre *</label>
                            <input type="date" name="fecha_fin" required
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium px-5 py-2 rounded-md">
                        Crear período
                    </button>
                </form>
            </div>

            {{-- Lista de períodos --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Historial de períodos</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Año</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Apertura</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cierre</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prematrículas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($periodos as $periodo)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $periodo->nombre }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $periodo->anio }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $periodo->fecha_inicio->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $periodo->fecha_fin->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $periodo->prematriculas()->count() }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($periodo->activo)
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">Activo</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Cerrado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <div class="flex justify-end gap-3">
                                        @if (!$periodo->activo)
                                            <form method="POST" action="{{ route('admin.periodos.activar', $periodo) }}">
                                                @csrf
                                                <button type="submit" class="text-xs text-green-600 hover:text-green-800 font-medium">
                                                    Activar
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.periodos.destroy', $periodo) }}"
                                                onsubmit="return confirm('¿Seguro? Solo se puede eliminar si no tiene prematrículas.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                                    No hay períodos creados todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>