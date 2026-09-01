<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url('{{ storage_path("fonts/DejaVuSans.ttf") }}') format('truetype');
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px;
            color: #000;
            background: #fff;
        }

        .header-img {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #a9b4c0;
        }
        .header-img img {
            width: 100%;
            max-width: 650px;
        }

        .contenido { padding: 0 25px; }

        .titulo-principal { text-align: center; margin-bottom: 6px; }
        .titulo-principal h1 { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .titulo-principal h2 { font-size: 10px; font-weight: bold; margin-top: 2px; }

        .info-general { width: 100%; margin-bottom: 6px; }
        .info-general td { border: none; font-size: 9px; padding: 1px 3px; }

        .nivel-info { width: 100%; margin-bottom: 4px; }
        .nivel-info td { border: none; font-size: 9px; padding: 1px 3px; }

        .seccion-titulo {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            padding: 3px 0;
            border: 1px solid #000;
            background: #fff;
            margin-top: 6px;
        }

        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        table.datos td, table.datos th { border: 1px solid #000; padding: 2px 4px; font-size: 8.5px; vertical-align: middle; }
        table.datos th { background: #e0e0e0; font-weight: bold; font-size: 8px; }
        table.datos td.val { font-weight: bold; }
        table.datos td.vacia { height: 16px; }

        .sub-titulo { background: #f0f0f0; font-weight: bold; font-size: 9px; padding: 3px 5px; border: 1px solid #000; border-top: none; }

        .codigo-box { border: 1px solid #000; padding: 2px 8px; font-weight: bold; font-size: 10px; }

        .firma-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .firma-table td { border: 1px solid #000; padding: 4px 8px; vertical-align: bottom; height: 35px; font-size: 8px; }
        .firma-linea { border-top: 1px solid #000; margin-top: 18px; }
        .sello-td { width: 90px; text-align: center; vertical-align: middle; font-size: 8px; }

        .pie { text-align: center; font-size: 8px; color: #333; margin-top: 10px; padding-top: 6px; border-top: 1px solid #666; font-style: italic; }

        .check-ok { color: #000; font-weight: bold; }
        .check-no { color: #000; }
    </style>
</head>
<body>

    <div class="contenido">

        {{-- Encabezado único (una sola imagen) --}}
        <div class="header-img">
            <img src="{{ public_path('images/encabezado-completo.jpg') }}" alt="Encabezado institucional">
        </div>

        @php
            $esPlanNacionalPdf = $prematricula->modalidad && $prematricula->modalidad->nombre === 'Plan Nacional';
            $esNocturnaPdf = !$esPlanNacionalPdf && $prematricula->carrera_id !== null;
            $tallerA = $prematricula->seccion?->talleres->where('grupo', 'A')->first();
            $tallerB = $prematricula->seccion?->talleres->where('grupo', 'B')->first();
        @endphp

        <div class="titulo-principal">
            <h1>MATRÍCULA: {{ mb_strtoupper($prematricula->modalidad->nombre ?? '', 'UTF-8') }} {{ $prematricula->periodo->anio ?? now()->addYear()->year }}</h1>
            <h2>Institución: COLEGIO TÉCNICO PROFESIONAL LOS CHILES</h2>
        </div>

        <table class="info-general">
            <tr>
                <td>Fecha: <strong>{{ now()->format('d/m/Y') }}</strong></td>
                <td style="text-align:center">Código: <span class="codigo-box">{{ $prematricula->codigo }}</span></td>
                <td style="text-align:right">Circuito: <strong>09</strong></td>
            </tr>
        </table>

        @if ($esPlanNacionalPdf)
            @php
                $esBajoCicloPdf = $prematricula->nivel && in_array((string) $prematricula->nivel->numero, ['7', '8', '9']);
            @endphp
            <table class="nivel-info">
                <tr>
                    <td style="width: 50%">Nivel: <strong>{{ $prematricula->nivel->nombre ?? '---' }}</strong></td>
                    <td style="width: 50%">Sección: <strong>{{ $prematricula->seccion->nombre ?? '---' }}</strong></td>
                </tr>
                @if ($esBajoCicloPdf)
                    <tr>
                        <td>Técnica 1: <strong>{{ $prematricula->tecnica_1 ?? '---' }}</strong></td>
                        <td>Técnica 2: <strong>{{ $prematricula->tecnica_2 ?? '---' }}</strong></td>
                    </tr>
                @else
                    <tr>
                        <td>Formación vocacional: <strong>{{ $prematricula->formacion_vocacional ?? '---' }}</strong></td>
                        <td>Técnica: <strong>{{ $prematricula->tecnica_3 ?? '---' }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="2">Seguimiento: <strong>{{ $prematricula->seguimiento_pn ?? '---' }}</strong></td>
                    </tr>
                @endif
            </table>

       {{-- @elseif ($esNocturnaPdf)
            <table class="nivel-info">
                <tr>
                    <td style="width: 40%">Nivel: <strong>{{ $prematricula->nivel->nombre ?? '---' }}</strong></td>
                    <td style="width: 60%">Carrera técnica: <strong>{{ $prematricula->carrera->nombre ?? '---' }}</strong></td>
                </tr>
                @php
                    $opcionesExtraCarrera = collect([
                        $prematricula->carreraSegundaOpcion ? '2ª: ' . $prematricula->carreraSegundaOpcion->nombre : null,
                        $prematricula->carreraTerceraOpcion ? '3ª: ' . $prematricula->carreraTerceraOpcion->nombre : null,
                        $prematricula->carreraCuartaOpcion  ? '4ª: ' . $prematricula->carreraCuartaOpcion->nombre : null,
                    ])->filter();
                @endphp
                @if ($opcionesExtraCarrera->count())
                    <tr>
                        <td colspan="2" style="font-size:8px;">Otras opciones: {{ $opcionesExtraCarrera->implode('  |  ') }}</td>
                    </tr>
                @endif
            </table> --}}

        @elseif ($esNocturnaPdf)
            <table class="nivel-info">
                <tr>
                    <td style="width: 40%">Nivel: <strong>{{ $prematricula->nivel->nombre ?? '---' }}</strong></td>
                    <td style="width: 60%">Carrera técnica: <strong>{{ $prematricula->carrera->nombre ?? '---' }}</strong></td>
                </tr>
            </table>

       {{-- @else
            <table class="nivel-info">
                <tr>
                    <td>Nivel: <strong>{{ $prematricula->nivel->nombre ?? '---' }}</strong></td>
                    <td style="text-align:center">Sección: <strong>{{ $prematricula->seccion->nombre ?? 'Sin preferencia' }}</strong></td>
                </tr>
                <tr>
                    <td>Sub A Especialidad/Taller: <strong>{{ $tallerA?->nombre ?? '---' }}</strong></td>
                    <td style="text-align:center">Sub B Especialidad/Taller: <strong>{{ $tallerB?->nombre ?? '---' }}</strong></td>
                    <td style="text-align:right">Grupo elegido: <strong>{{ $prematricula->grupo_taller ? 'Grupo '.$prematricula->grupo_taller : '---' }}</strong></td>
                </tr>
                @php
                    $opcionesExtra = collect([
                        $prematricula->tallerSegundaOpcion ? '2ª: ' . $prematricula->tallerSegundaOpcion->seccion->nombre . '-' . $prematricula->tallerSegundaOpcion->nombre . ' (Grupo ' . $prematricula->tallerSegundaOpcion->grupo . ')' : null,
                        $prematricula->tallerTerceraOpcion ? '3ª: ' . $prematricula->tallerTerceraOpcion->seccion->nombre . '-' . $prematricula->tallerTerceraOpcion->nombre . ' (Grupo ' . $prematricula->tallerTerceraOpcion->grupo . ')' : null,
                        $prematricula->tallerCuartaOpcion  ? '4ª: ' . $prematricula->tallerCuartaOpcion->seccion->nombre . '-' . $prematricula->tallerCuartaOpcion->nombre . ' (Grupo ' . $prematricula->tallerCuartaOpcion->grupo . ')' : null,
                    ])->filter();
                @endphp
                @if ($opcionesExtra->count())
                    <tr>
                        <td colspan="3" style="font-size:8px;">Otras opciones: {{ $opcionesExtra->implode('  |  ') }}</td>
                    </tr>
                @endif
            </table>
        @endif --}}

        @else
            <table class="nivel-info">
                <tr>
                    <td>Nivel: <strong>{{ $prematricula->nivel->nombre ?? '---' }}</strong></td>
                    <td style="text-align:center">Sección: <strong>{{ $prematricula->seccion->nombre ?? 'Sin preferencia' }}</strong></td>
                </tr>
                <tr>
                    <td>Sub A Especialidad/Taller: <strong>{{ $tallerA?->nombre ?? '---' }}</strong></td>
                    <td style="text-align:center">Sub B Especialidad/Taller: <strong>{{ $tallerB?->nombre ?? '---' }}</strong></td>
                    <td style="text-align:right">Grupo elegido: <strong>{{ $prematricula->grupo_taller ? 'Grupo '.$prematricula->grupo_taller : '---' }}</strong></td>
                </tr>
            </table>
        @endif

        {{-- 1. Datos del estudiante --}}
        <div class="seccion-titulo">1. Datos personales del estudiante</div>
        <table class="datos">
            <tr>
                <th width="20%">Identificación</th>
                <th width="20%">Primer Apellido</th>
                <th width="20%">Segundo Apellido</th>
                <th width="40%">Nombre Completo</th>
            </tr>
            <tr>
                <td class="val">{{ $prematricula->estudiante->cedula }}</td>
                <td class="val">{{ explode(' ', trim($prematricula->estudiante->apellido))[0] ?? '' }}</td>
                <td class="val">{{ explode(' ', trim($prematricula->estudiante->apellido))[1] ?? '' }}</td>
                <td class="val">{{ $prematricula->estudiante->nombre }}</td>
            </tr>
            <tr>
                <th>Nacionalidad</th>
                <th>Correo institucional (MEP)</th>
                <th colspan="2">Dirección Residencia</th>
            </tr>
            <tr>
                <td class="val">{{ $prematricula->estudiante->nacionalidad ?? 'Costarricense' }}</td>
                <td class="val">{{ $prematricula->estudiante->email_mep ?? '---' }}</td>
                <td class="val" colspan="2">{{ $prematricula->estudiante->direccion }}</td>
            </tr>
            @php
                $fechaNac = \Carbon\Carbon::parse($prematricula->estudiante->fecha_nacimiento);
                $edadActual = $fechaNac->age;
            @endphp
            <tr>
                <th>Fecha de nacimiento</th>
                <th>Género</th>
                <th>Centro educativo de procedencia</th>
                <th>Año cursado anteriormente</th>
            </tr>
            <tr>
                <td class="val">{{ $fechaNac->format('d/m/Y') }} ({{ $edadActual }} años)</td>
                <td class="val">{{ $prematricula->estudiante->genero ?? '---' }}</td>
                <td class="val">{{ $prematricula->colegio_procedencia }}</td>
                <td class="val">{{ $prematricula->anio_cursado_anterior }}</td>
            </tr>
            @if ($esPlanNacionalPdf)
                <tr>
                    <th>Tipo de discapacidad</th>
                    <th>Cuenta con boleta de ubicación</th>
                    <th colspan="2">Nivel de funcionamiento</th>
                </tr>
                <tr>
                    <td class="val">{{ $prematricula->estudiante->tipo_discapacidad ?? '---' }}</td>
                    <td class="val">{{ $prematricula->estudiante->boleta_ubicacion ?? '---' }}</td>
                    <td class="val" colspan="2">{{ $prematricula->estudiante->nivel_funcionamiento ?? '---' }}</td>
                </tr>
            @else
                <tr>
                    <th colspan="4">Adecuación</th>
                </tr>
                <tr>
                    <td class="val" colspan="4">{{ $prematricula->estudiante->adecuacion ?? 'No aplica' }}</td>
                </tr>
            @endif
        </table>

        {{-- 2. Datos del/los Encargado(s) Legal(es) --}}
        <div class="seccion-titulo">2. Datos del Encargado Legal</div>
        @php
            $listaEncargados = $prematricula->tutores->count() > 0
                ? $prematricula->tutores->sortByDesc(fn($e) => $e->pivot->principal)->values()
                : collect([$prematricula->tutor]);
            $etiquetasEncargados = ['Principal', 'Segundo', 'Tercero'];
        @endphp
        <table class="datos" style="font-size: 8px;">
            <tr>
                <th width="12%">Tipo</th>
                <th width="20%">Nombre completo</th>
                <th width="12%">Relación</th>
                <th width="12%">Cédula</th>
                <th width="11%">Tel. principal</th>
                <th width="11%">Tel. secundario</th>
                <th width="17%">Correo</th>
                <th width="15%">Ocupación</th>
            </tr>
            @foreach ($listaEncargados as $i => $encargado)
                <tr>
                    <td class="val">{{ $etiquetasEncargados[$i] ?? ('Encargado ' . ($i + 1)) }}</td>
                    <td class="val">{{ $encargado->nombre_completo }}</td>
                    <td class="val">{{ $encargado->relacion ?? '---' }}</td>
                    <td class="val">{{ $encargado->cedula ?? '---' }}</td>
                    <td class="val">{{ $encargado->telefono_principal ?? '---' }}</td>
                    <td class="val">{{ $encargado->telefono_secundario ?? '---' }}</td>
                    <td class="val" style="font-size:7px;">{{ $encargado->email ?? '---' }}</td>
                    <td class="val">{{ $encargado->ocupacion ?? '---' }}</td>
                </tr>
            @endforeach
        </table>
        <table class="datos">
            <tr>
                <th width="100%">Dirección del encargado principal</th>
            </tr>
            <tr>
                <td class="val">{{ $prematricula->tutor->direccion ?? $prematricula->estudiante->direccion }}</td>
            </tr>
        </table>

        {{-- 3. Documentos --}}
        <div class="seccion-titulo">3. Documentos Presentados</div>
        <table class="datos">
            <tr>
                <th width="60%">Documento</th>
                <th width="20%">Estado</th>
                <th width="20%">Observación</th>
            </tr>
            @php
                $tiposDocumentos = [
                    'cedula_estudiante' => 'Cédula de identidad del estudiante',
                    'cedula_encargado'  => 'Cédula del encargado legal',
                    'notas'             => 'Certificado de notas del año anterior',
                    'foto'              => 'Fotografía reciente del estudiante',
                ];

                $esSetimoPdf = $prematricula->nivel && (
                    str_contains((string) $prematricula->nivel->numero, '7') ||
                    str_contains(strtolower($prematricula->nivel->nombre), 'sétimo') ||
                    str_contains(strtolower($prematricula->nivel->nombre), 'setimo')
                );

                if ($esSetimoPdf) {
                    $tiposDocumentos['prueba_admision'] = 'Certificado de prueba de admisión';
                }

                if ($esPlanNacionalPdf) {
                    $tiposDocumentos['pase'] = 'PASE';
                }

                $docsPresentes = $prematricula->documentos->pluck('tipo')->toArray();
            @endphp
            @foreach ($tiposDocumentos as $tipo => $label)
                @php
                    $doc = $prematricula->documentos->firstWhere('tipo', $tipo);
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td style="text-align:center" class="{{ $doc ? 'check-ok' : 'check-no' }}">
                        @if ($doc && $doc->entregado_fisico)
                            Entregado en físico
                        @elseif ($doc)
                            Presentado digital
                        @else
                            Pendiente
                        @endif
                    </td>
                    <td class="vacia"></td>
                </tr>
            @endforeach
        </table>

        {{-- Firmas --}}
        <table class="firma-table">
            <tr>
                <td width="40%">
                    Nombre del encargado que matricula
                    <div style="font-weight: bold; font-size: 9px; padding-top: 14px;">
                        {{ $prematricula->tutor->nombre_completo }}
                    </div>
                    <div style="font-size: 7px; color: #555;">
                        Cédula: {{ $prematricula->tutor->cedula }}
                    </div>
                </td>
                <td width="35%">
                    Firma del encargado legal
                    <div class="firma-linea"></div>
                </td>
                <td class="sello-td" rowspan="{{ $esPlanNacionalPdf ? 4 : 3 }}">
                    <div style="border: 1px dashed #999; height: 60px; text-align: center; padding-top: 24px; font-size: 8px; color: #999;">
                        Sello
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    Nombre del docente encargado de matrícula
                    <div style="font-weight: bold; font-size: 9px; padding-top: 14px;">
                        {{ $prematricula->user->name ?? '---' }}
                    </div>
                </td>
                <td>
                    Firma del docente
                    <div class="firma-linea"></div>
                </td>
            </tr>

            @if ($esPlanNacionalPdf)
                <tr>
                    <td>
                        Coordinadora de Plan Nacional
                        <div style="font-weight: bold; font-size: 9px; padding-top: 14px;">
                            Licda. Mónica Saborío Herrera
                        </div>
                    </td>
                    <td>
                        Firma
                        <div class="firma-linea"></div>
                    </td>
                </tr>
            @endif

            <tr>
                <td>
                    Director(a)
                    <div style="font-weight: bold; font-size: 9px; padding-top: 14px;">
                        MSc. Kattia Madrigal Gómez
                    </div>
                </td>
                <td>
                    Firma
                    <div class="firma-linea"></div>
                </td>
            </tr>
        </table>

        <div class="pie">
            Alajuela, Los Chiles, Av 7, calle 2, contiguo a la Escuela Ricardo Vargas Murillo<br>
            Tel: 2471-1110, ctp.loschiles@mep.go.cr — www.mep.go.cr
        </div>

    </div>
</body>
</html>