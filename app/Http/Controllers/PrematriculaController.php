<?php

namespace App\Http\Controllers;

use App\Models\Prematricula;
use App\Models\Estudiante;
use App\Models\Tutor;
use App\Models\Documento;
use App\Models\Nivel;
use App\Models\Periodo;
use App\Mail\PrematriculaRecibida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;


class PrematriculaController extends Controller
{
    public function index()
{
    $periodo = Periodo::activo();

    $prematriculas = Prematricula::where('user_id', Auth::id())
        ->with(['estudiante', 'tutor', 'documentos', 'nivel', 'seccion', 'periodo', 'modalidad'])
        ->latest()
        ->get();

    return view('prematricula.index', compact('prematriculas', 'periodo'));
}

    public function create(Request $request)
{
    $periodo = Periodo::activo();

    if (!$periodo) {
        return view('prematricula.cerrado', [
            'mensaje' => 'No hay ningún período de prematrícula activo en este momento.'
        ]);
    }

    if (!$periodo->estaAbierto()) {
        return view('prematricula.cerrado', [
            'mensaje' => "El período \"{$periodo->nombre}\" está fuera de las fechas habilitadas ({$periodo->fecha_inicio->format('d/m/Y')} al {$periodo->fecha_fin->format('d/m/Y')})."
        ]);
    }

    /** @var \App\Models\User $usuario */
    $usuario = Auth::user();

    if ($usuario->esAdmin()) {
        $modalidades = \App\Models\Modalidad::where('activa', true)->get();
    } else {
        $modalidades = $usuario->modalidades()->where('activa', true)->get();
    }

    if ($modalidades->isEmpty()) {
        return view('prematricula.cerrado', [
            'mensaje' => 'No tenés ninguna modalidad asignada. Contactá al administrador.'
        ]);
    }

    $modalidadId = $request->get('modalidad_id');
    if (!$modalidadId && $modalidades->count() === 1) {
        $modalidadId = $modalidades->first()->id;
    }

    if (!$modalidadId) {
        return view('prematricula.elegir_modalidad', compact('modalidades', 'periodo'));
    }

    $modalidad = $modalidades->firstWhere('id', $modalidadId);
    if (!$modalidad) {
        return view('prematricula.cerrado', [
            'mensaje' => 'No tenés acceso a esa modalidad.'
        ]);
    }

    $esNocturna     = $modalidad->nombre === 'Nocturna';
    $esPlanNacional = $modalidad->nombre === 'Plan Nacional';
    $esDiurna       = $modalidad->nombre === 'Diurna';

    $niveles = Nivel::with([
        'secciones' => function($q) {
            $q->where('activa', true)->orderBy('numero');
        },
        'secciones.talleres',
        'carreras' => function($q) {
            $q->where('activa', true)->orderBy('nombre');
        },
    ])->where('activo', true)
      ->where('modalidad_id', $modalidad->id)
      ->get();

    $nivelesJs = $niveles->map(function($n) use ($esNocturna, $esPlanNacional) {
        if ($esNocturna) {
            $carreras = $n->carreras->where('activa', true)->values()->map(function($c) {
                return [
                    'id'     => $c->id,
                    'nombre' => $c->nombre,
                    'llena'  => $c->estaLlena(),
                    'cupos'  => $c->cuposDisponibles(),
                ];
            });

            return [
                'id'       => $n->id,
                'tipo'     => 'nocturna',
                'numero'   => $n->numero,
                'carreras' => $carreras,
                'opciones' => $carreras->map(fn($c) => ['id' => $c['id'], 'nombre' => $c['nombre']]),
            ];
        } elseif ($esPlanNacional) {
            $secciones = $n->secciones->where('activa', true)->values()->map(function($s) {
                return ['id' => $s->id, 'nombre' => $s->nombre];
            });

            return [
                'id'        => $n->id,
                'tipo'      => 'planNacional',
                'numero'    => $n->numero,
                'secciones' => $secciones,
            ];
        } else {
            $secciones = $n->secciones->where('activa', true)->values()->map(function($s) {
                $talleres = $s->talleres->map(function($t) {
                    return [
                        'id'     => $t->id,
                        'nombre' => $t->nombre,
                        'grupo'  => $t->grupo,
                        'lleno'  => $t->estaLleno(),
                        'cupos'  => $t->cuposDisponibles(),
                    ];
                });
                return [
                    'id'       => $s->id,
                    'nombre'   => $s->nombre,
                    'talleres' => $talleres,
                ];
            });

            $opciones = collect();
            foreach ($secciones as $seccion) {
                foreach ($seccion['talleres'] as $taller) {
                    $opciones->push([
                        'id'     => $taller['id'],
                        'nombre' => $seccion['nombre'] . ' — ' . $taller['nombre'] . ' (Grupo ' . $taller['grupo'] . ')',
                    ]);
                }
            }

            return [
                'id'        => $n->id,
                'tipo'      => 'diurna',
                'numero'    => $n->numero,
                'secciones' => $secciones,
                'opciones'  => $opciones,
            ];
        }
    })->keyBy('id');

    return view('prematricula.create', compact('niveles', 'nivelesJs', 'periodo', 'modalidad', 'esNocturna', 'esPlanNacional', 'esDiurna'));
}

