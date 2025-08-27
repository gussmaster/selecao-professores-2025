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
Route::post('/avaliar/{id}', [InscricaoController::class, 'salvarAvaliacao'])->name('avaliar.salvar');
// Exibir a tela de seleção do cargo e classificação
//Route::get('/classificacao', [InscricaoController::class, 'classificacao'])->name('classificacao');
//Route::get('/exportar-classificacao-csv', [InscricaoController::class, 'exportarClassificacaoCSV'])->name('classificacao.export.csv');
//Route::get('/exportar-classificacao-pdf', [InscricaoController::class, 'exportarClassificacaoPDF'])->name('classificacao.export.pdf');

// Área do Candidato - Recurso de inscrição
Route::get('/recurso/login', [InscricaoController::class, 'recursoLoginForm'])->name('recurso.login.form');
Route::post('/recurso/login', [InscricaoController::class, 'recursoLogin'])->name('recurso.login');
Route::get('/recurso', [InscricaoController::class, 'recursoForm'])->name('recurso.form');
Route::post('/recurso/enviar', [InscricaoController::class, 'recursoEnviar'])->name('recurso.enviar');

// Área do Candidato - Recurso de Pontuação
//Route::get('/recurso-pontuacao/login', [InscricaoController::class, 'recursoPontuacaoLoginForm'])->name('recurso-pontuacao.login');
//Route::post('/recurso-pontuacao/login', [InscricaoController::class, 'recursoPontuacaoLogin']);
//Route::get('/recurso-pontuacao/form', [InscricaoController::class, 'recursoPontuacaoForm'])->name('recurso-pontuacao.form');
//Route::post('/recurso-pontuacao/solicitar', [InscricaoController::class, 'recursoPontuacaoSolicitar'])->name('recurso-pontuacao.solicitar');


// Área do Candidato - Recurso da Entrevista
//Route::get('/recurso-entrevista/login', [InscricaoController::class, 'recursoEntrevistaLoginForm'])->name('recurso-entrevista.login');
//Route::post('/recurso-entrevista/login', [InscricaoController::class, 'recursoEntrevistaLogin']);
//Route::get('/recurso-entrevista/form', [InscricaoController::class, 'recursoEntrevistaForm'])->name('recurso-entrevista.form');
//Route::post('/recurso-entrevista/solicitar', [InscricaoController::class, 'recursoEntrevistaSolicitar'])->name('recurso-entrevista.solicitar');







// Exibe formulário para buscar comprovante
Route::get('/segunda-via', [InscricaoController::class, 'segundaViaForm'])->name('segunda.via.form');

// Processa o CPF e redireciona para comprovante
Route::post('/segunda-via', [InscricaoController::class, 'segundaViaBuscar'])->name('segunda.via.buscar');

// Autenticação
Route::get('/login', [InscricaoController::class, 'loginForm'])->name('login');
Route::post('/login', [InscricaoController::class, 'login'])->name('login.submit');
Route::post('/logout', [InscricaoController::class, 'logout'])->name('logout');


// 🔥 Login para reenvio de documentação (acesso dos candidatos)
Route::get('/reenviar/login', [InscricaoController::class, 'showReenvioLogin'])->name('reenvio.login');
Route::post('/reenviar/login', [InscricaoController::class, 'processarReenvioLogin'])->name('reenvio.login.post');

// 🔐 Área dos candidatos para reenvio
Route::get('/reenviar', [InscricaoController::class, 'painelReenvio'])->name('reenvio.painel');
Route::post('/reenviar/upload', [InscricaoController::class, 'uploadReenvio'])->name('reenvio.upload');
Route::post('/reenviar/logout', [InscricaoController::class, 'logoutReenvio'])->name('reenvio.logout');


// Área de correção dos Professores
Route::get('/corrigir', [InscricaoController::class, 'loginCorrigir'])->name('corrigir.login');
Route::post('/corrigir', [InscricaoController::class, 'autenticarCorrigir'])->name('corrigir.autenticar');
Route::get('/corrigir/formulario', [InscricaoController::class, 'formularioCorrigir'])->name('corrigir.formulario');
Route::post('/corrigir/atualizar', [InscricaoController::class, 'atualizarCorrigir'])->name('corrigir.atualizar');
Route::get('/corrigir/logout', [InscricaoController::class, 'logoutCorrigir'])->name('corrigir.logout');


