<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ratificar matrículas
            </h2>
            <a href="{{ route('prematricula.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Volver a mis matrículas
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700">
                📅 Estudiantes que matriculaste en <strong>{{ $periodoAnterior->nombre }}</strong> y todavía no tienen
                matrícula en <strong>{{ $periodoActivo->nombre }}</strong>. Ratificá para reutilizar sus datos y solo
                confirmar/actualizar la sección o especialidad de este año.
            </div>

            @if ($candidatos->isEmpty())
                <div class="bg-white shadow-sm rounded-lg p-8 border border-gray-200 text-center">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">No hay estudiantes pendientes de ratificar</h3>
                    <p class="text-sm text-gray-500">Ya ratificaste a todos tus estudiantes del período anterior, o no tenías ninguno.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($candidatos as $prematriculaAnterior)
                        <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-200 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $prematriculaAnterior->estudiante->nombre }} {{ $prematriculaAnterior->estudiante->apellido }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Cédula {{ $prematriculaAnterior->estudiante->cedula }}
                                    — {{ $prematriculaAnterior->nivel->nombre ?? 'Sin nivel' }}
                                    ({{ $prematriculaAnterior->modalidad->nombre ?? '—' }})
                                </p>
                            </div>
                            <a href="{{ route('prematricula.ratificar.form', $prematriculaAnterior->estudiante) }}"
                                class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-md text-sm">
                                Ratificar
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
