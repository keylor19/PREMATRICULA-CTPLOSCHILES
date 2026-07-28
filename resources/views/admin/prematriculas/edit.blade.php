<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar prematrícula {{ $prematricula->codigo }}
            </h2>
            <a href="{{ route('admin.prematriculas.show', $prematricula) }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Volver al detalle
            </a>
        </div>
    </x-slot>

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

            <form method="POST" action="{{ route('admin.prematriculas.update', $prematricula) }}" class="space-y-6">
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
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                            <input type="text" name="est_direccion" value="{{ old('est_direccion', $prematricula->estudiante->direccion) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Condición de salud / NEE</label>
                            <textarea name="est_salud" rows="2"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('est_salud', $prematricula->estudiante->condicion_salud) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Datos del tutor --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Datos del padre / madre / encargado</h3>
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
                                <option value="Otro" {{ $prematricula->tutor->relacion === 'Otro' ? 'selected' : '' }}>Otro</option>
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

                {{-- Nivel y sección --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Nivel y sección</h3>
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sección preferida</label>
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Grupo de taller *</label>
                            <select name="grupo_taller" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="A" {{ $prematricula->grupo_taller === 'A' ? 'selected' : '' }}>Grupo A</option>
                                <option value="B" {{ $prematricula->grupo_taller === 'B' ? 'selected' : '' }}>Grupo B</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Colegio de procedencia *</label>
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