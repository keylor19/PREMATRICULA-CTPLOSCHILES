<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prematricula;
use App\Models\Nivel;
use App\Models\Modalidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Exports\PrematriculasExport;
use App\Models\Periodo;
use Maatwebsite\Excel\Facades\Excel;

class PrematriculaAdminController extends Controller
{
    public function index(Request $request)
    {
        $solicitudes = Prematricula::with(['estudiante', 'tutor', 'nivel', 'seccion', 'modalidad', 'periodo'])
            ->when($request->modalidad_id, function ($query) use ($request) {
                $query->where('modalidad_id', $request->modalidad_id);
            })
            ->when($request->buscar, function ($query) use ($request) {
                $query->whereHas('estudiante', function ($q) use ($request) {
                    $q->where('nombre', 'like', '%' . $request->buscar . '%')
                      ->orWhere('apellido', 'like', '%' . $request->buscar . '%')
                      ->orWhere('cedula', 'like', '%' . $request->buscar . '%');
                });
            })
            ->latest()
            ->paginate(15);

        $modalidades = Modalidad::where('activa', true)->get();

        return view('admin.prematriculas.index', compact('solicitudes', 'modalidades'));
    }

    public function show(Prematricula $prematricula)
    {
        $prematricula->load(['estudiante', 'estudiante.familiares', 'tutor', 'tutores', 'documentos', 'user', 'nivel', 'seccion', 'modalidad', 'periodo', 'carrera']);
        return view('admin.prematriculas.show', compact('prematricula'));
    }

    public function edit(Prematricula $prematricula)
    {
        $prematricula->load(['estudiante', 'estudiante.familiares', 'tutor', 'tutores', 'documentos', 'nivel', 'seccion', 'modalidad', 'periodo', 'carrera']);

        // Traer los niveles SOLO de la modalidad de esta matrícula, con sus secciones y carreras
        $niveles = Nivel::with([
                'secciones' => function($q) {
                    $q->where('activa', true)->orderBy('numero');
                },
                'carreras' => function($q) {
                    $q->where('activa', true)->orderBy('nombre');
                },
            ])
            ->where('activo', true)
            ->where('modalidad_id', $prematricula->modalidad_id)
            ->get();

        return view('admin.prematriculas.edit', compact('prematricula', 'niveles'));
    }

