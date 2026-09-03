<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Matrícula {{ $prematricula->codigo }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.prematriculas.edit', $prematricula) }}"
                    class="text-sm bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md font-medium">
                    ✏️ Editar
                </a>
                <form method="POST" action="{{ route('admin.prematriculas.destroy', $prematricula) }}"
                    onsubmit="return confirm('¿Seguro que querés eliminar esta matrícula? Se borrarán todos los datos y se liberará el cupo.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium">
                        🗑️ Eliminar
                    </button>
                </form>
                <a href="{{ route('admin.prematriculas.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    @php
        // Detección de modalidad (misma lógica que el PDF)
        $esPlanNacional = $prematricula->modalidad && $prematricula->modalidad->nombre === 'Plan Nacional';
        $esNocturna     = $prematricula->modalidad && $prematricula->modalidad->nombre === 'Nocturna';
        $esDiurna       = $prematricula->modalidad && $prematricula->modalidad->nombre === 'Diurna';

        // Edad del estudiante
        $edadEstudiante = $prematricula->estudiante && $prematricula->estudiante->fecha_nacimiento
            ? \Carbon\Carbon::parse($prematricula->estudiante->fecha_nacimiento)->age
            : null;

        // Nocturna + mayor de edad: se oculta padre/madre y encargados, se muestra contacto del estudiante
        $ocultarEncargados = $esNocturna && $edadEstudiante !== null && $edadEstudiante >= 18;

        // Bajo ciclo Plan Nacional (7,8,9)
        $esBajoCiclo = $prematricula->nivel && in_array((string) $prematricula->nivel->numero, ['7', '8', '9']);

        // Padre / madre
        $padre = $prematricula->estudiante->familiares->firstWhere('tipo', 'padre');
        $madre = $prematricula->estudiante->familiares->firstWhere('tipo', 'madre');

        // Encargados
        $listaEncargados = $prematricula->tutores->count() > 0
            ? $prematricula->tutores->sortByDesc(fn($e) => $e->pivot->principal)->values()
            : collect([$prematricula->tutor]);
        $etiquetasEncargados = ['Principal', 'Segundo', 'Tercero'];
    @endphp

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Info general --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Matrícula {{ $prematricula->codigo }}</h3>
                        <p class="text-xs text-gray-400 mt-1">Registrada el {{ $prematricula->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($prematricula->modalidad)
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $prematricula->modalidad->nombre }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Datos del estudiante --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Datos del estudiante</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-400">Nombre completo</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->nombre }} {{ $prematricula->estudiante->apellido }}</dd></div>
                    <div><dt class="text-gray-400">Cédula</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->cedula }}</dd></div>
                    <div><dt class="text-gray-400">Fecha de nacimiento</dt><dd class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($prematricula->estudiante->fecha_nacimiento)->format('d/m/Y') }}{{ $edadEstudiante !== null ? ' (' . $edadEstudiante . ' años)' : '' }}</dd></div>
                    <div><dt class="text-gray-400">Género</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->genero ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400">Nacionalidad</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->nacionalidad ?? '—' }}</dd></div>

                    {{-- Correo MEP: en todas menos Nocturna (igual que el formulario) --}}
                    @if (!$esNocturna)
                        <div><dt class="text-gray-400">Correo institucional (MEP)</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->email_mep ?? '—' }}</dd></div>
                    @endif

                    <div><dt class="text-gray-400">Centro educativo de procedencia</dt><dd class="text-gray-900 font-medium">{{ $prematricula->colegio_procedencia }}</dd></div>
                    <div><dt class="text-gray-400">Año cursado anteriormente</dt><dd class="text-gray-900 font-medium">{{ $prematricula->anio_cursado_anterior }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-400">Dirección</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->direccion }}</dd></div>

                    {{-- Campos según modalidad --}}
                    @if ($esPlanNacional)
                        <div><dt class="text-gray-400">Tipo de discapacidad</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->tipo_discapacidad ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Cuenta con boleta de ubicación</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->boleta_ubicacion ?? '—' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-gray-400">Nivel de funcionamiento</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->nivel_funcionamiento ?? '—' }}</dd></div>
                    @else
                        <div class="md:col-span-2"><dt class="text-gray-400">Adecuación</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->adecuacion ?? 'No aplica' }}</dd></div>
                    @endif
                </dl>
            </div>

            {{-- Contacto propio del estudiante (solo Nocturna + mayor de edad) --}}
            @if ($ocultarEncargados)
                <div class="bg-amber-50 shadow-sm rounded-lg p-6 border border-amber-200">
                    <h3 class="text-base font-semibold text-amber-800 mb-1">Datos de contacto del estudiante (mayor de edad)</h3>
                    <p class="text-xs text-amber-700 mb-4">Al ser mayor de edad, el estudiante es su propio contacto. No se registran padre, madre ni encargados.</p>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-400">Teléfono</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->telefono ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Correo personal</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->email_personal ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Correo institucional (MEP)</dt><dd class="text-gray-900 font-medium">{{ $prematricula->estudiante->email_mep ?? '—' }}</dd></div>
                    </dl>
                </div>
            @endif

            {{-- Datos del padre y la madre (oculto en Nocturna mayor de edad) --}}
            @if (!$ocultarEncargados && ($padre || $madre))
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Datos del padre y la madre</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Padre --}}
                        <div class="border border-gray-100 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Padre</h4>
                            @if ($padre)
                                <dl class="space-y-2 text-sm">
                                    <div><dt class="text-gray-400">Nombre completo</dt><dd class="text-gray-900 font-medium">{{ $padre->nombre_completo ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Cédula</dt><dd class="text-gray-900 font-medium">{{ $padre->cedula ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Teléfono</dt><dd class="text-gray-900 font-medium">{{ $padre->telefono_principal ?? '—' }}</dd></div>
                                    @if ($padre->telefono_secundario)
                                        <div><dt class="text-gray-400">Teléfono secundario</dt><dd class="text-gray-900 font-medium">{{ $padre->telefono_secundario }}</dd></div>
                                    @endif
                                    <div><dt class="text-gray-400">Correo</dt><dd class="text-gray-900 font-medium">{{ $padre->email ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Ocupación</dt><dd class="text-gray-900 font-medium">{{ $padre->ocupacion ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Dirección</dt><dd class="text-gray-900 font-medium">{{ $padre->direccion ?? '—' }}</dd></div>
                                </dl>
                            @else
                                <p class="text-sm text-gray-400">No se registraron datos del padre.</p>
                            @endif
                        </div>
                        {{-- Madre --}}
                        <div class="border border-gray-100 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Madre</h4>
                            @if ($madre)
                                <dl class="space-y-2 text-sm">
                                    <div><dt class="text-gray-400">Nombre completo</dt><dd class="text-gray-900 font-medium">{{ $madre->nombre_completo ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Cédula</dt><dd class="text-gray-900 font-medium">{{ $madre->cedula ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Teléfono</dt><dd class="text-gray-900 font-medium">{{ $madre->telefono_principal ?? '—' }}</dd></div>
                                    @if ($madre->telefono_secundario)
                                        <div><dt class="text-gray-400">Teléfono secundario</dt><dd class="text-gray-900 font-medium">{{ $madre->telefono_secundario }}</dd></div>
                                    @endif
                                    <div><dt class="text-gray-400">Correo</dt><dd class="text-gray-900 font-medium">{{ $madre->email ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Ocupación</dt><dd class="text-gray-900 font-medium">{{ $madre->ocupacion ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Dirección</dt><dd class="text-gray-900 font-medium">{{ $madre->direccion ?? '—' }}</dd></div>
                                </dl>
                            @else
                                <p class="text-sm text-gray-400">No se registraron datos de la madre.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Encargados legales (oculto en Nocturna mayor de edad) --}}
            @if (!$ocultarEncargados)
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Encargados legales</h3>
                    <div class="space-y-4">
                        @foreach ($listaEncargados as $i => $encargado)
                            <div class="border border-gray-100 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <h4 class="text-sm font-semibold text-gray-800">Encargado {{ $i + 1 }}</h4>
                                    @if (($etiquetasEncargados[$i] ?? '') === 'Principal')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Principal</span>
                                    @endif
                                </div>
                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <div><dt class="text-gray-400">Nombre completo</dt><dd class="text-gray-900 font-medium">{{ $encargado->nombre_completo }}</dd></div>
                                    <div><dt class="text-gray-400">Relación</dt><dd class="text-gray-900 font-medium">{{ $encargado->relacion ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Cédula</dt><dd class="text-gray-900 font-medium">{{ $encargado->cedula ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Teléfono principal</dt><dd class="text-gray-900 font-medium">{{ $encargado->telefono_principal ?? '—' }}</dd></div>
                                    @if ($encargado->telefono_secundario)
                                        <div><dt class="text-gray-400">Teléfono secundario</dt><dd class="text-gray-900 font-medium">{{ $encargado->telefono_secundario }}</dd></div>
                                    @endif
                                    <div><dt class="text-gray-400">Correo</dt><dd class="text-gray-900 font-medium">{{ $encargado->email ?? '—' }}</dd></div>
                                    <div><dt class="text-gray-400">Ocupación</dt><dd class="text-gray-900 font-medium">{{ $encargado->ocupacion ?? '—' }}</dd></div>
                                    <div class="md:col-span-2"><dt class="text-gray-400">Dirección</dt><dd class="text-gray-900 font-medium">{{ $encargado->direccion ?? '—' }}</dd></div>
                                </dl>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Nivel y sección/carrera (según modalidad) --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">
                    @if ($esNocturna) Nivel y carrera técnica
                    @elseif ($esPlanNacional) Nivel y sección
                    @else Nivel y sección solicitada
                    @endif
                </h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-400">Nivel</dt><dd class="text-gray-900 font-medium">{{ $prematricula->nivel->nombre ?? '—' }}</dd></div>

                    @if ($esNocturna)
                        {{-- Nocturna: carrera técnica --}}
                        <div><dt class="text-gray-400">Carrera técnica</dt><dd class="text-gray-900 font-medium">{{ $prematricula->carrera->nombre ?? '—' }}</dd></div>

                    @elseif ($esPlanNacional)
                        {{-- Plan Nacional: sección + campos según ciclo --}}
                        <div><dt class="text-gray-400">Sección</dt><dd class="text-gray-900 font-medium">{{ $prematricula->seccion->nombre ?? '—' }}</dd></div>
                        @if ($esBajoCiclo)
                            <div><dt class="text-gray-400">Técnica 1</dt><dd class="text-gray-900 font-medium">{{ $prematricula->tecnica_1 ?? '—' }}</dd></div>
                            <div><dt class="text-gray-400">Técnica 2</dt><dd class="text-gray-900 font-medium">{{ $prematricula->tecnica_2 ?? '—' }}</dd></div>
                        @else
                            <div><dt class="text-gray-400">Formación vocacional</dt><dd class="text-gray-900 font-medium">{{ $prematricula->formacion_vocacional ?? '—' }}</dd></div>
                            <div><dt class="text-gray-400">Técnica</dt><dd class="text-gray-900 font-medium">{{ $prematricula->tecnica_3 ?? '—' }}</dd></div>
                            <div class="md:col-span-2"><dt class="text-gray-400">Seguimiento</dt><dd class="text-gray-900 font-medium">{{ $prematricula->seguimiento_pn ?? '—' }}</dd></div>
                        @endif

                    @else
                        {{-- Diurna: sección + grupo de taller --}}
                        <div><dt class="text-gray-400">Sección preferida</dt><dd class="text-gray-900 font-medium">{{ $prematricula->seccion->nombre ?? 'Sin preferencia' }}</dd></div>
                        <div><dt class="text-gray-400">Grupo de taller</dt><dd class="text-gray-900 font-medium">{{ $prematricula->grupo_taller ? 'Grupo ' . $prematricula->grupo_taller : '—' }}</dd></div>
                    @endif

                    @if ($prematricula->periodo)
                        <div><dt class="text-gray-400">Período</dt><dd class="text-gray-900 font-medium">{{ $prematricula->periodo->nombre }}</dd></div>
                    @endif
                </dl>
            </div>

            {{-- Documentos --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Documentos adjuntos</h3>
                <ul class="space-y-2">
                    @forelse ($prematricula->documentos as $doc)
                        <li class="text-sm text-gray-600 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            {{ $doc->nombre_original ?? 'Entregado en físico' }}
                            <span class="text-xs text-gray-400">({{ str_replace('_', ' ', $doc->tipo) }}{{ $doc->entregado_fisico ? ' — físico' : '' }})</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400">No hay documentos registrados.</li>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>
</x-app-layout>