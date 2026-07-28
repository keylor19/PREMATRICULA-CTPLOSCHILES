<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mis prematrículas
            </h2>
            @if ($periodo && $periodo->estaAbierto())
                <a href="{{ route('prematricula.create') }}"
                    class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-medium px-4 py-2 rounded-md text-sm">
                    + Nueva prematrícula
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

            @if (session('info'))
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                    {{ session('info') }}
                </div>
            @endif

            @if (!$periodo || !$periodo->estaAbierto())
                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700">
                    🔒 El período de prematrícula está cerrado actualmente.
                </div>
            @endif

            @if ($prematriculas->isEmpty())
                <div class="bg-white shadow-sm rounded-lg p-8 border border-gray-200 text-center">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">No hay prematrículas registradas</h3>
                    <p class="text-sm text-gray-500 mb-6">Completá el formulario para registrar una prematrícula.</p>
                    @if ($periodo && $periodo->estaAbierto())
                        <a href="{{ route('prematricula.create') }}"
                            class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-medium px-6 py-2.5 rounded-md text-sm">
                            Iniciar prematrícula
                        </a>
                    @endif
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($prematriculas as $prematricula)
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-xs text-gray-400">Código de referencia</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $prematricula->codigo }}</p>
                                    @if ($prematricula->modalidad)
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100 mt-1">
                                            {{ $prematricula->modalidad->nombre }}
                                        </span>
                                    @endif
                                </div>

                                <a href="{{ route('prematricula.pdf', $prematricula) }}"
                                class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium mt-2">
                                📄 Descargar PDF
                                </a>
                                <form method="POST" action="{{ route('prematricula.reenviarCorreo', $prematricula) }}" class="inline-block mt-2 ml-3">
    @csrf
    <button type="submit" class="inline-flex items-center gap-1 text-xs text-green-600 hover:text-green-800 font-medium">
        ✉️ Reenviar correo
    </button>
</form>

                                @if ($prematricula->estado === 'pendiente')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
                                        Pendiente de revisión
                                    </span>
                                @elseif ($prematricula->estado === 'aprobada')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                        Aprobada
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                        Rechazada
                                    </span>
                                @endif
                            </div>

                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <dt class="text-gray-400">Estudiante</dt>
                                    <dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->nombre }} {{ $prematricula->estudiante->apellido }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">Nivel</dt>
                                    <dd class="text-gray-900 font-medium">{{ $prematricula->nivel->nombre ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">Sección</dt>
                                    <dd class="text-gray-900 font-medium">{{ $prematricula->seccion->nombre ?? 'Sin preferencia' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">Grupo de taller</dt>
                                    <dd class="text-gray-900 font-medium">{{ $prematricula->grupo_taller ? 'Grupo ' . $prematricula->grupo_taller : '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">Período</dt>
                                    <dd class="text-gray-900 font-medium">{{ $prematricula->periodo->nombre ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">Fecha</dt>
                                    <dd class="text-gray-900 font-medium">{{ $prematricula->created_at->format('d/m/Y') }}</dd>
                                </div>
                            </dl>

                            @if ($prematricula->nota_admin)
                                <div class="mt-4 p-3 bg-gray-50 rounded-md border border-gray-200">
                                    <p class="text-xs text-gray-400 mb-1">Nota de la institución</p>
                                    <p class="text-sm text-gray-700">{{ $prematricula->nota_admin }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>