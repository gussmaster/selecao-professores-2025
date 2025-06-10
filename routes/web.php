<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InscricaoController;
use Illuminate\Support\Facades\Auth;

// Rotas públicas
Route::get('/', [InscricaoController::class, 'verificarCPF'])->name('verificar.cpf');
Route::post('/verificar', [InscricaoController::class, 'checarCPF'])->name('checar.cpf');
Route::get('/formulario/{cpf}', [InscricaoController::class, 'formulario'])->name('formulario.inscricao');
Route::post('/confirmar', [InscricaoController::class, 'confirmar'])->name('formulario.confirmar');
Route::get('/comprovante/{id}', [InscricaoController::class, 'comprovante'])->name('comprovante.inscricao');
Route::get('/comprovante/{id}/pdf', [InscricaoController::class, 'gerarPDF'])->name('comprovante.pdf');
Route::get('/validar', [InscricaoController::class, 'validarHashForm'])->name('validar.form');
Route::post('/validar', [InscricaoController::class, 'validarHashResultado'])->name('validar.resultado');

// Autenticação
Route::get('/login', [InscricaoController::class, 'loginForm'])->name('login');
Route::post('/login', [InscricaoController::class, 'login'])->name('login.submit');
Route::post('/logout', [InscricaoController::class, 'logout'])->name('logout');

// Rotas administrativas (agrupadas com prefixo e middleware)
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/painel', [InscricaoController::class, 'painel'])->name('painel');
    Route::get('/exportar-inscricoes', [InscricaoController::class, 'exportarCSV'])->name('exportar.csv');
    Route::get('/download/{tipo}/{id}', [InscricaoController::class, 'downloadPrivado'])->name('admin.download');
    Route::get('/relatorio', [InscricaoController::class, 'relatorio'])->name('relatorio');
    Route::get('/pontuacao', [InscricaoController::class, 'buscarCPF'])->name('pontuacao.buscar');
    Route::post('/pontuacao', [InscricaoController::class, 'salvarPontuacao'])->name('pontuacao.salvar');

});
