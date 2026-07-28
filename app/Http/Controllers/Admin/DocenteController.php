<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class DocenteController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('rol')->orderBy('name')->get();
        return view('admin.docentes.index', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'rol'      => 'required|in:docente,admin',
        ]);

        User::create([
            'name'     => $validado['name'],
            'email'    => $validado['email'],
            'password' => Hash::make($validado['password']),
            'rol'      => $validado['rol'],
        ]);

        $tipoLabel = $validado['rol'] === 'admin' ? 'Administrador' : 'Docente';

        return back()->with('success', "Cuenta de {$tipoLabel} creada correctamente.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No podés eliminar tu propia cuenta.');
        }

        $user->delete();
        return back()->with('success', 'Cuenta eliminada correctamente.');
    }
}