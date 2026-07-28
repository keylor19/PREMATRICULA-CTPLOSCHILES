<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Solicitud {{ $prematricula->codigo }}
            </h2>
            <a href="{{ route('admin.prematriculas.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Estado actual --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Estado de la solicitud</h3>
                    @if ($prematricula->estado === 'pendiente')
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">Pendiente de revisión</span>
                    @elseif ($prematricula->estado === 'aprobada')
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">Aprobada</span>
                    @else
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">Rechazada</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-1">Solicitada el {{ $prematricula->created_at->format('d/m/Y H:i') }}</p>
            </div>

            {{-- Datos del estudiante --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Datos del estudiante</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-400">Nombre completo</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->nombre }} {{ $prematricula->estudiante->apellido }}</dd></div>
                    <div><dt class="text-gray-400">Cédula</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->cedula }}</dd></div>
                    <div><dt class="text-gray-400">Fecha de nacimiento</dt><dd class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($prematricula->estudiante->fecha_nacimiento)->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-400">Género</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->genero ?? '—' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-400">Dirección</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->direccion }}</dd></div>
                    @if ($prematricula->estudiante->condicion_salud)
                        <div class="md:col-span-2"><dt class="text-gray-400">Condición de salud / NEE</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->condicion_salud }}</dd></div>
                    @endif
                </dl>
            </div>

            {{-- Datos del tutor --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Datos del encargado</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-400">Nombre completo</dt><dd class="text-gray-900 font-medium">{{ $prematricula->tutor->nombre_completo }}</dd></div>
                    <div><dt class="text-gray-400">Relación</dt><dd class="text-gray-900 font-medium">{{ $prematricula->tutor->relacion }}</dd></div>
                    <div><dt class="text-gray-400">Teléfono</dt><dd class="text-gray-900 font-medium">{{ $prematricula->tutor->telefono_principal }}</dd></div>
                    <div><dt class="text-gray-400">Correo</dt><dd class="text-gray-900 font-medium">{{ $prematricula->tutor->email }}</dd></div>
                </dl>
            </div>

            {{-- Nivel solicitado --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Nivel solicitado</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-400">Nivel</dt><dd class="text-gray-900 font-medium">{{ $prematricula->nivel_solicitado }}.º año</dd></div>
                    <div><dt class="text-gray-400">Sección preferida</dt><dd class="text-gray-900 font-medium">{{ $prematricula->seccion_preferida ?? 'Sin preferencia' }}</dd></div>
                    <div><dt class="text-gray-400">Colegio de procedencia</dt><dd class="text-gray-900 font-medium">{{ $prematricula->colegio_procedencia }}</dd></div>
                    <div><dt class="text-gray-400">Año cursado anteriormente</dt><dd class="text-gray-900 font-medium">{{ $prematricula->anio_cursado_anterior }}</dd></div>
                </dl>
            </div>

            {{-- Documentos --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Documentos adjuntos</h3>
                <ul class="space-y-2">
                    @foreach ($prematricula->documentos as $doc)
                        <li class="text-sm text-gray-600">📎 {{ $doc->nombre_original }} <span class="text-xs text-gray-400">({{ $doc->tipo }})</span></li>
                    @endforeach
                </ul>
            </div>

            {{-- Decisión --}}
            @if ($prematricula->estado === 'pendiente')
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Tomar decisión</h3>
                    <form method="POST" action="{{ route('admin.prematriculas.decidir', $prematricula) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nota para el encargado (opcional)</label>
                            <textarea name="nota_admin" rows="2"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" name="estado" value="aprobada"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-5 py-2 rounded-md">
                                ✓ Aprobar
                            </button>
                            <button type="submit" name="estado" value="rechazada"
                                class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-5 py-2 rounded-md">
                                ✕ Rechazar
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Decisión registrada</h3>
                    <p class="text-sm text-gray-500">Fecha: {{ $prematricula->fecha_decision?->format('d/m/Y H:i') }}</p>
                    @if ($prematricula->nota_admin)
                        <p class="text-sm text-gray-700 mt-2">{{ $prematricula->nota_admin }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>