// Rotas administrativas (agrupadas com prefixo e middleware)
Route::prefix('admin')->middleware(['auth'])->group(function () {

    Route::get('/painel', [InscricaoController::class, 'painel'])->name('painel');
    Route::get('/exportar-inscricoes', [InscricaoController::class, 'exportarCSV'])->name('exportar.csv');
    Route::get('/download/{tipo}/{id}', [InscricaoController::class, 'downloadPrivado'])->name('admin.download');
    Route::get('/relatorio', [InscricaoController::class, 'relatorio'])->name('relatorio');
    Route::get('/pontuacao', [InscricaoController::class, 'buscarCPF'])->name('pontuacao.buscar');
    Route::post('/pontuacao', [InscricaoController::class, 'salvarPontuacao'])->name('pontuacao.salvar');

    Route::get('/classificacao', [InscricaoController::class, 'classificacao'])->name('classificacao');
    Route::get('/classificacao/pdf', [InscricaoController::class, 'classificacaoPdf'])->name('classificacao.pdf');

    // Desempate manual e geração do relatório final
    Route::get('/classificacao/desempate', [InscricaoController::class, 'telaDesempate'])->name('classificacao.desempate');
    Route::post('/classificacao/desempate', [InscricaoController::class, 'resolverDesempates'])->name('classificacao.desempate.resolver');
    Route::get('/classificacao/final/export', [InscricaoController::class, 'exportarClassificacaoFinal'])->name('classificacao.final.export');
   Route::get('/classificacao/final/export-csv', [InscricaoController::class, 'exportarClassificacaoFinalCsv'])->name('classificacao.final.export.csv');

    // Entrevistas
    Route::get('/entrevista', [InscricaoController::class, 'formEntrevista'])->name('entrevista.form');
    Route::post('/entrevista/buscar', [InscricaoController::class, 'buscarEntrevista'])->name('entrevista.buscar');
    Route::post('/entrevista/salvar/{id}', [InscricaoController::class, 'salvarEntrevista'])->name('entrevista.salvar');

    // Recursos gerais
    Route::get('/recursos', [InscricaoController::class, 'recursosAdmin'])->name('recursos.admin');
    Route::post('/recursos/{id}/analise', [InscricaoController::class, 'salvarAnaliseRecurso'])->name('recursos.analise');
    Route::get('/recursos/export/csv', [InscricaoController::class, 'exportarRecursosCSV'])->name('recursos.export.csv');
    Route::get('/recursos/export/pdf', [InscricaoController::class, 'exportarRecursosPDF'])->name('recursos.export.pdf');

    // Recursos de pontuação
    Route::get('/recursos-pontuacao', [InscricaoController::class, 'recursosPontuacaoAdmin'])->name('recursos-pontuacao.admin');
    Route::post('/recursos-pontuacao/{id}/analise', [InscricaoController::class, 'analiseRecursoPontuacao'])->name('recursos-pontuacao.analise');
    Route::get('/recursos-pontuacao/export/csv', [InscricaoController::class, 'exportarRecursosPontuacaoCSV'])->name('recursos-pontuacao.export.csv');
    Route::get('/recursos-pontuacao/export/pdf', [InscricaoController::class, 'exportarRecursosPontuacaoPDF'])->name('recursos-pontuacao.export.pdf');

    // Exportações seguras
    Route::get('/exportar-classificados-seguro-pdf', [InscricaoController::class, 'exportarClassificadosSeguroPdf'])->name('exportar.classificados.seguro.pdf');
    Route::get('/exportar-entrevista-seguro-pdf', [InscricaoController::class, 'exportarEntrevistaSeguroPdf'])->name('exportar.entrevista.seguro.pdf');

    // Recursos de entrevista
    Route::get('/recursos-entrevista', [InscricaoController::class, 'recursosEntrevistaAdmin'])->name('recursos-entrevista.admin');
    Route::post('/recursos-entrevista/{id}/analise', [InscricaoController::class, 'analiseRecursoEntrevista'])->name('recursos-entrevista.analise');
    Route::get('/recursos-entrevista/export/csv', [InscricaoController::class, 'exportarRecursosEntrevistaCSV'])->name('recursos-entrevista.export.csv');
    Route::get('/recursos-entrevista/export/pdf', [InscricaoController::class, 'exportarRecursosEntrevistaPDF'])->name('recursos-entrevista.export.pdf');
});
