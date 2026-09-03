<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar matrícula {{ $prematricula->codigo }}
            </h2>
            <a href="{{ route('admin.prematriculas.show', $prematricula) }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Volver al detalle
            </a>
        </div>
    </x-slot>

    @php
        $esPlanNacional = $prematricula->modalidad && $prematricula->modalidad->nombre === 'Plan Nacional';
        $esNocturna     = $prematricula->modalidad && $prematricula->modalidad->nombre === 'Nocturna';
        $esDiurna       = $prematricula->modalidad && $prematricula->modalidad->nombre === 'Diurna';

        $edad = $prematricula->estudiante->fecha_nacimiento
            ? \Carbon\Carbon::parse($prematricula->estudiante->fecha_nacimiento)->age : null;
        $esNocturnaMayor = $esNocturna && $edad !== null && $edad >= 18;

        $esBajoCiclo = $prematricula->nivel && in_array((string) $prematricula->nivel->numero, ['7', '8', '9']);

        $padre = $prematricula->estudiante->familiares->firstWhere('tipo', 'padre');
        $madre = $prematricula->estudiante->familiares->firstWhere('tipo', 'madre');

        // Encargados 2 y 3 (los que no son el principal)
        $extras = $prematricula->tutores->where('id', '!=', $prematricula->tutor_id)->values();
        $enc2 = $extras[0] ?? null;
        $enc3 = $extras[1] ?? null;

        // Documentos por tipo
        $docPorTipo = $prematricula->documentos->keyBy('tipo');
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    <p class="font-semibold mb-2">Por favor corregí los siguientes errores:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.prematriculas.update', $prematricula) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Datos del estudiante --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Datos del estudiante</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre(s) *</label>
                            <input type="text" name="est_nombre" value="{{ old('est_nombre', $prematricula->estudiante->nombre) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apellido(s) *</label>
                            <input type="text" name="est_apellido" value="{{ old('est_apellido', $prematricula->estudiante->apellido) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cédula / pasaporte *</label>
                            <input type="text" name="est_cedula" value="{{ old('est_cedula', $prematricula->estudiante->cedula) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento *</label>
                            <input type="date" name="est_nacimiento" value="{{ old('est_nacimiento', $prematricula->estudiante->fecha_nacimiento->format('Y-m-d')) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                            <select name="est_genero" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Seleccionar</option>
                                <option value="Masculino" {{ $prematricula->estudiante->genero === 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                <option value="Femenino" {{ $prematricula->estudiante->genero === 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                <option value="Prefiero no indicar" {{ $prematricula->estudiante->genero === 'Prefiero no indicar' ? 'selected' : '' }}>Prefiero no indicar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nacionalidad</label>
                            <input type="text" name="est_nacionalidad" value="{{ old('est_nacionalidad', $prematricula->estudiante->nacionalidad) }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        @if (!$esNocturna)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo institucional (MEP)</label>
                                <input type="text" name="est_email_mep" value="{{ old('est_email_mep', $prematricula->estudiante->email_mep) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        @endif

                        {{-- Contacto propio del estudiante (Nocturna mayor de edad) --}}
                        @if ($esNocturnaMayor)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo personal del estudiante *</label>
                                <input type="email" name="est_email_personal" value="{{ old('est_email_personal', $prematricula->estudiante->email_personal) }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono del estudiante *</label>
                                <input type="tel" name="est_telefono" value="{{ old('est_telefono', $prematricula->estudiante->telefono) }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        @endif

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                            <input type="text" name="est_direccion" value="{{ old('est_direccion', $prematricula->estudiante->direccion) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        {{-- Campos según modalidad --}}
                        @if ($esPlanNacional)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de discapacidad</label>
                                <input type="text" name="est_tipo_discapacidad" value="{{ old('est_tipo_discapacidad', $prematricula->estudiante->tipo_discapacidad) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cuenta con boleta de ubicación</label>
                                <select name="est_boleta_ubicacion" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccionar</option>
                                    <option value="Sí" {{ $prematricula->estudiante->boleta_ubicacion === 'Sí' ? 'selected' : '' }}>Sí</option>
                                    <option value="No" {{ $prematricula->estudiante->boleta_ubicacion === 'No' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nivel de funcionamiento</label>
                                <textarea name="est_nivel_funcionamiento" rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('est_nivel_funcionamiento', $prematricula->estudiante->nivel_funcionamiento) }}</textarea>
                            </div>
                        @else
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Adecuación</label>
                                <select name="est_adecuacion" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="No aplica" {{ $prematricula->estudiante->adecuacion === 'No aplica' ? 'selected' : '' }}>No aplica</option>
                                    <option value="Adecuación de acceso" {{ $prematricula->estudiante->adecuacion === 'Adecuación de acceso' ? 'selected' : '' }}>Adecuación de acceso</option>
                                    <option value="Adecuación no significativa" {{ $prematricula->estudiante->adecuacion === 'Adecuación no significativa' ? 'selected' : '' }}>Adecuación no significativa</option>
                                    <option value="Adecuación significativa" {{ $prematricula->estudiante->adecuacion === 'Adecuación significativa' ? 'selected' : '' }}>Adecuación significativa</option>
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Padre y Madre (oculto en Nocturna mayor de edad) --}}
                @if (!$esNocturnaMayor)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Datos del padre y la madre</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Padre --}}
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Padre</h4>
                                <div class="space-y-3">
                                    <input type="text" name="padre_nombre" placeholder="Nombre completo" value="{{ old('padre_nombre', $padre->nombre_completo ?? '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <input type="text" name="padre_cedula" placeholder="Cédula" value="{{ old('padre_cedula', $padre->cedula ?? '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="tel" name="padre_telefono" placeholder="Teléfono" value="{{ old('padre_telefono', $padre->telefono_principal ?? '') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <input type="tel" name="padre_telefono2" placeholder="Tel. secundario" value="{{ old('padre_telefono2', $padre->telefono_secundario ?? '') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <input type="email" name="padre_email" placeholder="Correo" value="{{ old('padre_email', $padre->email ?? '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <input type="text" name="padre_ocupacion" placeholder="Ocupación" value="{{ old('padre_ocupacion', $padre->ocupacion ?? '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <textarea name="padre_direccion" rows="2" placeholder="Dirección"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('padre_direccion', $padre->direccion ?? '') }}</textarea>
                                </div>
                            </div>
                            {{-- Madre --}}
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Madre</h4>
                                <div class="space-y-3">
                                    <input type="text" name="madre_nombre" placeholder="Nombre completo" value="{{ old('madre_nombre', $madre->nombre_completo ?? '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <input type="text" name="madre_cedula" placeholder="Cédula" value="{{ old('madre_cedula', $madre->cedula ?? '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="tel" name="madre_telefono" placeholder="Teléfono" value="{{ old('madre_telefono', $madre->telefono_principal ?? '') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <input type="tel" name="madre_telefono2" placeholder="Tel. secundario" value="{{ old('madre_telefono2', $madre->telefono_secundario ?? '') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <input type="email" name="madre_email" placeholder="Correo" value="{{ old('madre_email', $madre->email ?? '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <input type="text" name="madre_ocupacion" placeholder="Ocupación" value="{{ old('madre_ocupacion', $madre->ocupacion ?? '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <textarea name="madre_direccion" rows="2" placeholder="Dirección"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('madre_direccion', $madre->direccion ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Encargado 1 (principal) --}}
                @if (!$esNocturnaMayor)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Encargado 1 (principal)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                                <input type="text" name="tut_nombre" value="{{ old('tut_nombre', $prematricula->tutor->nombre_completo) }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Relación *</label>
                                <select name="tut_relacion" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="Padre" {{ $prematricula->tutor->relacion === 'Padre' ? 'selected' : '' }}>Padre</option>
                                    <option value="Madre" {{ $prematricula->tutor->relacion === 'Madre' ? 'selected' : '' }}>Madre</option>
                                    <option value="Tutor legal" {{ $prematricula->tutor->relacion === 'Tutor legal' ? 'selected' : '' }}>Tutor legal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula *</label>
                                <input type="text" name="tut_cedula" value="{{ old('tut_cedula', $prematricula->tutor->cedula) }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono principal *</label>
                                <input type="tel" name="tut_telefono" value="{{ old('tut_telefono', $prematricula->tutor->telefono_principal) }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono secundario</label>
                                <input type="tel" name="tut_telefono2" value="{{ old('tut_telefono2', $prematricula->tutor->telefono_secundario) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico *</label>
                                <input type="email" name="tut_email" value="{{ old('tut_email', $prematricula->tutor->email) }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ocupación</label>
                                <input type="text" name="tut_ocupacion" value="{{ old('tut_ocupacion', $prematricula->tutor->ocupacion) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                                <input type="text" name="tut_dir" value="{{ old('tut_dir', $prematricula->tutor->direccion) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Encargado 2 --}}
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Encargado 2 (opcional)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="tut2_nombre" placeholder="Nombre completo" value="{{ old('tut2_nombre', $enc2->nombre_completo ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <select name="tut2_relacion" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Relación</option>
                                <option value="Padre" {{ ($enc2->relacion ?? '') === 'Padre' ? 'selected' : '' }}>Padre</option>
                                <option value="Madre" {{ ($enc2->relacion ?? '') === 'Madre' ? 'selected' : '' }}>Madre</option>
                                <option value="Tutor legal" {{ ($enc2->relacion ?? '') === 'Tutor legal' ? 'selected' : '' }}>Tutor legal</option>
                            </select>
                            <input type="text" name="tut2_cedula" placeholder="Cédula" value="{{ old('tut2_cedula', $enc2->cedula ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="tel" name="tut2_telefono" placeholder="Teléfono" value="{{ old('tut2_telefono', $enc2->telefono_principal ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="tel" name="tut2_telefono2" placeholder="Tel. secundario" value="{{ old('tut2_telefono2', $enc2->telefono_secundario ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="email" name="tut2_email" placeholder="Correo" value="{{ old('tut2_email', $enc2->email ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" name="tut2_ocupacion" placeholder="Ocupación" value="{{ old('tut2_ocupacion', $enc2->ocupacion ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" name="tut2_dir" placeholder="Dirección" value="{{ old('tut2_dir', $enc2->direccion ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Encargado 3 --}}
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Encargado 3 (opcional)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="tut3_nombre" placeholder="Nombre completo" value="{{ old('tut3_nombre', $enc3->nombre_completo ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <select name="tut3_relacion" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Relación</option>
                                <option value="Padre" {{ ($enc3->relacion ?? '') === 'Padre' ? 'selected' : '' }}>Padre</option>
                                <option value="Madre" {{ ($enc3->relacion ?? '') === 'Madre' ? 'selected' : '' }}>Madre</option>
                                <option value="Tutor legal" {{ ($enc3->relacion ?? '') === 'Tutor legal' ? 'selected' : '' }}>Tutor legal</option>
                            </select>
                            <input type="text" name="tut3_cedula" placeholder="Cédula" value="{{ old('tut3_cedula', $enc3->cedula ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="tel" name="tut3_telefono" placeholder="Teléfono" value="{{ old('tut3_telefono', $enc3->telefono_principal ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="tel" name="tut3_telefono2" placeholder="Tel. secundario" value="{{ old('tut3_telefono2', $enc3->telefono_secundario ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="email" name="tut3_email" placeholder="Correo" value="{{ old('tut3_email', $enc3->email ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" name="tut3_ocupacion" placeholder="Ocupación" value="{{ old('tut3_ocupacion', $enc3->ocupacion ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" name="tut3_dir" placeholder="Dirección" value="{{ old('tut3_dir', $enc3->direccion ?? '') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                @endif

                {{-- Nivel y sección/carrera --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">
                        @if ($esNocturna) Nivel y carrera técnica
                        @elseif ($esPlanNacional) Nivel y sección
                        @else Nivel y sección
                        @endif
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nivel *</label>
                            <select name="nivel_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach ($niveles as $nivel)
                                    <option value="{{ $nivel->id }}" {{ $prematricula->nivel_id == $nivel->id ? 'selected' : '' }}>
                                        {{ $nivel->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if ($esNocturna)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Carrera técnica</label>
                                <select name="carrera_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccionar</option>
                                    @foreach ($niveles as $nivel)
                                        @foreach ($nivel->carreras as $carrera)
                                            <option value="{{ $carrera->id }}" {{ $prematricula->carrera_id == $carrera->id ? 'selected' : '' }}>
                                                {{ $carrera->nombre }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sección</label>
                                <select name="seccion_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sin preferencia</option>
                                    @foreach ($niveles as $nivel)
                                        @foreach ($nivel->secciones as $seccion)
                                            <option value="{{ $seccion->id }}" {{ $prematricula->seccion_id == $seccion->id ? 'selected' : '' }}>
                                                {{ $seccion->nombre }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>

                            @if ($esDiurna)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Grupo de taller</label>
                                    <select name="grupo_taller" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">—</option>
                                        <option value="A" {{ $prematricula->grupo_taller === 'A' ? 'selected' : '' }}>Grupo A</option>
                                        <option value="B" {{ $prematricula->grupo_taller === 'B' ? 'selected' : '' }}>Grupo B</option>
                                    </select>
                                </div>
                            @endif
                        @endif

                        {{-- Campos Plan Nacional según ciclo --}}
                        @if ($esPlanNacional)
                            @if ($esBajoCiclo)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Técnica 1</label>
                                    <input type="text" name="tecnica_1" value="{{ old('tecnica_1', $prematricula->tecnica_1) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Técnica 2</label>
                                    <input type="text" name="tecnica_2" value="{{ old('tecnica_2', $prematricula->tecnica_2) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Formación vocacional</label>
                                    <input type="text" name="formacion_vocacional" value="{{ old('formacion_vocacional', $prematricula->formacion_vocacional) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Técnica</label>
                                    <input type="text" name="tecnica_alto" value="{{ old('tecnica_alto', $prematricula->tecnica_3) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Seguimiento</label>
                                    <textarea name="seguimiento_pn" rows="2"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('seguimiento_pn', $prematricula->seguimiento_pn) }}</textarea>
                                </div>
                            @endif
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Centro educativo de procedencia *</label>
                            <input type="text" name="colegio_procedencia" value="{{ old('colegio_procedencia', $prematricula->colegio_procedencia) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Año cursado anteriormente *</label>
                            <input type="text" name="anio_cursado_anterior" value="{{ old('anio_cursado_anterior', $prematricula->anio_cursado_anterior) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                {{-- Documentos --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-1">Documentos</h3>
                    <p class="text-xs text-gray-400 mb-4">Los documentos entregados en físico se mantienen como están. Para los digitales, subí un archivo solo si querés reemplazarlo.</p>
                    <div class="space-y-5">
                        @php
                            $listaDocsEdit = [
                                'doc_cedula'           => ['tipo' => 'cedula_estudiante', 'label' => 'Cédula del estudiante'],
                                'doc_notas'            => ['tipo' => 'notas', 'label' => 'Certificado de notas'],
                                'doc_foto'             => ['tipo' => 'foto', 'label' => 'Fotografía del estudiante'],
                                'doc_cedula_encargado' => ['tipo' => 'cedula_encargado', 'label' => 'Cédula del encargado legal'],
                            ];
                            if ($esPlanNacional) {
                                $listaDocsEdit['doc_pase'] = ['tipo' => 'pase', 'label' => 'PASE'];
                            }
                            $listaDocsEdit['doc_prueba_admision'] = ['tipo' => 'prueba_admision', 'label' => 'Certificado de prueba de admisión'];
                        @endphp

                        @foreach ($listaDocsEdit as $campo => $info)
                            @php $docActual = $docPorTipo[$info['tipo']] ?? null; @endphp
                            <div class="border-b border-gray-100 pb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $info['label'] }}</label>
                                <p class="text-xs mb-2">
                                    @if ($docActual && $docActual->entregado_fisico)
                                        <span class="text-amber-600 font-medium">Entregado en físico</span> — se mantiene sin cambios.
                                    @elseif ($docActual)
                                        <span class="text-green-600 font-medium">Digital:</span> {{ $docActual->nombre_original }}
                                    @else
                                        <span class="text-gray-400">No registrado</span>
                                    @endif
                                </p>
                                @if (!($docActual && $docActual->entregado_fisico))
                                    <input type="file" name="{{ $campo }}" accept=".pdf,.jpg,.jpeg,.png"
                                        class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700">
                                    <p class="text-xs text-gray-400 mt-1">Dejar vacío para mantener el actual.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('admin.prematriculas.show', $prematricula) }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-md text-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="bg-blue-700 hover:bg-blue-800 text-white font-medium px-6 py-2.5 rounded-md text-sm">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>