<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prematricula;
use App\Models\Nivel;
use App\Models\Modalidad;
use App\Mail\PrematriculaDecidida;
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
            ->when($request->estado, function ($query) use ($request) {
                $query->where('estado', $request->estado);
            })
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
        $prematricula->load(['estudiante', 'tutor', 'documentos', 'user', 'nivel', 'seccion', 'modalidad', 'periodo']);
        return view('admin.prematriculas.show', compact('prematricula'));
    }

    public function edit(Prematricula $prematricula)
    {
        $prematricula->load(['estudiante', 'tutor', 'documentos', 'nivel', 'seccion', 'modalidad', 'periodo']);
        $niveles = Nivel::with(['secciones' => function($q) {
            $q->where('activa', true)->orderBy('numero');
        }])->where('activo', true)->get();

        return view('admin.prematriculas.edit', compact('prematricula', 'niveles'));
    }

    public function update(Request $request, Prematricula $prematricula)
    {
        $validado = $request->validate([
            'est_nombre'       => 'required|string|max:100',
            'est_apellido'     => 'required|string|max:100',
            'est_cedula'       => 'required|string|unique:estudiantes,cedula,' . $prematricula->estudiante_id,
            'est_nacimiento'   => 'required|date',
            'est_genero'       => 'nullable|string',
            'est_nacionalidad' => 'nullable|string',
            'est_direccion'    => 'required|string',
            'est_salud'        => 'nullable|string',

            'tut_nombre'    => 'required|string|max:150',
            'tut_relacion'  => 'required|string',
            'tut_cedula'    => 'required|string',
            'tut_telefono'  => 'required|string',
            'tut_telefono2' => 'nullable|string',
            'tut_email'     => 'required|email',
            'tut_ocupacion' => 'nullable|string',
            'tut_dir'       => 'nullable|string',

            'nivel_id'              => 'required|exists:niveles,id',
            'seccion_id'            => 'nullable|exists:secciones,id',
            'grupo_taller'          => 'required|in:A,B',
            'colegio_procedencia'   => 'required|string',
            'anio_cursado_anterior' => 'required|string',
        ]);

        // Actualizar estudiante
        $prematricula->estudiante->update([
            'nombre'           => $validado['est_nombre'],
            'apellido'         => $validado['est_apellido'],
            'cedula'           => $validado['est_cedula'],
            'fecha_nacimiento' => $validado['est_nacimiento'],
            'genero'           => $validado['est_genero'] ?? null,
            'nacionalidad'     => $validado['est_nacionalidad'] ?? null,
            'direccion'        => $validado['est_direccion'],
            'condicion_salud'  => $validado['est_salud'] ?? null,
        ]);

        // Actualizar tutor
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

        // Actualizar prematrícula
        $prematricula->update([
            'nivel_id'              => $validado['nivel_id'],
            'seccion_id'            => $validado['seccion_id'] ?? null,
            'grupo_taller'          => $validado['grupo_taller'],
            'colegio_procedencia'   => $validado['colegio_procedencia'],
            'anio_cursado_anterior' => $validado['anio_cursado_anterior'],
        ]);

        return redirect()->route('admin.prematriculas.show', $prematricula)
            ->with('success', 'Prematrícula actualizada correctamente.');
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
            ->with('success', 'Prematrícula eliminada correctamente. El cupo ha sido liberado.');
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
        return back()->with('error', "No hay prematrículas registradas para {$periodo->nombre} — {$modalidad->nombre}.");
    }

    $nombre = 'prematriculas-' . str($periodo->nombre)->slug() . '-' . str($modalidad->nombre)->slug() . '.xlsx';

    return Excel::download(
        new PrematriculasExport($validado['periodo_id'], $validado['modalidad_id']),
        $nombre
    );

}



    public function decidir(Request $request, Prematricula $prematricula)
    {
        $validado = $request->validate([
            'estado'     => 'required|in:aprobada,rechazada',
            'nota_admin' => 'nullable|string|max:500',
        ]);

        $prematricula->update([
            'estado'         => $validado['estado'],
            'nota_admin'     => $validado['nota_admin'] ?? null,
            'fecha_decision' => now(),
        ]);

    try {
    $destinatarios = collect([$prematricula->tutor->email]);

    if (!empty($prematricula->estudiante->email_mep)) {
        $destinatarios->push($prematricula->estudiante->email_mep);
    }

    Mail::to($destinatarios->first())
        ->cc($destinatarios->slice(1)->all())
        ->send(new PrematriculaDecidida($prematricula));
} catch (\Exception $e) {
    // Si el correo falla no interrumpimos el flujo
}

        

        return back()->with('success', 'Decisión guardada y notificación enviada.');
    }
}