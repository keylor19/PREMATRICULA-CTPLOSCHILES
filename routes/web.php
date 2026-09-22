<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PrematriculaController;
use App\Http\Controllers\Admin\PrematriculaAdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    /** @var \App\Models\User $usuario */
    $usuario = Auth::user();

    if ($usuario->esAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('prematricula.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===== Rutas del docente (prematrícula) =====
Route::middleware(['auth'])->group(function () {
    Route::get('/prematricula', [PrematriculaController::class, 'index'])->name('prematricula.index');
    Route::get('/prematricula/crear', [PrematriculaController::class, 'create'])->name('prematricula.create');
    Route::post('/prematricula', [PrematriculaController::class, 'store'])->name('prematricula.store');
    Route::get('/prematricula/ratificar', [PrematriculaController::class, 'ratificarIndex'])->name('prematricula.ratificar.index');
    Route::get('/prematricula/ratificar/{estudiante}', [PrematriculaController::class, 'ratificarForm'])->name('prematricula.ratificar.form');
    Route::post('/prematricula/ratificar/{estudiante}', [PrematriculaController::class, 'ratificarStore'])->name('prematricula.ratificar.store');
    Route::get('/prematricula/{prematricula}/pdf', [PrematriculaController::class, 'descargarPdf'])->name('prematricula.pdf');
    Route::post('/prematricula/{prematricula}/reenviar-correo', [PrematriculaController::class, 'reenviarCorreo'])->name('prematricula.reenviarCorreo');
});

// ===== Rutas de administración =====
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Exportar Excel (debe ir antes de {prematricula} para no confundirse)
    Route::get('/prematriculas/exportar/excel', [PrematriculaAdminController::class, 'exportarExcel'])->name('prematriculas.exportar');

    // Prematrículas
    Route::get('/prematriculas', [PrematriculaAdminController::class, 'index'])->name('prematriculas.index');
    Route::get('/prematriculas/{prematricula}', [PrematriculaAdminController::class, 'show'])->name('prematriculas.show');
    Route::get('/prematriculas/{prematricula}/editar', [PrematriculaAdminController::class, 'edit'])->name('prematriculas.edit');
    Route::put('/prematriculas/{prematricula}', [PrematriculaAdminController::class, 'update'])->name('prematriculas.update');
    Route::delete('/prematriculas/{prematricula}', [PrematriculaAdminController::class, 'destroy'])->name('prematriculas.destroy');
    //Route::post('/prematriculas/{prematricula}/decidir', [PrematriculaAdminController::class, 'decidir'])->name('prematriculas.decidir');

    // Configuración — general
    Route::get('/configuracion', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion/nivel', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'guardarNivel'])->name('configuracion.nivel');

    // Configuración — Diurna (secciones y talleres)
    Route::post('/configuracion/seccion/{seccion}/toggle', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'toggleSeccion'])->name('configuracion.seccion.toggle');
    Route::post('/configuracion/seccion/taller', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'guardarTallerSeccion'])->name('configuracion.seccion.taller');
    Route::patch('/configuracion/seccion/taller/{seccionTaller}/capacidad', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'actualizarCapacidad'])->name('configuracion.seccion.taller.capacidad');
    Route::delete('/configuracion/seccion/taller/{seccionTaller}', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'eliminarTallerSeccion'])->name('configuracion.seccion.taller.eliminar');

    // Configuración — Nocturna (carreras técnicas)
    Route::post('/configuracion/carrera', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'guardarCarrera'])->name('configuracion.carrera');
    Route::patch('/configuracion/carrera/{carrera}/capacidad', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'actualizarCapacidadCarrera'])->name('configuracion.carrera.capacidad');
    Route::post('/configuracion/carrera/{carrera}/toggle', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'toggleCarrera'])->name('configuracion.carrera.toggle');
    Route::delete('/configuracion/carrera/{carrera}', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'eliminarCarrera'])->name('configuracion.carrera.eliminar');

    // Gestión de usuarios
    Route::get('/docentes', [\App\Http\Controllers\Admin\DocenteController::class, 'index'])->name('docentes.index');
    Route::post('/docentes', [\App\Http\Controllers\Admin\DocenteController::class, 'store'])->name('docentes.store');
    Route::delete('/docentes/{user}', [\App\Http\Controllers\Admin\DocenteController::class, 'destroy'])->name('docentes.destroy');

    // Períodos de prematrícula
    Route::get('/periodos', [\App\Http\Controllers\Admin\PeriodoController::class, 'index'])->name('periodos.index');
    Route::post('/periodos', [\App\Http\Controllers\Admin\PeriodoController::class, 'store'])->name('periodos.store');
    Route::post('/periodos/{periodo}/activar', [\App\Http\Controllers\Admin\PeriodoController::class, 'activar'])->name('periodos.activar');
    Route::post('/periodos/{periodo}/reabrir', [\App\Http\Controllers\Admin\PeriodoController::class, 'reabrir'])->name('periodos.reabrir');
    Route::post('/periodos/{periodo}/cerrar', [\App\Http\Controllers\Admin\PeriodoController::class, 'cerrar'])->name('periodos.cerrar');
    Route::delete('/periodos/{periodo}', [\App\Http\Controllers\Admin\PeriodoController::class, 'destroy'])
        ->middleware('superadmin')
        ->name('periodos.destroy');

    // Modalidades
    Route::get('/modalidades', [\App\Http\Controllers\Admin\ModalidadController::class, 'index'])->name('modalidades.index');
    Route::post('/modalidades/asignar', [\App\Http\Controllers\Admin\ModalidadController::class, 'asignarDocente'])->name('modalidades.asignar');
    Route::post('/modalidades/quitar', [\App\Http\Controllers\Admin\ModalidadController::class, 'quitarDocente'])->name('modalidades.quitar');
});

require __DIR__.'/auth.php';