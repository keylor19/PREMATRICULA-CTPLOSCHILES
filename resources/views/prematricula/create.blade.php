<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $periodo->nombre }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            {{-- Aviso del período --}}
            <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700">
                📅 Período activo: <strong>{{ $periodo->nombre }}</strong>
                — Cierra el {{ $periodo->fecha_fin->format('d/m/Y') }}
            </div>

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

            <form method="POST" action="{{ route('prematricula.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">

                {{-- Datos del estudiante --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Datos del estudiante</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre(s) *</label>
                            <input type="text" name="est_nombre" value="{{ old('est_nombre') }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apellido(s) *</label>
                            <input type="text" name="est_apellido" value="{{ old('est_apellido') }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cédula / pasaporte / DIMEX *</label>
                            <input type="text" name="est_cedula" id="est_cedula" value="{{ old('est_cedula') }}" required
                                oninput="generarCorreoMep(this.value)"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        @if ($esNocturna === false)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo institucional (MEP)</label>
                                <input type="text" name="est_email_mep" id="est_email_mep" value="{{ old('est_email_mep') }}"
                                    placeholder="Se genera automáticamente"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-400 mt-1">Se genera con la cédula, pero podés editarlo si es necesario.</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento *</label>
                            <div class="flex items-center gap-3">
                                <input type="date" name="est_nacimiento" id="est_nacimiento" value="{{ old('est_nacimiento') }}" required
                                    oninput="calcularEdad(this.value)"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span id="edad_calculada" class="whitespace-nowrap text-sm font-medium text-gray-500"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                            <select name="est_genero" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Seleccionar</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Prefiero no indicar">Prefiero no indicar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nacionalidad</label>
                            <input type="text" name="est_nacionalidad" value="{{ old('est_nacionalidad') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        {{-- Dirección del ESTUDIANTE --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección de residencia *</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Provincia *</label>
                                    <select name="est_provincia" id="est_provincia" required
                                        onchange="cargarCantones('est', this.value)"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Seleccionar provincia</option>
                                        <option value="Alajuela">Alajuela</option>
                                        <option value="San José">San José</option>
                                        <option value="Cartago">Cartago</option>
                                        <option value="Heredia">Heredia</option>
                                        <option value="Guanacaste">Guanacaste</option>
                                        <option value="Puntarenas">Puntarenas</option>
                                        <option value="Limón">Limón</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Cantón *</label>
                                    <select name="est_canton" id="est_canton" required
                                        onchange="cargarDistritos('est', this.value)"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Seleccionar cantón</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Distrito *</label>
                                    <select name="est_distrito" id="est_distrito" required
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Seleccionar distrito</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Poblado / Señas *</label>
                                    <input type="text" name="est_poblado" value="{{ old('est_poblado') }}" required
                                        placeholder="Ej. Barrio La Cruz, 200m norte de la iglesia"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <input type="hidden" name="est_direccion" id="est_direccion_completa">
                        </div>

                        @if ($modalidad->nombre === 'Plan Nacional')
                            {{-- Campos exclusivos de Plan Nacional --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de discapacidad *</label>
                                <input type="text" name="est_tipo_discapacidad" value="{{ old('est_tipo_discapacidad') }}" required
                                    placeholder="Describa el tipo de discapacidad"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cuenta con boleta de ubicación *</label>
                                <select name="est_boleta_ubicacion" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccionar</option>
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nivel de funcionamiento *</label>
                                <textarea name="est_nivel_funcionamiento" rows="2" required
                                    placeholder="Describa el nivel de funcionamiento"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('est_nivel_funcionamiento') }}</textarea>
                            </div>
                        @else
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Adecuación *</label>
                                <select name="est_adecuacion" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccionar</option>
                                    <option value="No aplica">No aplica</option>
                                    <option value="Adecuación de acceso">Adecuación de acceso</option>
                                    <option value="Adecuación no significativa">Adecuación no significativa</option>
                                    <option value="Adecuación significativa">Adecuación significativa</option>
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Datos de los encargados --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-1">Datos del padre / madre / tutor</h3>
                    <p class="text-xs text-gray-400 mb-4">Podés agregar hasta 3 encargados. Marcá cuál es el principal (recibe correos y firma el documento).</p>

                    {{-- Encargado 1 (obligatorio) --}}
                    <div class="border border-blue-100 bg-blue-50 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-900">Encargado 1</h4>
                            <label class="flex items-center gap-2 text-xs text-blue-700 font-medium">
                                <input type="radio" name="principal" value="1" checked>
                                Marcar como principal
                            </label>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                                <input type="text" name="tut_nombre" value="{{ old('tut_nombre') }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Relación con el estudiante *</label>
                                <select name="tut_relacion" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccionar</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Madre">Madre</option>
                                    <option value="Tutor legal">Tutor legal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula *</label>
                                <input type="text" name="tut_cedula" value="{{ old('tut_cedula') }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono principal *</label>
                                <input type="tel" name="tut_telefono" value="{{ old('tut_telefono') }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono secundario</label>
                                <input type="tel" name="tut_telefono2" value="{{ old('tut_telefono2') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico *</label>
                                <input type="email" name="tut_email" value="{{ old('tut_email') }}" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ocupación</label>
                                <input type="text" name="tut_ocupacion" value="{{ old('tut_ocupacion') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            {{-- Dirección del encargado 1 --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dirección de residencia *</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Provincia *</label>
                                        <select name="tut_provincia" id="tut_provincia" required
                                            onchange="cargarCantones('tut', this.value)"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Seleccionar provincia</option>
                                            <option value="Alajuela">Alajuela</option>
                                            <option value="San José">San José</option>
                                            <option value="Cartago">Cartago</option>
                                            <option value="Heredia">Heredia</option>
                                            <option value="Guanacaste">Guanacaste</option>
                                            <option value="Puntarenas">Puntarenas</option>
                                            <option value="Limón">Limón</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Cantón *</label>
                                        <select name="tut_canton" id="tut_canton" required
                                            onchange="cargarDistritos('tut', this.value)"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Seleccionar cantón</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Distrito *</label>
                                        <select name="tut_distrito" id="tut_distrito" required
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Seleccionar distrito</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Poblado / Señas *</label>
                                        <input type="text" name="tut_poblado" value="{{ old('tut_poblado') }}" required
                                            placeholder="Ej. Barrio La Cruz, 200m norte de la iglesia"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="flex items-center gap-2 text-xs text-gray-500">
                                        <input type="checkbox" id="misma_direccion" onchange="copiarDireccionEstudiante(this.checked)">
                                        Usar la misma dirección del estudiante
                                    </label>
                                </div>
                                <input type="hidden" name="tut_direccion" id="tut_direccion_completa">
                            </div>
                        </div>
                    </div>

                    {{-- Encargado 2 (opcional) --}}
                    <div id="bloque_encargado2" class="border border-gray-200 rounded-lg p-4 mb-4 hidden">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-900">Encargado 2</h4>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 text-xs text-blue-700 font-medium">
                                    <input type="radio" name="principal" value="2">
                                    Marcar como principal
                                </label>
                                <button type="button" onclick="quitarEncargado(2)" class="text-xs text-red-500 hover:text-red-700">Quitar</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                                <input type="text" name="tut2_nombre" value="{{ old('tut2_nombre') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Relación con el estudiante</label>
                                <select name="tut2_relacion" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccionar</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Madre">Madre</option>
                                    <option value="Tutor legal">Tutor legal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                                <input type="text" name="tut2_cedula" value="{{ old('tut2_cedula') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input type="tel" name="tut2_telefono" value="{{ old('tut2_telefono') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono secundario</label>
                                <input type="tel" name="tut2_telefono2" value="{{ old('tut2_telefono2') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                                <input type="email" name="tut2_email" value="{{ old('tut2_email') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ocupación</label>
                                <input type="text" name="tut2_ocupacion" value="{{ old('tut2_ocupacion') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Encargado 3 (opcional) --}}
                    <div id="bloque_encargado3" class="border border-gray-200 rounded-lg p-4 mb-4 hidden">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-900">Encargado 3</h4>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 text-xs text-blue-700 font-medium">
                                    <input type="radio" name="principal" value="3">
                                    Marcar como principal
                                </label>
                                <button type="button" onclick="quitarEncargado(3)" class="text-xs text-red-500 hover:text-red-700">Quitar</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                                <input type="text" name="tut3_nombre" value="{{ old('tut3_nombre') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Relación con el estudiante</label>
                                <select name="tut3_relacion" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccionar</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Madre">Madre</option>
                                    <option value="Tutor legal">Tutor legal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                                <input type="text" name="tut3_cedula" value="{{ old('tut3_cedula') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input type="tel" name="tut3_telefono" value="{{ old('tut3_telefono') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono secundario</label>
                                <input type="tel" name="tut3_telefono2" value="{{ old('tut3_telefono2') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                                <input type="email" name="tut3_email" value="{{ old('tut3_email') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ocupación</label>
                                <input type="text" name="tut3_ocupacion" value="{{ old('tut3_ocupacion') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" id="btn_agregar_encargado2" onclick="mostrarEncargado(2)"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            + Agregar segundo encargado
                        </button>
                        <button type="button" id="btn_agregar_encargado3" onclick="mostrarEncargado(3)"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium hidden">
                            + Agregar tercer encargado
                        </button>
                    </div>
                </div>

                {{-- Nivel y sección --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">
                        @if ($esNocturna) Nivel y carrera técnica
                        @elseif ($esPlanNacional) Nivel y sección
                        @else Nivel y sección solicitada
                        @endif
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nivel solicitado *</label>
                            <select name="nivel_id" id="nivel_id" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                onchange="cargarOpciones(this.value); actualizarLabelColegio();">
                                <option value="">Seleccionar</option>
                                @foreach ($niveles as $nivel)
                                    <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($esNocturna)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Carrera técnica *</label>
                                <select name="carrera_id" id="carrera_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione primero el nivel</option>
                                </select>
                            </div>
                        @elseif ($esPlanNacional)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sección *</label>
                                <select name="seccion_id" id="seccion_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione primero el nivel</option>
                                </select>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sección preferida</label>
                                <select name="seccion_id" id="seccion_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    onchange="cargarTalleres(this.value)">
                                    <option value="">Seleccione primero el nivel</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Grupo de taller *</label>
                                <select name="grupo_taller" id="grupo_taller" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione primero la sección</option>
                                </select>
                            </div>
                        @endif

                        @if ($esPlanNacional)
                            {{-- Campos exclusivos de Plan Nacional, según ciclo del nivel elegido --}}
                            <div id="bloque_bajociclo" class="md:col-span-2 hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Técnica 1 *</label>
                                        <input type="text" name="tecnica_1" id="tecnica_1" value="{{ old('tecnica_1') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Técnica 2</label>
                                        <input type="text" name="tecnica_2" id="tecnica_2" value="{{ old('tecnica_2') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>
                            <div id="bloque_altociclo" class="md:col-span-2 hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Formación vocacional *</label>
                                        <input type="text" name="formacion_vocacional" id="formacion_vocacional" value="{{ old('formacion_vocacional') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Técnica</label>
                                        <input type="text" name="tecnica_alto" id="tecnica_alto" value="{{ old('tecnica_alto') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Seguimiento</label>
                                        <textarea name="seguimiento_pn" id="seguimiento_pn" rows="2"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('seguimiento_pn') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" id="label_colegio">
                                Centro educativo de procedencia *
                            </label>
                            <input type="text" name="colegio_procedencia" id="colegio_procedencia"
                                value="{{ old('colegio_procedencia') }}" required
                                placeholder="Nombre del centro educativo"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Año que cursó anteriormente *</label>
                            <input type="text" name="anio_cursado_anterior" value="{{ old('anio_cursado_anterior') }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                {{-- Documentos --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Documentos adjuntos</h3>
                    <div class="space-y-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Copia de cédula del estudiante *</label>
                            <input type="file" name="doc_cedula" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700">
                            <p class="text-xs text-gray-400 mt-1">PDF, JPG o PNG — máximo 5 MB</p>
                            <label class="flex items-center gap-2 text-xs text-gray-600 mt-2">
                                <input type="checkbox" name="fisico_cedula" value="1" onchange="toggleFisico(this, 'doc_cedula')">
                                Lo entrega en físico (no lo adjunta digitalmente)
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Certificado de notas del año anterior *</label>
                            <input type="file" name="doc_notas" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700">
                            <p class="text-xs text-gray-400 mt-1">PDF — máximo 5 MB</p>
                            <label class="flex items-center gap-2 text-xs text-gray-600 mt-2">
                                <input type="checkbox" name="fisico_notas" value="1" onchange="toggleFisico(this, 'doc_notas')">
                                Lo entrega en físico (no lo adjunta digitalmente)
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Foto reciente del estudiante</label>
                            <input type="file" name="doc_foto" accept=".jpg,.jpeg,.png"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700">
                            <p class="text-xs text-gray-400 mt-1">JPG o PNG — máximo 2 MB (opcional)</p>
                            <label class="flex items-center gap-2 text-xs text-gray-600 mt-2">
                                <input type="checkbox" name="fisico_foto" value="1" onchange="toggleFisico(this, 'doc_foto')">
                                Lo entrega en físico (no lo adjunta digitalmente)
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Copia de cédula del encargado legal *</label>
                            <input type="file" name="doc_cedula_encargado" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700">
                            <p class="text-xs text-gray-400 mt-1">PDF, JPG o PNG — máximo 5 MB</p>
                            <label class="flex items-center gap-2 text-xs text-gray-600 mt-2">
                                <input type="checkbox" name="fisico_cedula_encargado" value="1" onchange="toggleFisico(this, 'doc_cedula_encargado')">
                                Lo entrega en físico (no lo adjunta digitalmente)
                            </label>
                        </div>

                        @if ($esPlanNacional)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PASE *</label>
                                <input type="file" name="doc_pase" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700">
                                <p class="text-xs text-gray-400 mt-1">PDF, JPG o PNG — máximo 5 MB</p>
                                <label class="flex items-center gap-2 text-xs text-gray-600 mt-2">
                                    <input type="checkbox" name="fisico_pase" value="1" onchange="toggleFisico(this, 'doc_pase')">
                                    Lo entrega en físico (no lo adjunta digitalmente)
                                </label>
                            </div>
                        @endif

                        @if (!$esNocturna)
                            <div id="bloque_prueba_admision" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Certificado de prueba de admisión (solo Sétimo)</label>
                                <input type="file" name="doc_prueba_admision" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700">
                                <p class="text-xs text-gray-400 mt-1">PDF, JPG o PNG — máximo 5 MB (opcional)</p>
                                <label class="flex items-center gap-2 text-xs text-gray-600 mt-2">
                                    <input type="checkbox" name="fisico_prueba_admision" value="1" onchange="toggleFisico(this, 'doc_prueba_admision')">
                                    Lo entrega en físico (no lo adjunta digitalmente)
                                </label>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-700 hover:bg-blue-800 text-white font-medium px-6 py-2.5 rounded-md text-sm">
                        Enviar matrícula
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const datosNiveles = @json($nivelesJs);
    const esNocturna = {{ $esNocturna ? 'true' : 'false' }};
    const esPlanNacional = {{ $esPlanNacional ? 'true' : 'false' }};

    // ===== Niveles / secciones / talleres / carreras =====
    function cargarOpciones(nivelId) {
        if (esNocturna) {
            cargarCarreras(nivelId);
        } else if (esPlanNacional) {
            cargarSeccionesPlanNacional(nivelId);
            toggleCicloPlanNacional(nivelId);
        } else {
            cargarSecciones(nivelId);
        }
    }

    function cargarCarreras(nivelId) {
        const carreraSelect = document.getElementById('carrera_id');
        if (!carreraSelect) return;

        carreraSelect.innerHTML = '<option value="">Seleccionar carrera</option>';
        if (!nivelId || !datosNiveles[nivelId]) return;

        datosNiveles[nivelId].carreras.forEach(function(c) {
            const opt = document.createElement('option');
            opt.value = c.id;
            if (c.llena) {
                opt.textContent = c.nombre + ' — SIN CUPOS';
                opt.disabled = true;
                opt.style.color = '#ef4444';
            } else {
                opt.textContent = c.nombre + ' (' + c.cupos + ' cupos disponibles)';
            }
            carreraSelect.appendChild(opt);
        });
    }

    function cargarSeccionesPlanNacional(nivelId) {
        const seccionSelect = document.getElementById('seccion_id');
        if (!seccionSelect) return;

        seccionSelect.innerHTML = '<option value="">Seleccionar sección</option>';
        if (!nivelId || !datosNiveles[nivelId]) return;

        datosNiveles[nivelId].secciones.forEach(function(s) {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.nombre;
            seccionSelect.appendChild(opt);
        });
    }

    function toggleCicloPlanNacional(nivelId) {
        const bloqueBajo = document.getElementById('bloque_bajociclo');
        const bloqueAlto = document.getElementById('bloque_altociclo');
        if (!bloqueBajo || !bloqueAlto) return;

        bloqueBajo.classList.add('hidden');
        bloqueAlto.classList.add('hidden');
        document.getElementById('tecnica_1').required = false;
        document.getElementById('formacion_vocacional').required = false;

        if (!nivelId || !datosNiveles[nivelId]) return;

        const numero = String(datosNiveles[nivelId].numero);
        const esBajoCiclo = ['7', '8', '9'].includes(numero);

        if (esBajoCiclo) {
            bloqueBajo.classList.remove('hidden');
            document.getElementById('tecnica_1').required = true;
        } else {
            bloqueAlto.classList.remove('hidden');
            document.getElementById('formacion_vocacional').required = true;
        }
    }

    function cargarSecciones(nivelId) {
        const seccionSelect = document.getElementById('seccion_id');
        const tallerSelect  = document.getElementById('grupo_taller');
        seccionSelect.innerHTML = '<option value="">Sin preferencia</option>';
        tallerSelect.innerHTML  = '<option value="">Seleccione primero la sección</option>';
        if (!nivelId || !datosNiveles[nivelId]) return;

        datosNiveles[nivelId].secciones.forEach(function(s) {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.nombre;
            seccionSelect.appendChild(opt);
        });
    }

    function cargarTalleres(seccionId) {
        const nivelId      = document.getElementById('nivel_id').value;
        const tallerSelect = document.getElementById('grupo_taller');
        tallerSelect.innerHTML = '<option value="">Seleccionar grupo</option>';
        if (!nivelId || !seccionId || !datosNiveles[nivelId]) return;

        const seccion = datosNiveles[nivelId].secciones.find(function(s) { return s.id == seccionId; });
        if (!seccion) return;

        seccion.talleres.forEach(function(t) {
            const opt = document.createElement('option');
            opt.value = t.grupo;
            if (t.lleno) {
                opt.textContent = 'Grupo ' + t.grupo + ': ' + t.nombre + ' — SIN CUPOS';
                opt.disabled = true;
                opt.style.color = '#ef4444';
            } else {
                opt.textContent = 'Grupo ' + t.grupo + ': ' + t.nombre + ' (' + t.cupos + ' cupos disponibles)';
            }
            tallerSelect.appendChild(opt);
        });
    }

    // ===== Etiqueta escuela/colegio según nivel =====
    function actualizarLabelColegio() {
        const label = document.getElementById('label_colegio');
        const input = document.getElementById('colegio_procedencia');
        const nivelSelect = document.getElementById('nivel_id');
        const nivelTexto = nivelSelect.options[nivelSelect.selectedIndex]?.text || '';
        const esSetimo = nivelTexto.includes('7') || nivelTexto.toLowerCase().includes('sétimo') || nivelTexto.toLowerCase().includes('setimo');

        if (esSetimo) {
            label.textContent = 'Escuela de procedencia *';
            input.placeholder = 'Nombre de la escuela';
        } else {
            label.textContent = 'Colegio de procedencia *';
            input.placeholder = 'Nombre del colegio';
        }

        const bloquePrueba = document.getElementById('bloque_prueba_admision');
        if (bloquePrueba) {
            bloquePrueba.classList.toggle('hidden', !esSetimo);
        }
    }

    // ===== Datos geográficos de Costa Rica =====
    const geo = {
        "Alajuela": {
            "Alajuela": ["Alajuela","San José","Carrizal","San Antonio","Guácimo","San Isidro","Sabanilla","San Rafael","Río Segundo","Desamparados","Turrúcares","Tambor","La Garita","Sarapiquí"],
            "San Ramón": ["San Ramón","Santiago","San Juan","Piedades Norte","Piedades Sur","San Rafael","San Isidro","Ángeles","Alfaro","Volio","Concepción","Zapotal","Peñas Blancas","La Angostura"],
            "Grecia": ["Grecia","San Isidro","San José","San Roque","Tacares","Rodríguez","Puente Piedra","Bolívar"],
            "San Mateo": ["San Mateo","Desmonte","Jesús María","Labrador"],
            "Atenas": ["Atenas","Jesús","Mercedes","San Isidro","Concepción","San José","Santa Eulalia","Escobal"],
            "Naranjo": ["Naranjo","San Miguel","San José","Cirrí Sur","San Jerónimo","San Juan","El Rosario","Palmitos"],
            "Palmares": ["Palmares","Zaragoza","Buenos Aires","Santiago","Candelaria","Esquipulas","La Granja"],
            "Poás": ["San Juan","San Luis","Carrillos","Sabana Redonda","Güitite"],
            "Orotina": ["Orotina","El Mastate","Hacienda Vieja","Coyolar","La Ceiba"],
            "San Carlos": ["Ciudad Quesada","Florencia","Buenavista","Aguas Zarcas","Venecia","Pital","La Fortuna","La Tigra","La Palmera","Venado","Cutris","Monterrey","Pocosol"],
            "Zarcero": ["Zarcero","Laguna","Tapesco","Guadalupe","Palmira","Zapote","Brisas"],
            "Sarchí": ["Sarchí Norte","Sarchí Sur","Toro Amarillo","San Pedro","Rodríguez"],
            "Upala": ["Upala","Aguas Claras","San José","Bijagua","Delicias","Dos Ríos","Yolillal","Canalete"],
            "Los Chiles": ["Los Chiles","Caño Negro","El Amparo","San Jorge","Pocosol","Cutris","Guatuso","Río Frío","La Palmera","El Jardín","Las Brisas","Finca 6","Finca 7","Finca 8","Finca 9","Finca 10","El Jobo","La Reserva","Tablonal","San Antonio","Los Ángeles","La Luisa","Veracruz","San José","Las Vegas","La Granja","Margarita","El Capulín","Playuelas","Horquetas","Caño Ciego","Caño Chiquito","La Tigra","El Futuro","La Esperanza","Pueblo Nuevo"],
            "Guatuso": ["San Rafael","Buenavista","Cote","Katira"],
            "Río Cuarto": ["Río Cuarto","Santa Rita","Santa Isabel"]
        },
        "San José": {
            "San José": ["Carmen","Merced","Hospital","Catedral","Zapote","San Francisco de Dos Ríos","Uruca","Mata Redonda","Pavas","Hatillo","San Sebastián"],
            "Escazú": ["Escazú","San Antonio","San Rafael"],
            "Desamparados": ["Desamparados","San Miguel","San Juan de Dios","San Rafael Arriba","San Antonio","Frailes","Patarra","San Cristóbal","Rosario","Damas","San Rafael Abajo","Gravilias","Los Guido"],
            "Puriscal": ["Santiago","Mercedes Sur","Barbacoas","Grifo Alto","San Rafael","Candelarita","Desamparaditos","San Antonio","Chires"],
            "Tarrazú": ["San Marcos","San Lorenzo","San Carlos"],
            "Aserrí": ["Aserrí","Tarbaca","Vuelta de Jorco","San Gabriel","Legua","Monterrey","Salitrillos"],
            "Mora": ["Ciudad Colón","Bermúdez","Tabarcia","Piedras Negras","Picagres","Jaris","Quitirrisí"],
            "Goicoechea": ["Guadalupe","San Francisco","Calle Blancos","Mata de Plátano","Ipís","Rancho Redondo","Purral"],
            "Santa Ana": ["Santa Ana","Salitral","Pozos","Uruca","Piedades","Brasil"],
            "Alajuelita": ["Alajuelita","San Josecito","San Antonio","Concepción","San Felipe"],
            "Vásquez de Coronado": ["San Isidro","San Rafael","Dulce Nombre de Jesús","Patalillo","Cascajal"],
            "Acosta": ["San Ignacio","Guaitil","Palmichal","Cangrejal","Sabanillas"],
            "Tibás": ["San Juan","Cinco Esquinas","Anselmo Llorente","León XIII","Colima"],
            "Moravia": ["San Vicente","San Jerónimo","La Trinidad"],
            "Montes de Oca": ["San Pedro","Sabanilla","Mercedes","San Rafael"],
            "Turrubares": ["San Pablo","San Pedro","San Juan de Mata","San Luis","Carara"],
            "Dota": ["Santa María","Jardín","Copey"],
            "Curridabat": ["Curridabat","Granadilla","Sánchez","Tirrases"],
            "Pérez Zeledón": ["San Isidro de El General","El General","Daniel Flores","Rivas","San Pedro","Platanares","Pejibaye","Cajón","Barú","Río Nuevo","Páramo","La Amistad"],
            "León Cortés Castro": ["San Pablo","San Andrés","Llano Bonito","San Isidro","Santa Cruz","San Antonio"]
        },
        "Cartago": {
            "Cartago": ["Oriental","Occidental","El Tejar","San Nicolás","Aguacaliente","Guadalupe","Corralillo","Tierra Blanca","Dulce Nombre","Llanos de Santa Lucía","Quebradilla"],
            "Paraíso": ["Paraíso","Santiago","Orosi","Cachí","Llanos de Santa Lucía"],
            "La Unión": ["Tres Ríos","San Diego","San Juan","San Rafael","Concepción","Dulce Nombre","San Ramón","Río Azul"],
            "Jiménez": ["Juan Viñas","Tucurrique","Pejibaye"],
            "Turrialba": ["Turrialba","La Suiza","Peralta","Santa Cruz","Santa Teresita","Pavones","Tuis","Tayutic","Santa Rosa","Tres Equis","La Isabel","Chirripó"],
            "Alvarado": ["Pacayas","Cervantes","Capellades"],
            "Oreamuno": ["San Rafael","Cot","Potrero Cerrado","Cipreses","Santa Rosa"],
            "El Guarco": ["El Tejar","San Isidro","Tobosi","Patio de Agua"]
        },
        "Heredia": {
            "Heredia": ["Heredia","Mercedes","San Francisco","Ulloa","Varablanca"],
            "Barva": ["Barva","San Pedro","San Pablo","San Roque","Santa Lucía","San José de la Montaña"],
            "Santo Domingo": ["Santo Domingo","San Vicente","San Miguel","Paracito","Santo Tomás","Santa Rosa","Tures","Pará"],
            "Santa Bárbara": ["Santa Bárbara","San Pedro","San Juan","Jesús","Santo Domingo","Puraba"],
            "San Rafael": ["San Rafael","San Josecito","Santiago","Ángeles","Concepción"],
            "San Isidro": ["San Isidro","San José","Concepción","San Francisco"],
            "Belén": ["San Antonio","La Ribera","La Asunción"],
            "Flores": ["San Joaquín","Barrantes","Llorente"],
            "San Pablo": ["San Pablo","Rincón de Sabanilla"],
            "Sarapiquí": ["Puerto Viejo","La Virgen","Las Horquetas","Llanuras del Gaspar","Cureña"]
        },
        "Guanacaste": {
            "Liberia": ["Liberia","Cañas Dulces","Mayorga","Nacascolo","Curubandé"],
            "Nicoya": ["Nicoya","Mansion","San Antonio","Quebrada Honda","Sámara","Nosara","Belén de Nosarita"],
            "Santa Cruz": ["Santa Cruz","Bolsón","Veintisiete de Abril","Tempate","Cartagena","Cuajiniquil","Diriá","Cabo Velas","Tamarindo"],
            "Bagaces": ["Bagaces","La Fortuna","Mogote","Rio Naranjo"],
            "Carrillo": ["Filadelfia","Palmira","Sardinal","Belén"],
            "Cañas": ["Cañas","Palmira","San Miguel","Bebedero","Porozal"],
            "Abangares": ["Las Juntas","Sierra","San Juan","Colorado"],
            "Tilarán": ["Tilarán","Quebrada Grande","Tronadora","Santa Rosa","Líbano","Tierras Morenas","Arenal","Cabeceras"],
            "Nandayure": ["Carmona","Santa Rita","Zapotal","San Pablo","Porvenir","Bejuco"],
            "La Cruz": ["La Cruz","Santa Elena","Melcho","Santa Cecilia","La Garita"],
            "Hojancha": ["Hojancha","Monte Romo","Puerto Carrillo","Huacas","Matambú"]
        },
        "Puntarenas": {
            "Puntarenas": ["Puntarenas","Pitahaya","Chomes","Lepanto","Paquera","Manzanillo","Guacimal","Barranca","Invu Las Delicias","Chacarita","El Roble","Arancibia","Monteverde","Isla del Coco"],
            "Esparza": ["Espíritu Santo","San Juan Grande","Macacona","San Rafael","San Jerónimo","La Mina"],
            "Buenos Aires": ["Buenos Aires","Volcán","Potrero Grande","Boruca","Pilas","Colinas","Chánguena","Biolley","Brunka"],
            "Montes de Oro": ["Miramar","La Unión","San Isidro"],
            "Osa": ["Puerto Cortés","Palmar","Sierpe","Bahía Ballena","Piedras Blancas","Bahía Drake"],
            "Quepos": ["Quepos","Savegre","Naranjito"],
            "Golfito": ["Golfito","Puerto Jiménez","Guaycará","Pavón"],
            "Coto Brus": ["San Vito","Sabalito","Aguabuena","Limoncito","Pittier","Gutiérrez Braun"],
            "Parrita": ["Parrita"],
            "Corredores": ["Corredor","La Cuesta","Canoas","Laurel"],
            "Garabito": ["Jacó","Tárcoles"]
        },
        "Limón": {
            "Limón": ["Limón","Valle La Estrella","Río Blanco","Matama"],
            "Pococí": ["Guápiles","Jiménez","La Rita","Roxana","Cariari","Colorado","La Colonia"],
            "Siquirres": ["Siquirres","Pacuarito","Florida","Germania","El Cairo","Alegría","Reventazón"],
            "Talamanca": ["Bratsi","Sixaola","Cahuita","Telire"],
            "Matina": ["Matina","Batán","Carrandi"],
            "Guácimo": ["Guácimo","Mercedes","Pocora","Rio Jiménez","Duacarí"]
        }
    };

    // prefix = 'est' o 'tut'
    function cargarCantones(prefix, provincia) {
        const cantonSelect   = document.getElementById(prefix + '_canton');
        const distritoSelect = document.getElementById(prefix + '_distrito');
        cantonSelect.innerHTML   = '<option value="">Seleccionar cantón</option>';
        distritoSelect.innerHTML = '<option value="">Seleccionar distrito</option>';

        if (!provincia || !geo[provincia]) return;

        Object.keys(geo[provincia]).sort().forEach(function(canton) {
            const opt = document.createElement('option');
            opt.value = canton;
            opt.textContent = canton;
            cantonSelect.appendChild(opt);
        });
    }

    function cargarDistritos(prefix, canton) {
        const provincia = document.getElementById(prefix + '_provincia').value;
        const distritoSelect = document.getElementById(prefix + '_distrito');
        distritoSelect.innerHTML = '<option value="">Seleccionar distrito</option>';

        if (!canton || !geo[provincia] || !geo[provincia][canton]) return;

        geo[provincia][canton].forEach(function(distrito) {
            const opt = document.createElement('option');
            opt.value = distrito;
            opt.textContent = distrito;
            distritoSelect.appendChild(opt);
        });
    }

    function seleccionarLosChiles(prefix) {
        const provinciaSelect = document.getElementById(prefix + '_provincia');
        provinciaSelect.value = 'Alajuela';
        cargarCantones(prefix, 'Alajuela');

        const cantonSelect = document.getElementById(prefix + '_canton');
        cantonSelect.value = 'Los Chiles';
        cargarDistritos(prefix, 'Los Chiles');

        const distritoSelect = document.getElementById(prefix + '_distrito');
        distritoSelect.value = 'Los Chiles';
    }

    function copiarDireccionEstudiante(activar) {
        if (!activar) return;
        document.getElementById('tut_provincia').value = document.getElementById('est_provincia').value;
        cargarCantones('tut', document.getElementById('est_provincia').value);

        document.getElementById('tut_canton').value = document.getElementById('est_canton').value;
        cargarDistritos('tut', document.getElementById('est_canton').value);

        document.getElementById('tut_distrito').value = document.getElementById('est_distrito').value;
        document.querySelector('[name="tut_poblado"]').value = document.querySelector('[name="est_poblado"]').value;
    }

    function toggleFisico(checkbox, inputId) {
        const input = document.querySelector('[name="' + inputId + '"]');
        if (checkbox.checked) {
            input.value = '';
            input.disabled = true;
        } else {
            input.disabled = false;
        }
    }

    function generarCorreoMep(cedula) {
        const emailInput = document.getElementById('est_email_mep');
        if (!emailInput) return;

        const cedulaLimpia = cedula.replace(/\s+/g, '').trim();
        if (!cedulaLimpia) {
            emailInput.value = '';
            return;
        }
        emailInput.value = cedulaLimpia + '@est.mep.go.cr';
    }

    function calcularEdad(fechaNacimiento) {
        const spanEdad = document.getElementById('edad_calculada');
        if (!fechaNacimiento) {
            spanEdad.textContent = '';
            return;
        }

        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento + 'T00:00:00');

        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mesDiferencia = hoy.getMonth() - nacimiento.getMonth();

        if (mesDiferencia < 0 || (mesDiferencia === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }

        if (edad < 0 || edad > 100) {
            spanEdad.textContent = '';
            return;
        }

        spanEdad.textContent = edad + ' años';
    }

    function mostrarEncargado(numero) {
        document.getElementById('bloque_encargado' + numero).classList.remove('hidden');
        document.getElementById('btn_agregar_encargado' + numero).classList.add('hidden');

        if (numero === 2) {
            document.getElementById('btn_agregar_encargado3').classList.remove('hidden');
        }
    }

    function quitarEncargado(numero) {
        document.getElementById('bloque_encargado' + numero).classList.add('hidden');
        document.getElementById('btn_agregar_encargado' + numero).classList.remove('hidden');

        document.querySelectorAll('[name^="tut' + numero + '_"]').forEach(function(input) {
            input.value = '';
        });

        const radioPrincipal = document.querySelector('input[name="principal"][value="' + numero + '"]');
        if (radioPrincipal && radioPrincipal.checked) {
            document.querySelector('input[name="principal"][value="1"]').checked = true;
        }

        if (numero === 3) {
            document.getElementById('btn_agregar_encargado3').classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        seleccionarLosChiles('est');
        seleccionarLosChiles('tut');
        actualizarLabelColegio();

        const fechaNacimientoInput = document.getElementById('est_nacimiento');
        if (fechaNacimientoInput && fechaNacimientoInput.value) {
            calcularEdad(fechaNacimientoInput.value);
        }

        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            const estProv = document.getElementById('est_provincia').value;
            const estCant = document.getElementById('est_canton').value;
            const estDist = document.getElementById('est_distrito').value;
            const estPob  = document.querySelector('[name="est_poblado"]').value;
            document.getElementById('est_direccion_completa').value =
                [estProv, estCant, estDist, estPob].filter(Boolean).join(', ');

            const tutProv = document.getElementById('tut_provincia').value;
            const tutCant = document.getElementById('tut_canton').value;
            const tutDist = document.getElementById('tut_distrito').value;
            const tutPob  = document.querySelector('[name="tut_poblado"]').value;
            document.getElementById('tut_direccion_completa').value =
                [tutProv, tutCant, tutDist, tutPob].filter(Boolean).join(', ');
        });
    });
    </script>
</x-app-layout>