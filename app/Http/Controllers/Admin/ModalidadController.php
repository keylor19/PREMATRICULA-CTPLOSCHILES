<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modalidad;
use App\Models\User;
use Illuminate\Http\Request;

class ModalidadController extends Controller
{
    public function index()
    {
        $modalidades = Modalidad::with('docentes')->get();
        $docentes = User::where('rol', 'docente')->orderBy('name')->get();
        return view('admin.modalidades.index', compact('modalidades', 'docentes'));
    }

    public function asignarDocente(Request $request)
    {
        $validado = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'modalidad_id' => 'required|exists:modalidades,id',
        ]);

        $docente = User::findOrFail($validado['user_id']);

        // Agregar la modalidad si no la tiene ya
        $docente->modalidades()->syncWithoutDetaching([$validado['modalidad_id']]);

        return back()->with('success', 'Modalidad asignada correctamente.');
    }

    public function quitarDocente(Request $request)
    {
        $validado = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'modalidad_id' => 'required|exists:modalidades,id',
        ]);

        $docente = User::findOrFail($validado['user_id']);
        $docente->modalidades()->detach($validado['modalidad_id']);

        return back()->with('success', 'Modalidad removida correctamente.');
    }
}
