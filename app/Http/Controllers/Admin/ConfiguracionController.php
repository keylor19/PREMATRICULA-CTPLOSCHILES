<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nivel;
use App\Models\Seccion;
use App\Models\SeccionTaller;
use App\Models\Carrera;
use App\Models\Modalidad;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $modalidades = Modalidad::where('activa', true)->get();
        $modalidadId = request('modalidad_id', $modalidades->first()?->id);
        $modalidadActual = $modalidades->firstWhere('id', $modalidadId);

        $niveles = Nivel::with([
            'secciones.talleres',
            'carreras',
        ])->where('modalidad_id', $modalidadId)
          ->get();

        return view('admin.configuracion.index', compact('modalidades', 'modalidadActual', 'niveles', 'modalidadId'));
    }

    public function guardarNivel(Request $request)
    {

   
        $validado = $request->validate([
            'modalidad_id'   => 'required|exists:modalidades,id',
            'numero'         => 'required|string|max:20',
            'nombre'         => 'required|string|max:100',
            'seccion_inicio' => 'nullable|integer|min:1',
            'seccion_fin'    => 'nullable|integer|min:1|gte:seccion_inicio',
        ]);

        $nivel = Nivel::updateOrCreate(
            [
                'modalidad_id' => $validado['modalidad_id'],
                'numero'       => $validado['numero'],
            ],
            [
                'nombre'         => $validado['nombre'],
                'seccion_inicio' => $validado['seccion_inicio'] ?? 1,
                'seccion_fin'    => $validado['seccion_fin'] ?? 1,
                'activo'         => true,
            ]
        );

        // Generar secciones para modalidades que las usan (Diurna y Plan Nacional)
$modalidad = Modalidad::find($validado['modalidad_id']);
if (in_array($modalidad->nombre, ['Diurna', 'Plan Nacional']) && $validado['seccion_inicio'] && $validado['seccion_fin']) {
    $nivel->generarSecciones();
}

        return back()->with('success', "Nivel {$nivel->nombre} configurado correctamente.");
    }

    // ===== MÉTODOS PARA DIURNA (secciones y talleres) =====

    public function guardarTallerSeccion(Request $request)
    {
        $validado = $request->validate([
            'seccion_id'  => 'required|exists:secciones,id',
            'grupo'       => 'required|in:A,B',
            'nombre'      => 'required|string|max:100',
            'capacidad'   => 'required|integer|min:1|max:100',
            'descripcion' => 'nullable|string|max:300',
        ]);

        SeccionTaller::updateOrCreate(
            ['seccion_id' => $validado['seccion_id'], 'grupo' => $validado['grupo']],
            ['nombre' => $validado['nombre'], 'capacidad' => $validado['capacidad'], 'descripcion' => $validado['descripcion'] ?? null]
        );

        return back()->with('success', 'Taller guardado correctamente.');
    }

    public function actualizarCapacidad(Request $request, SeccionTaller $seccionTaller)
    {
        $validado = $request->validate([
            'capacidad' => 'required|integer|min:1|max:500',
        ]);

        $seccionTaller->update(['capacidad' => $validado['capacidad']]);
        return back()->with('success', "Capacidad actualizada a {$validado['capacidad']} cupos.");
    }

    public function toggleSeccion(Seccion $seccion)
    {
        $seccion->update(['activa' => !$seccion->activa]);
        $estado = $seccion->activa ? 'activada' : 'desactivada';
        return back()->with('success', "Sección {$seccion->nombre} {$estado}.");
    }

    public function eliminarTallerSeccion(SeccionTaller $seccionTaller)
    {
        $seccionTaller->delete();
        return back()->with('success', 'Taller eliminado.');
    }

    // ===== MÉTODOS PARA NOCTURNA (carreras técnicas) =====

    public function guardarCarrera(Request $request)
    {
        $validado = $request->validate([
            'nivel_id'    => 'required|exists:niveles,id',
            'nombre'      => 'required|string|max:100',
            'capacidad'   => 'required|integer|min:1|max:500',
        ]);

        Carrera::create([
            'modalidad_id' => Nivel::find($validado['nivel_id'])->modalidad_id,
            'nivel_id'     => $validado['nivel_id'],
            'nombre'       => $validado['nombre'],
            'capacidad'    => $validado['capacidad'],
            'activa'       => true,
        ]);

        return back()->with('success', "Carrera \"{$validado['nombre']}\" agregada correctamente.");
    }

    public function actualizarCapacidadCarrera(Request $request, Carrera $carrera)
    {
        $validado = $request->validate([
            'capacidad' => 'required|integer|min:1|max:500',
        ]);

        $carrera->update(['capacidad' => $validado['capacidad']]);
        return back()->with('success', "Capacidad de {$carrera->nombre} actualizada a {$validado['capacidad']} cupos.");
    }

    public function toggleCarrera(Carrera $carrera)
    {
        $carrera->update(['activa' => !$carrera->activa]);
        $estado = $carrera->activa ? 'activada' : 'desactivada';
        return back()->with('success', "Carrera {$carrera->nombre} {$estado}.");
    }

    public function eliminarCarrera(Carrera $carrera)
    {
        if ($carrera->prematriculas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una carrera que ya tiene prematrículas registradas.');
        }
        $carrera->delete();
        return back()->with('success', 'Carrera eliminada.');
    }
}