    public function store(Request $request)
{
    $periodo = Periodo::activo();

    if (!$periodo || !$periodo->estaAbierto()) {
        return redirect()->route('prematricula.index')
            ->with('error', 'El período de prematrícula no está activo.');
    }

    $validado = $request->validate([
        'est_nombre'       => 'required|string|max:100',
        'est_apellido'     => 'required|string|max:100',
        'est_cedula'       => 'required|string|unique:estudiantes,cedula',
        'est_nacimiento'   => 'required|date',
        'est_genero'       => 'nullable|string',
        'est_nacionalidad' => 'nullable|string',
        'est_adecuacion'           => 'nullable|string',
        'est_tipo_discapacidad'    => 'nullable|string|max:150',
        'est_boleta_ubicacion'     => 'nullable|in:Sí,No',
        'est_nivel_funcionamiento' => 'nullable|string',
        'est_email_mep'    => 'nullable|email',

        'est_provincia' => 'required|string',
        'est_canton'    => 'required|string',
        'est_distrito'  => 'required|string',
        'est_poblado'   => 'required|string',

        'tut_nombre'    => 'required|string|max:150',
        'tut_relacion'  => 'required|string',
        'tut_cedula'    => 'required|string',
        'tut_telefono'  => 'required|string',
        'tut_telefono2' => 'nullable|string',
        'tut_email'     => 'required|email',
        'tut_ocupacion' => 'nullable|string',

        'tut_provincia' => 'required|string',
        'tut_canton'    => 'required|string',
        'tut_distrito'  => 'required|string',
        'tut_poblado'   => 'required|string',

        'principal'     => 'required|in:1,2,3',

        'tut2_nombre'    => 'nullable|string|max:150',
        'tut2_relacion'  => 'nullable|string',
        'tut2_cedula'    => 'nullable|string',
        'tut2_telefono'  => 'nullable|string',
        'tut2_telefono2' => 'nullable|string',
        'tut2_email'     => 'nullable|email',
        'tut2_ocupacion' => 'nullable|string',

        'tut3_nombre'    => 'nullable|string|max:150',
        'tut3_relacion'  => 'nullable|string',
        'tut3_cedula'    => 'nullable|string',
        'tut3_telefono'  => 'nullable|string',
        'tut3_telefono2' => 'nullable|string',
        'tut3_email'     => 'nullable|email',
        'tut3_ocupacion' => 'nullable|string',

        'nivel_id'              => 'required|exists:niveles,id',
        'seccion_id'            => 'nullable|exists:secciones,id',
        'grupo_taller'          => 'nullable|string|in:A,B',
        'carrera_id'            => 'nullable|exists:carreras,id',
        'colegio_procedencia'   => 'required|string',
        'anio_cursado_anterior' => 'required|string',

        'tecnica_1'             => 'nullable|string|max:150',
        'tecnica_2'             => 'nullable|string|max:150',
        'formacion_vocacional'  => 'nullable|string|max:150',
        'tecnica_alto'          => 'nullable|string|max:150',
        'seguimiento_pn'        => 'nullable|string',

        'doc_cedula' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120|required_without:fisico_cedula',
        'doc_notas'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120|required_without:fisico_notas',
        'doc_foto'   => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'doc_cedula_encargado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120|required_without:fisico_cedula_encargado',
        'doc_prueba_admision'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'doc_pase'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

        'fisico_cedula'           => 'nullable|boolean',
        'fisico_notas'            => 'nullable|boolean',
        'fisico_cedula_encargado' => 'nullable|boolean',
        'fisico_prueba_admision'  => 'nullable|boolean',
        'fisico_pase'             => 'nullable|boolean',

        'modalidad_id' => 'required|exists:modalidades,id',
        'taller_segunda_opcion_id'  => 'nullable|exists:seccion_talleres,id',
        'carrera_segunda_opcion_id' => 'nullable|exists:carreras,id',
        'taller_tercera_opcion_id'  => 'nullable|exists:seccion_talleres,id',
        'taller_cuarta_opcion_id'   => 'nullable|exists:seccion_talleres,id',
        'carrera_tercera_opcion_id' => 'nullable|exists:carreras,id',
        'carrera_cuarta_opcion_id'  => 'nullable|exists:carreras,id',
    ]);

    // Verificar cupo disponible — solo para Diurna (secciones y talleres)
    if (!empty($validado['seccion_id']) && !empty($validado['grupo_taller'])) {
        $tallerGrupo = \App\Models\SeccionTaller::where('seccion_id', $validado['seccion_id'])
            ->where('grupo', $validado['grupo_taller'])
            ->first();

        if ($tallerGrupo && $tallerGrupo->estaLleno()) {
            return back()->withInput()
                ->withErrors(['grupo_taller' => 'El grupo seleccionado ya no tiene cupos disponibles.']);
        }
    }

    // Verificar cupo disponible — solo para Nocturna (carreras)
    if (!empty($validado['carrera_id'])) {
        $carrera = \App\Models\Carrera::find($validado['carrera_id']);
        if ($carrera && $carrera->estaLlena()) {
            return back()->withInput()
                ->withErrors(['carrera_id' => 'La carrera seleccionada ya no tiene cupos disponibles.']);
        }
    }

    $emailMep = $validado['est_email_mep'] ?? null;
if (empty($emailMep)) {
    $emailMep = $validado['est_cedula'] . '@est.mep.go.cr';
}

$estudiante = Estudiante::create([
    'nombre'                => $validado['est_nombre'],
    'apellido'              => $validado['est_apellido'],
    'cedula'                => $validado['est_cedula'],
    'fecha_nacimiento'      => $validado['est_nacimiento'],
    'genero'                => $validado['est_genero'] ?? null,
    'nacionalidad'          => $validado['est_nacionalidad'] ?? null,
    'adecuacion'            => $validado['est_adecuacion'] ?? null,
    'tipo_discapacidad'     => $validado['est_tipo_discapacidad'] ?? null,
    'boleta_ubicacion'      => $validado['est_boleta_ubicacion'] ?? null,
    'nivel_funcionamiento'  => $validado['est_nivel_funcionamiento'] ?? null,
    'email_mep'             => $emailMep,
        'direccion' => implode(', ', array_filter([
            $validado['est_provincia'],
            $validado['est_canton'],
            $validado['est_distrito'],
            $validado['est_poblado'],
        ])),
    ]);

    $direccionTutor = implode(', ', array_filter([
        $validado['tut_provincia'],
        $validado['tut_canton'],
        $validado['tut_distrito'],
        $validado['tut_poblado'],
    ]));

    $encargados = [
        1 => [
            'nombre_completo'     => $validado['tut_nombre'],
            'relacion'            => $validado['tut_relacion'],
            'cedula'              => $validado['tut_cedula'],
            'telefono_principal'  => $validado['tut_telefono'],
            'telefono_secundario' => $validado['tut_telefono2'] ?? null,
            'email'               => $validado['tut_email'],
            'ocupacion'           => $validado['tut_ocupacion'] ?? null,
            'direccion'           => $direccionTutor,
        ],
    ];

    if (!empty($validado['tut2_nombre'])) {
    $encargados[2] = [
        'nombre_completo'     => $validado['tut2_nombre'],
        'relacion'            => $validado['tut2_relacion'] ?? null,
        'cedula'              => $validado['tut2_cedula'] ?? null,
        'telefono_principal'  => $validado['tut2_telefono'] ?? null,
        'telefono_secundario' => $validado['tut2_telefono2'] ?? null,
        'email'               => $validado['tut2_email'] ?? null,
        'ocupacion'           => $validado['tut2_ocupacion'] ?? null,
        'direccion'           => $direccionTutor,
    ];
}

if (!empty($validado['tut3_nombre'])) {
    $encargados[3] = [
        'nombre_completo'     => $validado['tut3_nombre'],
        'relacion'            => $validado['tut3_relacion'] ?? null,
        'cedula'              => $validado['tut3_cedula'] ?? null,
        'telefono_principal'  => $validado['tut3_telefono'] ?? null,
        'telefono_secundario' => $validado['tut3_telefono2'] ?? null,
        'email'               => $validado['tut3_email'] ?? null,
        'ocupacion'           => $validado['tut3_ocupacion'] ?? null,
        'direccion'           => $direccionTutor,
    ];
}
$modalidadSeleccionada = \App\Models\Modalidad::find($validado['modalidad_id']);
$esPlanNacional = $modalidadSeleccionada && $modalidadSeleccionada->nombre === 'Plan Nacional';

if ($esPlanNacional) {
    $request->validate([
        'est_tipo_discapacidad'    => 'required|string|max:150',
        'est_boleta_ubicacion'     => 'required|in:Sí,No',
        'est_nivel_funcionamiento' => 'required|string',
    ]);
} else {
    $request->validate([
        'est_adecuacion' => 'required|string',
    ]);
}

$nivelSeleccionado = \App\Models\Nivel::find($validado['nivel_id']);
$esBajoCiclo = $nivelSeleccionado && in_array((string) $nivelSeleccionado->numero, ['7', '8', '9']);

if ($esPlanNacional) {
    $validado = array_merge($validado, $request->validate([
        'seccion_id' => 'required|exists:secciones,id',
    ]));

    if ($esBajoCiclo) {
        $validado = array_merge($validado, $request->validate([
            'tecnica_1' => 'required|string|max:150',
        ]));
    } else {
        $validado = array_merge($validado, $request->validate([
            'formacion_vocacional' => 'required|string|max:150',
        ]));
    }
}

    

    $principalNum = (int) $validado['principal'];
    $tutoresCreados = [];

    foreach ($encargados as $numero => $datosEncargado) {
        $tutoresCreados[$numero] = Tutor::create(array_merge($datosEncargado, [
            'user_id' => Auth::id(),
        ]));
    }

    // El tutor "principal" se guarda también en prematriculas.tutor_id para compatibilidad
    $tutor = $tutoresCreados[$principalNum] ?? $tutoresCreados[1];

    $prematricula = Prematricula::create([
        'codigo'                => Prematricula::generarCodigo(),
        'user_id'               => Auth::id(),
        'periodo_id'            => $periodo->id,
        'estudiante_id'         => $estudiante->id,
        'tutor_id'              => $tutor->id,
        'nivel_id'              => $validado['nivel_id'],
        'seccion_id'            => $validado['seccion_id'] ?? null,
        'grupo_taller'          => $validado['grupo_taller'] ?? null,
        'colegio_procedencia'   => $validado['colegio_procedencia'],
        'anio_cursado_anterior' => $validado['anio_cursado_anterior'],
        'modalidad_id'          => $validado['modalidad_id'],
        'carrera_id'            => $validado['carrera_id'] ?? null,
        'taller_segunda_opcion_id'  => $validado['taller_segunda_opcion_id'] ?? null,
        'carrera_segunda_opcion_id' => $validado['carrera_segunda_opcion_id'] ?? null,
        'taller_tercera_opcion_id'  => $validado['taller_tercera_opcion_id'] ?? null,
        'taller_cuarta_opcion_id'   => $validado['taller_cuarta_opcion_id'] ?? null,
        'carrera_tercera_opcion_id' => $validado['carrera_tercera_opcion_id'] ?? null,
        'carrera_cuarta_opcion_id'  => $validado['carrera_cuarta_opcion_id'] ?? null,
        'tecnica_1'            => ($esPlanNacional && $esBajoCiclo) ? ($validado['tecnica_1'] ?? null) : null,
        'tecnica_2'            => ($esPlanNacional && $esBajoCiclo) ? ($validado['tecnica_2'] ?? null) : null,
        'formacion_vocacional' => ($esPlanNacional && !$esBajoCiclo) ? ($validado['formacion_vocacional'] ?? null) : null,
        'tecnica_3'            => ($esPlanNacional && !$esBajoCiclo) ? ($validado['tecnica_alto'] ?? null) : null,
        'seguimiento_pn'       => ($esPlanNacional && !$esBajoCiclo) ? ($validado['seguimiento_pn'] ?? null) : null,
    ]);

    foreach ($tutoresCreados as $numero => $tutorCreado) {
        $prematricula->tutores()->attach($tutorCreado->id, [
            'principal' => $numero === $principalNum,
            'orden'     => $numero,
        ]);
    }

    if ($esPlanNacional) {
    $request->validate([
        'doc_pase' => 'required_without:fisico_pase|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);
}

    $mapaDocumentos = [
    'doc_cedula'           => ['tipo' => 'cedula_estudiante', 'fisico' => 'fisico_cedula'],
    'doc_notas'            => ['tipo' => 'notas', 'fisico' => 'fisico_notas'],
    'doc_foto'             => ['tipo' => 'foto', 'fisico' => 'fisico_foto'],
    'doc_cedula_encargado' => ['tipo' => 'cedula_encargado', 'fisico' => 'fisico_cedula_encargado'],
    'doc_prueba_admision'  => ['tipo' => 'prueba_admision', 'fisico' => 'fisico_prueba_admision'],
];

if ($esPlanNacional) {
    $mapaDocumentos['doc_pase'] = ['tipo' => 'pase', 'fisico' => 'fisico_pase'];
}

    foreach ($mapaDocumentos as $campoFormulario => $info) {
        $esFisico = $info['fisico'] && $request->boolean($info['fisico']);

        if ($request->hasFile($campoFormulario)) {
            $archivo = $request->file($campoFormulario);
            $ruta = $archivo->store('documentos/' . $prematricula->id, 'local');
            \App\Models\Documento::create([
                'prematricula_id'  => $prematricula->id,
                'tipo'             => $info['tipo'],
                'entregado_fisico' => false,
                'nombre_original'  => $archivo->getClientOriginalName(),
                'ruta'             => $ruta,
            ]);
        } elseif ($esFisico) {
            \App\Models\Documento::create([
                'prematricula_id'  => $prematricula->id,
                'tipo'             => $info['tipo'],
                'entregado_fisico' => true,
                'nombre_original'  => null,
                'ruta'             => null,
            ]);
        }
    }

    try {
        $destinatarios = collect($tutoresCreados)->pluck('email')->filter()->unique();

        if (!empty($estudiante->email_mep)) {
            $destinatarios->push($estudiante->email_mep);
        }

        Mail::to($tutor->email)
            ->cc($destinatarios->reject(fn($email) => $email === $tutor->email)->all())
            ->send(new PrematriculaRecibida($prematricula));
    } catch (\Exception $e) {
        // Si el correo falla no interrumpimos el flujo
    }

   try {
    $prematricula->load(['estudiante', 'tutor', 'tutores', 'documentos', 'nivel', 'seccion', 'periodo', 'modalidad', 'carrera', 'user']);
    \App\Services\BoletaPdfBuilder::generar($prematricula);
} catch (\Exception $e) {
    // Si el PDF falla no interrumpimos el flujo

    }

    return redirect()->route('prematricula.index')
        ->with('success', '¡Prematrícula enviada correctamente! Código: ' . $prematricula->codigo . '. Podés descargar el PDF desde esta pantalla.');
}

public function descargarPdf(Prematricula $prematricula)
{
    if ($prematricula->user_id !== Auth::id()) {
        abort(403, 'No tenés permiso para descargar este documento.');
    }

    $prematricula->load(['estudiante', 'tutor', 'tutores', 'documentos', 'nivel', 'seccion', 'periodo', 'modalidad', 'carrera', 'user']);

    $ruta = \App\Services\BoletaPdfBuilder::generar($prematricula);

    return Storage::download($ruta, 'Matricula-' . $prematricula->codigo . '.pdf');
}

public function reenviarCorreo(Prematricula $prematricula)
{
    if ($prematricula->user_id !== Auth::id()) {
        abort(403, 'No tenés permiso para esta acción.');
    }

    $prematricula->load(['estudiante', 'tutor']);

    try {
        $destinatarios = collect([$prematricula->tutor->email]);

        if (!empty($prematricula->estudiante->email_mep)) {
            $destinatarios->push($prematricula->estudiante->email_mep);
        }

        Mail::to($destinatarios->first())
            ->cc($destinatarios->slice(1)->all())
            ->send(new PrematriculaRecibida($prematricula));

        return back()->with('success', 'Correo reenviado correctamente a ' . $destinatarios->implode(', '));
    } catch (\Exception $e) {
        return back()->with('error', 'No se pudo enviar el correo. Verificá la conexión a internet e intentá de nuevo.');
    }
}


}