    public function update(Request $request, Prematricula $prematricula)
    {
        $prematricula->load(['estudiante', 'estudiante.familiares', 'tutor', 'tutores', 'documentos', 'modalidad', 'nivel']);

        $esPlanNacional = $prematricula->modalidad && $prematricula->modalidad->nombre === 'Plan Nacional';
        $esNocturna     = $prematricula->modalidad && $prematricula->modalidad->nombre === 'Nocturna';

        // Edad calculada desde la fecha que llega en el request
        $edad = null;
        if ($request->filled('est_nacimiento')) {
            try { $edad = \Carbon\Carbon::parse($request->est_nacimiento)->age; } catch (\Exception $e) { $edad = null; }
        }
        $esNocturnaMayor = $esNocturna && $edad !== null && $edad >= 18;

        $reglas = [
            'est_nombre'       => 'required|string|max:100',
            'est_apellido'     => 'required|string|max:100',
            'est_cedula'       => 'required|string|unique:estudiantes,cedula,' . $prematricula->estudiante_id,
            'est_nacimiento'   => 'required|date',
            'est_genero'       => 'nullable|string',
            'est_nacionalidad' => 'nullable|string',
            'est_direccion'    => 'required|string',
            'est_email_mep'    => 'nullable|email',
            'est_email_personal' => 'nullable|email',
            'est_telefono'       => 'nullable|string|max:50',

            'est_adecuacion'           => 'nullable|string',
            'est_tipo_discapacidad'    => 'nullable|string|max:150',
            'est_boleta_ubicacion'     => 'nullable|in:Sí,No',
            'est_nivel_funcionamiento' => 'nullable|string',

            'nivel_id'              => 'required|exists:niveles,id',
            'seccion_id'            => 'nullable|exists:secciones,id',
            'grupo_taller'          => 'nullable|in:A,B',
            'carrera_id'            => 'nullable|exists:carreras,id',
            'colegio_procedencia'   => 'required|string',
            'anio_cursado_anterior' => 'required|string',

            'tecnica_1'             => 'nullable|string|max:150',
            'tecnica_2'             => 'nullable|string|max:150',
            'formacion_vocacional'  => 'nullable|string|max:150',
            'tecnica_alto'          => 'nullable|string|max:150',
            'seguimiento_pn'        => 'nullable|string',

            // Padre / madre (opcionales)
            'padre_nombre'    => 'nullable|string|max:150',
            'padre_cedula'    => 'nullable|string|max:50',
            'padre_telefono'  => 'nullable|string|max:50',
            'padre_telefono2' => 'nullable|string|max:50',
            'padre_email'     => 'nullable|email',
            'padre_ocupacion' => 'nullable|string|max:150',
            'padre_direccion' => 'nullable|string',

            'madre_nombre'    => 'nullable|string|max:150',
            'madre_cedula'    => 'nullable|string|max:50',
            'madre_telefono'  => 'nullable|string|max:50',
            'madre_telefono2' => 'nullable|string|max:50',
            'madre_email'     => 'nullable|email',
            'madre_ocupacion' => 'nullable|string|max:150',
            'madre_direccion' => 'nullable|string',

            // Encargados 2 y 3 (opcionales)
            'tut2_nombre'    => 'nullable|string|max:150',
            'tut2_relacion'  => 'nullable|string',
            'tut2_cedula'    => 'nullable|string',
            'tut2_telefono'  => 'nullable|string',
            'tut2_telefono2' => 'nullable|string',
            'tut2_email'     => 'nullable|email',
            'tut2_ocupacion' => 'nullable|string',
            'tut2_dir'       => 'nullable|string',

            'tut3_nombre'    => 'nullable|string|max:150',
            'tut3_relacion'  => 'nullable|string',
            'tut3_cedula'    => 'nullable|string',
            'tut3_telefono'  => 'nullable|string',
            'tut3_telefono2' => 'nullable|string',
            'tut3_email'     => 'nullable|email',
            'tut3_ocupacion' => 'nullable|string',
            'tut3_dir'       => 'nullable|string',

            // Reemplazo de documentos (todos opcionales)
            'doc_cedula'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_notas'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_foto'             => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'doc_cedula_encargado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_prueba_admision'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_pase'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        // Encargado 1: obligatorio salvo Nocturna mayor de edad
        if ($esNocturnaMayor) {
            $reglas['est_email_personal'] = 'required|email';
            $reglas['est_telefono']       = 'required|string|max:50';
            $reglas['tut_nombre']    = 'nullable|string|max:150';
            $reglas['tut_relacion']  = 'nullable|string';
            $reglas['tut_cedula']    = 'nullable|string';
            $reglas['tut_telefono']  = 'nullable|string';
            $reglas['tut_telefono2'] = 'nullable|string';
            $reglas['tut_email']     = 'nullable|email';
            $reglas['tut_ocupacion'] = 'nullable|string';
            $reglas['tut_dir']       = 'nullable|string';
        } else {
            $reglas['tut_nombre']    = 'required|string|max:150';
            $reglas['tut_relacion']  = 'required|string';
            $reglas['tut_cedula']    = 'required|string';
            $reglas['tut_telefono']  = 'required|string';
            $reglas['tut_telefono2'] = 'nullable|string';
            $reglas['tut_email']     = 'required|email';
            $reglas['tut_ocupacion'] = 'nullable|string';
            $reglas['tut_dir']       = 'nullable|string';
        }

        $validado = $request->validate($reglas);

        // Correo MEP fallback
        $emailMep = $validado['est_email_mep'] ?? null;
        if (empty($emailMep)) {
            $emailMep = $validado['est_cedula'] . '@est.mep.go.cr';
        }

        // ===== Actualizar estudiante =====
        $prematricula->estudiante->update([
            'nombre'               => $validado['est_nombre'],
            'apellido'             => $validado['est_apellido'],
            'cedula'               => $validado['est_cedula'],
            'fecha_nacimiento'     => $validado['est_nacimiento'],
            'genero'               => $validado['est_genero'] ?? null,
            'nacionalidad'         => $validado['est_nacionalidad'] ?? null,
            'direccion'            => $validado['est_direccion'],
            'email_mep'            => $emailMep,
            'email_personal'       => $validado['est_email_personal'] ?? null,
            'telefono'             => $validado['est_telefono'] ?? null,
            'adecuacion'           => $validado['est_adecuacion'] ?? null,
            'tipo_discapacidad'    => $validado['est_tipo_discapacidad'] ?? null,
            'boleta_ubicacion'     => $validado['est_boleta_ubicacion'] ?? null,
            'nivel_funcionamiento' => $validado['est_nivel_funcionamiento'] ?? null,
        ]);

        // ===== Padre / Madre (borrar y recrear según lo enviado) =====
        \App\Models\Familiar::where('estudiante_id', $prematricula->estudiante_id)->delete();

        if (!$esNocturnaMayor) {
            if (!empty($validado['padre_nombre'])) {
                \App\Models\Familiar::create([
                    'estudiante_id'       => $prematricula->estudiante_id,
                    'tipo'                => 'padre',
                    'nombre_completo'     => $validado['padre_nombre'],
                    'cedula'              => $validado['padre_cedula'] ?? null,
                    'telefono_principal'  => $validado['padre_telefono'] ?? null,
                    'telefono_secundario' => $validado['padre_telefono2'] ?? null,
                    'email'               => $validado['padre_email'] ?? null,
                    'ocupacion'           => $validado['padre_ocupacion'] ?? null,
                    'direccion'           => $validado['padre_direccion'] ?? null,
                ]);
            }
            if (!empty($validado['madre_nombre'])) {
                \App\Models\Familiar::create([
                    'estudiante_id'       => $prematricula->estudiante_id,
                    'tipo'                => 'madre',
                    'nombre_completo'     => $validado['madre_nombre'],
                    'cedula'              => $validado['madre_cedula'] ?? null,
                    'telefono_principal'  => $validado['madre_telefono'] ?? null,
                    'telefono_secundario' => $validado['madre_telefono2'] ?? null,
                    'email'               => $validado['madre_email'] ?? null,
                    'ocupacion'           => $validado['madre_ocupacion'] ?? null,
                    'direccion'           => $validado['madre_direccion'] ?? null,
                ]);
            }
        }

        // ===== Encargados =====
        if ($esNocturnaMayor) {
            // El estudiante mayor de edad es su propio contacto
            $prematricula->tutor->update([
                'nombre_completo'     => $validado['est_nombre'] . ' ' . $validado['est_apellido'],
                'relacion'            => 'Estudiante mayor de edad',
                'cedula'              => $validado['est_cedula'],
                'telefono_principal'  => $validado['est_telefono'],
                'telefono_secundario' => null,
                'email'               => $validado['est_email_personal'],
                'ocupacion'           => null,
                'direccion'           => $validado['est_direccion'],
            ]);

            // Quitar encargados 2 y 3 si existían
            $extraIds = $prematricula->tutores->where('id', '!=', $prematricula->tutor_id)->pluck('id');
            $prematricula->tutores()->detach($extraIds);
            \App\Models\Tutor::whereIn('id', $extraIds)->delete();

        } else {
            // Encargado 1 (principal): actualizar el tutor existente
            $prematricula->tutor->update([
                'nombre_completo'     => $validado['tut_nombre'],
                'relacion'            => $validado['tut_relacion'],
                'cedula'              => $validado['tut_cedula'],
                'telefono_principal'  => $validado['tut_telefono'],
                'telefono_secundario' => $validado['tut_telefono2'] ?? null,
                'email'               => $validado['tut_email'],
                'ocupacion'           => $validado['tut_ocupacion'] ?? null,
                'direccion'           => $validado['tut_dir'] ?? null,
            ]);

            // Encargados 2 y 3: los borramos y recreamos según lo enviado
            $extraIds = $prematricula->tutores->where('id', '!=', $prematricula->tutor_id)->pluck('id');
            $prematricula->tutores()->detach($extraIds);
            \App\Models\Tutor::whereIn('id', $extraIds)->delete();

            foreach (['tut2' => 2, 'tut3' => 3] as $prefijo => $num) {
                if (!empty($validado[$prefijo . '_nombre'])) {
                    $nuevo = \App\Models\Tutor::create([
                        'user_id'             => $prematricula->user_id,
                        'nombre_completo'     => $validado[$prefijo . '_nombre'],
                        'relacion'            => $validado[$prefijo . '_relacion'] ?? null,
                        'cedula'              => $validado[$prefijo . '_cedula'] ?? null,
                        'telefono_principal'  => $validado[$prefijo . '_telefono'] ?? null,
                        'telefono_secundario' => $validado[$prefijo . '_telefono2'] ?? null,
                        'email'               => $validado[$prefijo . '_email'] ?? null,
                        'ocupacion'           => $validado[$prefijo . '_ocupacion'] ?? null,
                        'direccion'           => $validado[$prefijo . '_dir'] ?? null,
                    ]);
                    $prematricula->tutores()->attach($nuevo->id, ['principal' => false, 'orden' => $num]);
                }
            }

            // Asegurar que el principal esté marcado en el pivote
            $prematricula->tutores()->updateExistingPivot($prematricula->tutor_id, ['principal' => true, 'orden' => 1]);
        }

        // ===== Actualizar prematrícula =====
        $prematricula->update([
            'nivel_id'              => $validado['nivel_id'],
            'seccion_id'            => $validado['seccion_id'] ?? null,
            'grupo_taller'          => $validado['grupo_taller'] ?? null,
            'carrera_id'            => $validado['carrera_id'] ?? null,
            'colegio_procedencia'   => $validado['colegio_procedencia'],
            'anio_cursado_anterior' => $validado['anio_cursado_anterior'],
            'tecnica_1'             => $validado['tecnica_1'] ?? null,
            'tecnica_2'             => $validado['tecnica_2'] ?? null,
            'formacion_vocacional'  => $validado['formacion_vocacional'] ?? null,
            'tecnica_3'             => $validado['tecnica_alto'] ?? null,
            'seguimiento_pn'        => $validado['seguimiento_pn'] ?? null,
        ]);

        // ===== Reemplazo de documentos digitales =====
        $mapaDocumentos = [
            'doc_cedula'           => 'cedula_estudiante',
            'doc_notas'            => 'notas',
            'doc_foto'             => 'foto',
            'doc_cedula_encargado' => 'cedula_encargado',
            'doc_prueba_admision'  => 'prueba_admision',
            'doc_pase'             => 'pase',
        ];

        foreach ($mapaDocumentos as $campo => $tipo) {
            if ($request->hasFile($campo)) {
                $docExistente = $prematricula->documentos->firstWhere('tipo', $tipo);

                // Si el existente era físico, no lo tocamos (regla: los físicos quedan como están)
                if ($docExistente && $docExistente->entregado_fisico) {
                    continue;
                }

                $archivo = $request->file($campo);
                $ruta = $archivo->store('documentos/' . $prematricula->id, 'local');

                if ($docExistente) {
                    // Borrar archivo viejo y actualizar
                    if ($docExistente->ruta) {
                        Storage::delete($docExistente->ruta);
                    }
                    $docExistente->update([
                        'entregado_fisico' => false,
                        'nombre_original'  => $archivo->getClientOriginalName(),
                        'ruta'             => $ruta,
                    ]);
                } else {
                    // No existía: crear nuevo
                    \App\Models\Documento::create([
                        'prematricula_id'  => $prematricula->id,
                        'tipo'             => $tipo,
                        'entregado_fisico' => false,
                        'nombre_original'  => $archivo->getClientOriginalName(),
                        'ruta'             => $ruta,
                    ]);
                }
            }
        }

        return redirect()->route('admin.prematriculas.show', $prematricula)
            ->with('success', 'Matrícula actualizada correctamente.');
    }

    public function destroy(Prematricula $prematricula)
    {
        // Borrar documentos del storage
        foreach ($prematricula->documentos as $doc) {
            Storage::delete($doc->ruta);
        }

        // Borrar el PDF si existe
        Storage::delete('pdfs/' . $prematricula->codigo . '.pdf');

        $estudianteId = $prematricula->estudiante_id;
        $tutorId      = $prematricula->tutor_id;

        // Borrar la prematrícula (documentos se borran por cascade)
        $prematricula->delete();

        // Borrar estudiante y tutor
        \App\Models\Estudiante::find($estudianteId)?->delete();
        \App\Models\Tutor::find($tutorId)?->delete();

        return redirect()->route('admin.prematriculas.index')
            ->with('success', 'Matrícula eliminada correctamente. El cupo ha sido liberado.');
    }

    public function exportarExcel(Request $request)
    {
        $validado = $request->validate([
            'periodo_id'   => 'required|exists:periodos,id',
            'modalidad_id' => 'required|exists:modalidades,id',
        ]);

        $periodo   = Periodo::find($validado['periodo_id']);
        $modalidad = Modalidad::find($validado['modalidad_id']);

        $total = Prematricula::where('periodo_id', $validado['periodo_id'])
            ->where('modalidad_id', $validado['modalidad_id'])
            ->count();

        if ($total === 0) {
            return back()->with('error', "No hay matrículas registradas para {$periodo->nombre} — {$modalidad->nombre}.");
        }

        $nombre = 'Matriculas-' . str($periodo->nombre)->slug() . '-' . str($modalidad->nombre)->slug() . '.xlsx';

        return Excel::download(
            new PrematriculasExport($validado['periodo_id'], $validado['modalidad_id']),
            $nombre
        );
    }
}