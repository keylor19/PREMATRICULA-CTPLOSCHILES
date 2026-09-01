<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Seleccionar la modalidad de matricula:
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700">
                📅 Período activo: <strong>{{ $periodo->nombre }}</strong>
                — Cierra el {{ $periodo->fecha_fin->format('d/m/Y') }}
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-2">¿En qué modalidad vas a registrar la Matrícula?</h3>
                <p class="text-sm text-gray-500 mb-6">Seleccioná la modalidad correspondiente al estudiante.</p>

                <div class="space-y-3">
                    @foreach ($modalidades as $modalidad)
                        <a href="{{ route('prematricula.create', ['modalidad_id' => $modalidad->id]) }}"
                            class="block p-4 border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $modalidad->nombre }}</p>
                                    @if ($modalidad->descripcion)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $modalidad->descripcion }}</p>
                                    @endif
                                </div>
                                <span class="text-blue-500 text-lg">→</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>