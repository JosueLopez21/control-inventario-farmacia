<?php

use App\Http\Controllers\AlertaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BienvenidaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JuegoController;
use App\Http\Controllers\LaboratorioController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/bienvenida', [BienvenidaController::class, 'index'])
    ->middleware('guest')
    ->name('bienvenida');

Route::post('/demo/{rol}', [DemoController::class, 'entrar'])
    ->middleware(['guest', 'throttle:6,1'])
    ->name('demo.entrar');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/simulador-venta', [DashboardController::class, 'simular'])
        ->middleware('rol:administrador,farmaceutico')
        ->name('simulador.venta');

    Route::get('/perfil/password', [PerfilController::class, 'editPassword'])->name('perfil.password');
    Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('perfil.password.update');

    // Lectura disponible para todos los roles autenticados.
    Route::get('/medicamentos', [MedicamentoController::class, 'index'])->name('medicamentos.index');
    Route::get('/lotes', [LoteController::class, 'index'])->name('lotes.index');
    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');

    // Juego educativo disponible para todos los roles.
    Route::get('/juego', [JuegoController::class, 'index'])->name('juego.index');
    Route::get('/juego/pregunta', [JuegoController::class, 'pregunta'])->name('juego.pregunta');

    // Inventario: administrador y farmacéutico.
    Route::middleware('rol:administrador,farmaceutico')->group(function () {
        Route::get('/medicamentos/crear', [MedicamentoController::class, 'create'])->name('medicamentos.create');
        Route::post('/medicamentos', [MedicamentoController::class, 'store'])->name('medicamentos.store');
        Route::get('/medicamentos-buscar/json', [MedicamentoController::class, 'buscar'])->name('medicamentos.buscar');

        Route::get('/lotes/crear', [LoteController::class, 'create'])->name('lotes.create');
        Route::post('/lotes', [LoteController::class, 'store'])->name('lotes.store');

        Route::resource('categorias', CategoriaController::class)->except(['show', 'destroy']);
        Route::resource('laboratorios', LaboratorioController::class)->except(['show', 'destroy']);

        Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
        Route::get('/ventas/nueva', [VentaController::class, 'create'])->name('ventas.create');
        Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
        Route::get('/ventas/{venta}/imprimir', [VentaController::class, 'print'])->name('ventas.print');
        Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    });

    // El comodín de medicamentos se registra al final para no capturar /crear.
    Route::get('/medicamentos/{medicamento}', [MedicamentoController::class, 'show'])->name('medicamentos.show');

    Route::middleware('rol:administrador,farmaceutico')->group(function () {
        Route::get('/medicamentos/{medicamento}/editar', [MedicamentoController::class, 'edit'])->name('medicamentos.edit');
        Route::put('/medicamentos/{medicamento}', [MedicamentoController::class, 'update'])->name('medicamentos.update');

        Route::get('/lotes/{lote}/editar', [LoteController::class, 'edit'])->name('lotes.edit');
        Route::put('/lotes/{lote}', [LoteController::class, 'update'])->name('lotes.update');
    });

    // Solo administrador: eliminaciones y gestión de usuarios.
    Route::middleware('rol:administrador')->group(function () {
        Route::delete('/medicamentos/{medicamento}', [MedicamentoController::class, 'destroy'])->name('medicamentos.destroy');
        Route::delete('/lotes/{lote}', [LoteController::class, 'destroy'])->name('lotes.destroy');
        Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
        Route::delete('/laboratorios/{laboratorio}', [LaboratorioController::class, 'destroy'])->name('laboratorios.destroy');

        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
        Route::post('/usuarios/{usuario}/password', [UsuarioController::class, 'resetPassword'])->name('usuarios.password');
    });
});
