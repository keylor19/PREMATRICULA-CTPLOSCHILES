<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configuración de niveles y secciones
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Selector de modalidad --}}
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
                <div class="flex gap-3 flex-wrap">
                    @foreach ($modalidades as $m)
                        <a href="{{ route('admin.configuracion.index', ['modalidad_id' => $m->id]) }}"
                            class="px-4 py-2 rounded-md text-sm font-medium border transition-colors
                            {{ $modalidadActual?->id == $m->id
                                ? 'bg-blue-700 text-white border-blue-700'
                                : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400' }}">
                            {{ $m->nombre }}
                        </a>
                    @endforeach
                </div>
            </div>

            @if ($modalidadActual)

                {{-- Formulario agregar nivel --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">
                        Agregar nivel — {{ $modalidadActual->nombre }}
                    </h3>
                    <form method="POST" action="{{ route('admin.configuracion.nivel') }}">
                        @csrf
                        <input type="hidden" name="modalidad_id" value="{{ $modalidadActual->id }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    @if ($modalidadActual->nombre === 'Diurna')
                                        Número de nivel (7 o 10) *
                                    @else
                                        Nombre corto del nivel *
                                    @endif
                                </label>
                                @if ($modalidadActual->nombre === 'Diurna')
                                    <select name="numero" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Seleccionar</option>
                                        <option value="7">7° año</option>
                                        <option value="10">10° año</option>
                                    </select>
                               @else
                             <input type="text" name="numero" placeholder="ej. I, II, III" required autocomplete="off"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
@endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo del nivel *</label>
                                <input type="text" name="nombre"
                                placeholder="{{ $modalidadActual->nombre === 'Diurna' ? 'ej. Sétimo año' : 'ej. Nivel I' }}"
                                required autocomplete="off"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            @if ($modalidadActual->nombre === 'Diurna')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sección inicio *</label>
                                    <input type="number" name="seccion_inicio" min="1" value="1" required
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="text-xs text-gray-400 mt-1">ej. 1 → primera sección será 7-1</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sección fin *</label>
                                    <input type="number" name="seccion_fin" min="1" value="1" required
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="text-xs text-gray-400 mt-1">ej. 7 → última sección será 7-7</p>
                                </div>
                            @endif
                        </div>

                        @if ($modalidadActual->nombre === 'Diurna')
                            <div class="bg-yellow-50 border border-yellow-100 rounded-md p-3 text-xs text-yellow-700 mb-4">
                                ⚠️ Al guardar se regeneran las secciones. Los talleres asignados se perderán.
                            </div>
                        @endif

                        <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium px-5 py-2 rounded-md">
                            Guardar nivel
                        </button>
                    </form>
                </div>

                {{-- Niveles configurados --}}
                @forelse ($niveles as $nivel)
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ $nivel->nombre }}</h3>
                                <p class="text-xs text-gray-400">{{ $modalidadActual->nombre }}</p>
                            </div>
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium {{ $nivel->activo ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-500' }}">
                                {{ $nivel->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>

                        @if ($modalidadActual->nombre === 'Diurna')
                            {{-- Vista Diurna: secciones con talleres A y B --}}
                            <div class="divide-y divide-gray-100">
                                @foreach ($nivel->secciones as $seccion)
                                    <div class="p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="font-semibold text-gray-900 text-sm">{{ $seccion->nombre }}</span>
                                            <form method="POST" action="{{ route('admin.configuracion.seccion.toggle', $seccion) }}">
                                                @csrf
                                                <button type="submit" class="text-xs {{ $seccion->activa ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }}">
                                                    {{ $seccion->activa ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @foreach (['A' => 'blue', 'B' => 'purple'] as $grupo => $color)
                                                @php $taller = $seccion->talleres->where('grupo', $grupo)->first(); @endphp
                                                <div class="border border-{{ $color }}-100 rounded-md p-3 bg-{{ $color }}-50">
                                                    <p class="text-xs font-semibold text-{{ $color }}-700 mb-2">Grupo {{ $grupo }}</p>
                                                    @if ($taller)
                                                        <div class="space-y-2">
                                                            <div class="flex items-start justify-between">
                                                                <div>
                                                                    <p class="text-sm font-medium text-gray-900">{{ $taller->nombre }}</p>
                                                                    <p class="text-xs text-gray-500 mt-0.5">
                                                                        {{ $taller->cuposOcupados() }} / {{ $taller->capacidad }} cupos ocupados
                                                                        @if ($taller->estaLleno())
                                                                            <span class="text-red-600 font-medium">— LLENO</span>
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                                <form method="POST" action="{{ route('admin.configuracion.seccion.taller.eliminar', $taller) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 ml-2">✕</button>
                                                                </form>
                                                            </div>
                                                            <form method="POST" action="{{ route('admin.configuracion.seccion.taller.capacidad', $taller) }}" class="flex gap-2 items-center">
                                                                @csrf
                                                                @method('PATCH')
                                                                <label class="text-xs text-gray-500">Cupos:</label>
                                                                <input type="number" name="capacidad" value="{{ $taller->capacidad }}" min="1" max="100"
                                                                    class="w-16 rounded border-gray-300 shadow-sm text-xs">
                                                                <button type="submit" class="text-xs bg-{{ $color }}-600 hover:bg-{{ $color }}-700 text-white px-2 py-1 rounded">
                                                                    Guardar
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <form method="POST" action="{{ route('admin.configuracion.seccion.taller') }}" class="space-y-2">
                                                            @csrf
                                                            <input type="hidden" name="seccion_id" value="{{ $seccion->id }}">
                                                            <input type="hidden" name="grupo" value="{{ $grupo }}">
                                                            <input type="text" name="nombre" placeholder="Nombre del taller" required
                                                                class="w-full rounded-md border-gray-300 shadow-sm text-xs">
                                                            <div class="flex gap-2 items-center">
                                                                <label class="text-xs text-gray-500 whitespace-nowrap">Cupos:</label>
                                                                <input type="number" name="capacidad" value="15" min="1" max="100" required
                                                                    class="w-16 rounded-md border-gray-300 shadow-sm text-xs">
                                                                <button type="submit" class="flex-1 bg-{{ $color }}-600 hover:bg-{{ $color }}-700 text-white text-xs px-3 py-1.5 rounded-md">
                                                                    Agregar
                                                                </button>
                                                            </div>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        @elseif ($modalidadActual->nombre === 'Plan Nacional')
                         {{-- Vista Plan Nacional: sin configuración adicional, es texto libre --}}
                            <div class="p-4">
                             <div class="bg-blue-50 border border-blue-100 rounded-md p-3 text-xs text-blue-700">
                       ℹ️ En Plan Nacional el docente escribe libremente la información de técnicas o formación vocacional al momento de matricular. No requiere configuración adicional en este nivel.
                        </div>
                     </div>
                    @else
            {{-- Vista Nocturna: carreras técnicas --}}
             <div class="p-4">
             <p class="text-xs font-medium text-gray-500 uppercase mb-3">Carreras técnicas</p>

                                @if ($nivel->carreras->count() > 0)
                                    <div class="space-y-2 mb-4">
                                        @foreach ($nivel->carreras as $carrera)
                                            <div class="flex items-center justify-between p-3 rounded-md border {{ $carrera->activa ? 'bg-blue-50 border-blue-100' : 'bg-gray-50 border-gray-200' }}">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">{{ $carrera->nombre }}</p>
                                                    <p class="text-xs text-gray-500 mt-0.5">
                                                        {{ $carrera->cuposOcupados() }} / {{ $carrera->capacidad }} cupos ocupados
                                                        @if ($carrera->estaLlena())
                                                            <span class="text-red-600 font-medium">— LLENA</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    {{-- Editar capacidad --}}
                                                    <form method="POST" action="{{ route('admin.configuracion.carrera.capacidad', $carrera) }}" class="flex gap-2 items-center">
                                                        @csrf
                                                        @method('PATCH')
                                                        <label class="text-xs text-gray-500">Cupos:</label>
                                                        <input type="number" name="capacidad" value="{{ $carrera->capacidad }}" min="1" max="500"
                                                            class="w-16 rounded border-gray-300 shadow-sm text-xs">
                                                        <button type="submit" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded">
                                                            Guardar
                                                        </button>
                                                    </form>

                                                    {{-- Toggle activa --}}
                                                    <form method="POST" action="{{ route('admin.configuracion.carrera.toggle', $carrera) }}">
                                                        @csrf
                                                        <button type="submit" class="text-xs {{ $carrera->activa ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }}">
                                                            {{ $carrera->activa ? 'Desactivar' : 'Activar' }}
                                                        </button>
                                                    </form>

                                                    {{-- Eliminar --}}
                                                    <form method="POST" action="{{ route('admin.configuracion.carrera.eliminar', $carrera) }}"
                                                        onsubmit="return confirm('¿Eliminar esta carrera?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">✕</button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400 mb-4">No hay carreras configuradas para este nivel.</p>
                                @endif

                                {{-- Agregar carrera --}}
                                <form method="POST" action="{{ route('admin.configuracion.carrera') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                                    @csrf
                                    <input type="hidden" name="nivel_id" value="{{ $nivel->id }}">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Nombre de la carrera *</label>
                                        <input type="text" name="nombre" placeholder="ej. Contabilidad, Aduanas..." required
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Cupos *</label>
                                        <input type="number" name="capacidad" value="20" min="1" max="500" required
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium px-4 py-2 rounded-md">
                                            Agregar carrera
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white shadow-sm rounded-lg p-8 border border-gray-200 text-center">
                        <p class="text-sm text-gray-400">No hay niveles configurados para {{ $modalidadActual->nombre }}. Usá el formulario de arriba para agregar el primero.</p>
                    </div>
                @endforelse

            @endif

        </div>
    </div>
</x-app-layout>