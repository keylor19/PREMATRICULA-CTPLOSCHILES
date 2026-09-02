<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periodo;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    public function index()
    {
        $periodos = Periodo::latest()->get();
        $periodoActivo = Periodo::activo();
        return view('admin.periodos.index', compact('periodos', 'periodoActivo'));
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'anio'         => 'required|integer|min:2024|max:2100',
            'nombre'       => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
        ]);

        Periodo::create([
            'anio'         => $validado['anio'],
            'nombre'       => $validado['nombre'],
            'fecha_inicio' => $validado['fecha_inicio'],
            'fecha_fin'    => $validado['fecha_fin'],
            'activo'       => false,
        ]);

        return back()->with('success', 'Período creado correctamente.');
    }

    public function activar(Periodo $periodo)
    {
        // Desactivar todos los períodos primero
        Periodo::where('activo', true)->update(['activo' => false]);

        // Activar el seleccionado
        $periodo->update(['activo' => true]);

        return back()->with('success', "Período \"{$periodo->nombre}\" activado. Solo este período recibirá prematrículas.");
    }

    /**
     * Reabre un período vencido (o cualquier período) extendiendo su fecha de cierre.
     * A diferencia de activar(), esto sí permite que vuelva a recibir prematrículas
     * aunque su fecha_fin original ya haya pasado.
     */
    public function reabrir(Request $request, Periodo $periodo)
    {
        $validado = $request->validate([
            'nueva_fecha_fin' => 'required|date|after:today',
        ]);

        // Desactivar todos los períodos primero (solo puede haber uno activo a la vez)
        Periodo::where('activo', true)->update(['activo' => false]);

        $periodo->update([
            'fecha_fin' => $validado['nueva_fecha_fin'],
            'activo'    => true,
        ]);

        return back()->with('success', "Período \"{$periodo->nombre}\" reabierto hasta el " . $periodo->fecha_fin->format('d/m/Y') . ".");
    }

    public function cerrar(Periodo $periodo)
    {
        $periodo->update(['activo' => false]);
        return back()->with('success', "Período \"{$periodo->nombre}\" cerrado.");
    }

    public function destroy(Periodo $periodo)
    {
        if ($periodo->prematriculas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un período que ya tiene prematrículas registradas.');
        }

        $periodo->delete();
        return back()->with('success', 'Período eliminado.');
    }
}