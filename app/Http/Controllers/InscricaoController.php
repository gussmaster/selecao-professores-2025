<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\Desempate;

class InscricaoController extends Controller
{

    //validacao de cpf

    public static function validaCPF($cpf)
{
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }

    return true;
}

//fim validacao cpf

//recurso de pontuação


// Exibe o login (CPF + data nascimento)
public function recursoPontuacaoLoginForm() {
    return view('recursos_pontuacao.login');
}

// Valida login do candidato
public function recursoPontuacaoLogin(Request $request) {
    $request->validate([
        'cpf' => 'required',
        'data_nascimento' => 'required|date',
    ]);

    $cpf = preg_replace('/\D/', '', $request->cpf);

    $inscricao = DB::table('inscricoes')
        ->where('cpf', $cpf)
        ->where('data_nascimento', $request->data_nascimento)
        ->where('status_avaliacao', 'deferido')
        ->first();

    if (!$inscricao) {
        return back()->withErrors(['cpf' => 'Dados inválidos ou sua inscrição foi INDEFERIDA não cabendo mais recurso']);
    }

    Session::put('inscricao_recurso_pontuacao_id', $inscricao->id);

    return redirect()->route('recurso-pontuacao.form');
}

// Exibe formulário do recurso
public function recursoPontuacaoForm() {
    $inscricaoId = Session::get('inscricao_recurso_pontuacao_id');
    if (!$inscricaoId) {
        return redirect()->route('recurso-pontuacao.login');
    }
    $inscricao = DB::table('inscricoes')->where('id', $inscricaoId)->first();
    if (!$inscricao) {
        return redirect()->route('recurso-pontuacao.login');
    }

    $jaTem = DB::table('recursos_pontuacao')->where('inscricao_id', $inscricaoId)->exists();
    if ($jaTem) {
        return view('recursos_pontuacao.ja_enviado');
    }

    return view('recursos_pontuacao.form', compact('inscricao'));
}

// Salva recurso de pontuação enviado pelo candidato
public function recursoPontuacaoSolicitar(Request $request) {
    \Log::info('INICIO DO MÉTODO recursoPontuacaoSolicitar');

    $inscricaoId = Session::get('inscricao_recurso_pontuacao_id');
    \Log::info('Session inscricao_recurso_pontuacao_id', ['id' => $inscricaoId]);

    if (!$inscricaoId) {
        \Log::warning('Session NÃO encontrada');
        return redirect()->route('recurso-pontuacao.login');
    }

    $inscricao = DB::table('inscricoes')->where('id', $inscricaoId)->first();
    if (!$inscricao) {
        \Log::warning('Inscrição NÃO encontrada no banco');
        return redirect()->route('recurso-pontuacao.login');
    }
    \Log::info('Inscrição encontrada', ['id' => $inscricao->id, 'nome' => $inscricao->nome_completo]);

    try {
        $request->validate([
            'motivo' => 'nullable|string|min:5',
            'arquivo' => 'required|file|max:30000|mimes:pdf,jpg,jpeg,png',
        ]);
        \Log::info('Validação passou');
    } catch (\Exception $e) {
        \Log::error('Erro na validação', ['msg' => $e->getMessage()]);
        return back()->with('error', 'Erro de validação.');
    }

    $jaTem = DB::table('recursos_pontuacao')->where('inscricao_id', $inscricaoId)->exists();
    \Log::info('Já existe recurso para essa inscrição?', ['jaTem' => $jaTem]);

    if ($jaTem) {
        \Log::info('Recurso já enviado, mostrando tela jaenviado');
        return view('recursos_pontuacao.jaenviado');
    }

    $arquivoPath = null;
    $arquivoEnviado = false;
    if ($request->hasFile('arquivo')) {
        $file = $request->file('arquivo');
        $arquivoPath = $file->store('recursos_pontuacao', 'public');
        $arquivoEnviado = true;
        \Log::info('Arquivo salvo em:', ['path' => $arquivoPath]);
        if (file_exists(storage_path('app/public/' . $arquivoPath))) {
            \Log::info('Arquivo REALMENTE existe no storage!');
        } else {
            \Log::error('ERRO: Arquivo NÃO existe em storage/app/public/' . $arquivoPath);
        }
    } else {
        \Log::info('Nenhum arquivo foi anexado');
    }

    try {
        $inseriu = DB::table('recursos_pontuacao')->insert([
            'inscricao_id'      => $inscricaoId,
            'nome_completo'     => $inscricao->nome_completo,
            'cpf'               => $inscricao->cpf,
            'cargo'             => $inscricao->cargo,
            'pontuacao_atual'   => $inscricao->pontuacao,
            'motivo'            => $request->motivo,
            'arquivo'           => $arquivoPath,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        \Log::info('Registro inserido no banco?', ['inseriu' => $inseriu]);
    } catch (\Exception $e) {
        \Log::error('Erro ao inserir recurso no banco: '.$e->getMessage());
        return back()->with('error', 'Erro ao salvar recurso no banco de dados.');
    }

    Session::forget('inscricao_recurso_pontuacao_id');
    \Log::info('Session esquecida, finalizando método');

    return view('recursos_pontuacao.sucesso', ['arquivoEnviado' => $arquivoEnviado]);
}


// Admin: Listagem recursos de pontuação
public function recursosPontuacaoAdmin(Request $request) {
    $query = DB::table('recursos_pontuacao');
    if ($request->filled('cpf')) {
        $query->where('cpf', 'like', '%' . preg_replace('/\D/', '', $request->cpf) . '%');
    }
    $recursos = $query->orderBy('created_at', 'desc')->paginate(20);
    return view('recursos_pontuacao.admin', compact('recursos'));
}

// Admin: Análise do recurso
public function analiseRecursoPontuacao(Request $request, $id) {
    $request->validate([
        'status_analise' => 'required',
        'nova_nota' => 'nullable|integer|min:0|max:1000',
    ]);
    $update = [
        'status_analise' => $request->status_analise,
        'updated_at' => now(),
    ];
    if ($request->status_analise == 'Aceito' && $request->filled('nova_nota')) {
        $update['nova_nota'] = $request->nova_nota;
    } else {
        $update['nova_nota'] = null;
    }
    DB::table('recursos_pontuacao')->where('id', $id)->update($update);
    return back()->with('success', 'Análise salva.');
}

// Admin: Exportação CSV
public function exportarRecursosPontuacaoCSV() {
    $recursos = DB::table('recursos_pontuacao')->get();
    $csvData = [];
    $csvData[] = ['Nome', 'CPF', 'Cargo', 'Pontuação Atual', 'Motivo', 'Status', 'Nova Nota', 'Data/Hora'];
    foreach ($recursos as $r) {
        $csvData[] = [
            $r->nome_completo,
            $r->cpf,
            $r->cargo,
            $r->pontuacao_atual,
            $r->motivo,
            $r->status_analise,
            $r->nova_nota,
            \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i')
        ];
    }
    $filename = 'recursos_pontuacao_' . date('Ymd_His') . '.csv';
    $handle = fopen('php://memory', 'r+');
    foreach ($csvData as $linha) {
        fputcsv($handle, $linha, ';');
    }
    rewind($handle);
    $csv = stream_get_contents($handle);
    fclose($handle);
    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', "attachment; filename=$filename");
}

// Admin: Exportação PDF
public function exportarRecursosPontuacaoPDF() {
    $recursos = DB::table('recursos_pontuacao')->get();
    $pdf = \PDF::loadView('recursos_pontuacao.pdf', compact('recursos'));
    return $pdf->download('recursos_pontuacao.pdf');
}




//

















//==== Recurso ===//

public function recursoLoginForm()
{
    return view('recurso-login');
}

public function recursoLogin(Request $request)
{
    $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
    $data_nasc = $request->data_nascimento;

    $inscricao = DB::table('inscricoes')
        ->where('cpf', $cpf)
        ->where('data_nascimento', $data_nasc)
        ->first();

    if (!$inscricao) {
        return back()->with('error', 'Dados não conferem.');
    }

    if ($inscricao->status_avaliacao !== 'Indeferido') {
        return back()->with('error', 'Sua inscrição foi DEFERIDA e, no momento, não cabe Recurso.');
    }

    session(['recurso_inscricao_id' => $inscricao->id]);
    return redirect()->route('recurso.form');
}

public function salvarAnaliseRecurso(Request $request, $id)
{
    \App\Models\Recurso::where('id', $id)->update([
        'status_analise' => $request->status_analise,
    ]);
    return back()->with('success', 'Status do recurso atualizado!');
}




public function recursoForm()
{
    $inscricao_id = session('recurso_inscricao_id');
    if (!$inscricao_id) {
        return redirect()->route('recurso.login.form');
    }

    $inscricao = DB::table('inscricoes')->where('id', $inscricao_id)->first();

    if (!$inscricao) {
        return redirect()->route('recurso.login.form');
    }

    $recurso = Recurso::where('inscricao_id', $inscricao_id)->where('tipo', 'inscricao')->first();

    return view('recurso-form', compact('inscricao', 'recurso'));
}


public function recursoEnviar(Request $request)
{
 $request->validate([
        'arquivo' => 'required|file|mimes:pdf|max:22000'
    ]);

    $inscricao_id = session('recurso_inscricao_id');
    if (!$inscricao_id) {
        return redirect()->route('recurso.login.form');
    }

    $inscricao = DB::table('inscricoes')->where('id', $inscricao_id)->first();
    if (!$inscricao) {
        return redirect()->route('recurso.login.form');
    }

    $jaExiste = Recurso::where('inscricao_id', $inscricao_id)->where('tipo', 'inscricao')->exists();
    if ($jaExiste) {
        return back()->with('error', 'Recurso já enviado para esta inscrição.');
    }

    $arquivo = $request->file('arquivo')->store('recursos');
    $numeroRecurso = 'R' . now()->format('YmdHis') . rand(1000, 9999);

    Recurso::create([
        'inscricao_id' => $inscricao_id,
        'numero_recurso' => $numeroRecurso,
        'tipo' => 'inscricao',
        'arquivo' => $arquivo,
    ]);

    return redirect()->route('recurso.form')->with('success', 'Recurso enviado com sucesso!');  
}


public function recursosAdmin(Request $request)
{
$query = DB::table('recursos')
        ->join('inscricoes', 'recursos.inscricao_id', '=', 'inscricoes.id')
        ->select(
            'recursos.*',
            'inscricoes.nome_completo',
            'inscricoes.cpf',
            'inscricoes.numero_inscricao',
            'inscricoes.cargo',
            'inscricoes.status_avaliacao',
            'inscricoes.motivo_indeferimento'
        )
        ->where('recursos.tipo', 'inscricao');

    if ($request->filled('cpf')) {
        $query->where('inscricoes.cpf', $request->cpf);
    }

    $recursos = $query->orderByDesc('recursos.id')->paginate(20);

    return view('recursos-admin', compact('recursos')); 



}


public function exportarRecursosCSV(Request $request)
{
    $recursos = DB::table('recursos')
        ->join('inscricoes', 'recursos.inscricao_id', '=', 'inscricoes.id')
        ->select(
            'inscricoes.nome_completo',
            'inscricoes.cpf',
            'inscricoes.cargo',
            'recursos.status_analise',
            'inscricoes.motivo_indeferimento',
            'recursos.created_at'
        )
        ->where('recursos.tipo', 'inscricao')
        ->get();

    $filename = 'recursos_' . date('Ymd_His') . '.csv';
   // Cabeçalhos para download e encoding correto
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo "\xEF\xBB\xBF";
   
    $handle = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"$filename\"");

    // Cabeçalhos
    fputcsv($handle, [
        'Nome', 'CPF', 'Cargo', 'Status do Recurso', 'Motivo Indeferimento', 'Data/Hora'
    ]);

    foreach ($recursos as $r) {
        fputcsv($handle, [
            $r->nome_completo,
            $r->cpf,
            $r->cargo,
            $r->status_analise,
            $r->motivo_indeferimento,
            $r->created_at,
        ]);
    }
    fclose($handle);
    exit;
}

public function exportarRecursosPDF(Request $request)
{
    $recursos = DB::table('recursos')
        ->join('inscricoes', 'recursos.inscricao_id', '=', 'inscricoes.id')
        ->select(
            'inscricoes.nome_completo',
            'inscricoes.cpf',
            'inscricoes.cargo',
            'recursos.status_analise',
            'inscricoes.motivo_indeferimento',
            'recursos.created_at',
	    'inscricoes.status_avaliacao'
        )
        ->where('recursos.tipo', 'inscricao')
        ->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('recursos-pdf', compact('recursos'));
    return $pdf->download('recursos_' . date('Ymd_His') . '.pdf');
}



//==== FIM Recurso===//

//===========FUNÇÃO PARA SALVAR AVALIAÇÃO DO STATUS DO DEFERIMENTO =======/

public function salvarAvaliacao(Request $request, $id)
{
    $status = $request->input('status_avaliacao');
    $motivo = $request->input('motivo_indeferimento');
    $pontuacao = $request->input('pontuacao');

    $data = [
        'status_avaliacao' => $status,
        'motivo_indeferimento' => null,
        'pontuacao' => null,
    ];

    if ($status === 'Indeferido') {
        $data['motivo_indeferimento'] = $motivo;
    }

    if ($status === 'Deferido') {
        $data['pontuacao'] = $pontuacao;
    }

    DB::table('inscricoes')->where('id', $id)->update($data);

    return back()->with('success', 'Avaliação salva com sucesso!');
}



//========== FIM DA FUNÇÃO PARA SALVAR AVALIAÇÃO DO STATUS DO DEFERIMENTO====/




// =========== MÉTODO CLASSIFICAÇÃO ======//



public function classificacao(Request $request)
{
    // Lista de cargos distintos cadastrados
    $cargos = DB::table('inscricoes')
        ->whereNotNull('cargo')
        ->select('cargo')
        ->distinct()
        ->pluck('cargo');

    $cargoSelecionado = $request->input('cargo');

    $candidatos = collect();

    if ($cargoSelecionado) {
        $candidatos = DB::table('inscricoes')
            ->where('cargo', $cargoSelecionado)
	    ->where('status_avaliacao', 'DEFERIDO')
            ->whereNotNull('pontuacao')
            ->whereNotNull('pontuacao_entrevista')
            ->select(
                'nome_completo',
                'cpf',
                'pontuacao',
                'pontuacao_entrevista',
                'data_nascimento',
		DB::raw('(pontuacao + pontuacao_entrevista) as nota_final')
            )
            ->orderByDesc(DB::raw('pontuacao + pontuacao_entrevista')) // Nota final decrescente
            ->orderBy('data_nascimento') // Mais velho primeiro
            ->paginate(100); // Paginação: 100 por página
    }

    return view('classificacao', compact('cargos', 'candidatos', 'cargoSelecionado'));
}


public function classificacaoPdf(Request $request)
{
    $cargo = $request->input('cargo');
    $candidatos = $this->getCandidatosClassificados($cargo);

    $pdf = Pdf::loadView('classificacao-pdf', [
        'cargo' => $cargo,
        'candidatos' => $candidatos
    ])->setPaper('a4', 'portrait');

    return $pdf->download("classificacao_{$cargo}.pdf");
}

private function getCandidatosClassificados($cargo)
{
    $candidatos = DB::table('inscricoes')
        ->where('cargo', $cargo)
        ->whereNotNull('pontuacao')
	->where('status_avaliacao', 'DEFERIDO')
        ->whereNotNull('pontuacao_entrevista')
        ->select(
            'nome_completo',
            'cpf',
            'pontuacao',
            'pontuacao_entrevista',
            'data_nascimento'
        )
        ->get()
        ->map(function ($c) {
            $c->nota_final = ($c->pontuacao ?? 0) + ($c->pontuacao_entrevista ?? 0);
            return $c;
        })
        ->sort(function ($a, $b) {
            if ($a->nota_final != $b->nota_final) {
                return $b->nota_final <=> $a->nota_final;
            }
            return strtotime($a->data_nascimento) <=> strtotime($b->data_nascimento);
        })->values();

    return $candidatos;
}


// ============ FIM DO MÉTODO DE CLASSIFICAÇÃO ======/

// ========== ENTREVISTA =========/

// Formulário de CPF
public function formEntrevista()
{
    return view('entrevista-form');
}

// Buscar candidato pelo CPF
public function buscarEntrevista(Request $request)
{
    $cpf = $request->input('cpf');

    $candidato = DB::table('inscricoes')->where('cpf', $cpf)->first();

    if (!$candidato) {
        return back()->with('erro', 'CPF não encontrado.');
    }

    return view('entrevista-lancar', compact('candidato'));
}

// Salvar nota da entrevista
public function salvarEntrevista(Request $request, $id)
{
    $cpf = $request->input('cpf');
    $nota = $request->input('pontuacao_entrevista');

    DB::table('inscricoes')
        ->where('id', $id)
        ->update(['pontuacao_entrevista' => $nota,
        'avaliador_id' => auth()->id(),
        'updated_at' => now(),
    ]);
	return back()->with('success', 'Nota da entrevista salva com sucesso!');
   // return redirect()->route('entrevista.form')->with('sucesso', 'Nota da entrevista salva com sucesso!');
}







//=============FIM ENTREVISTA =========/









//SEGUNDA VIA - FORMULARIO

public function segundaViaForm()
{
    return view('segunda-via');
}

public function segundaViaBuscar(Request $request)
{
    $cpf = preg_replace('/\D/', '', $request->input('cpf'));
    $request->merge(['cpf' => $cpf]);
    $request->validate([
	'cpf' => ['required','digits:11','exists:inscricoes,cpf'],
    ], [
	'cpf.exists' => 'CPF não encontrado na base de dados.',
        'cpf.digits' => 'O CPF deve conter exatamente 11 dígitos numéricos.',
]);

    $inscricao = DB::table('inscricoes')->where('cpf', $cpf)->first();

    if (!$inscricao) {
        return back()->withErrors(['cpf' => 'CPF não encontrado.'])->withInput();
    }

    return redirect()->route('comprovante.inscricao', ['id' => $inscricao->id]);
}

//FIM SEGUNDA VIA - FORMULARIO


//===================CORREÇÃO DE  CADASTRO E DOCUMENTOS=============================\\




public function loginCorrigir()
{
    session()->forget('corrigir_cpf');
    return view('corrigir-login');
}

public function autenticarCorrigir(Request $request)
{
    $cpf = preg_replace('/\D/', '', $request->input('cpf'));
    $senha = $request->input('senha');

    $inscricao = \DB::table('inscricoes')->where('cpf', $cpf)->first();

    if (!$inscricao) {
        return back()->withErrors(['cpf' => 'CPF não encontrado']);
    }

    if ($senha !== $inscricao->senha_correcao) {
        return back()->withErrors(['senha' => 'Senha incorreta']);
    }

    session(['corrigir_cpf' => $cpf]);

    return redirect()->route('corrigir.formulario');
}


public function formularioCorrigir()
{
    $cpf = session('corrigir_cpf');

    if (!$cpf) {
        return redirect()->route('corrigir.login')->withErrors('Por favor, faça login.');
    }

    $inscricao = \DB::table('inscricoes')->where('cpf', $cpf)->first();

    if (!$inscricao) {
        abort(404, 'Inscrição não encontrada.');
    }

    return view('corrigir-formulario', compact('inscricao'));
}

public function atualizarCorrigir(Request $request)
{
    $cpf = session('corrigir_cpf');

    if (!$cpf) {
        return redirect()->route('corrigir.login')->withErrors('Por favor, faça login.');
    }

    $request->validate([
        'nome_completo' => 'required|string|max:150',
        'nome_social' => 'nullable|string|max:150',
        'email' => 'required|email',
        'telefone' => 'nullable|string',
        'documento' => 'nullable|file|mimes:pdf|max:20480',
        'funcao' => 'nullable|file|mimes:pdf|max:20480',
    ]);

    $dados = [
        'nome_completo' => $request->input('nome_completo'),
        'nome_social' => $request->input('nome_social'),
        'email' => $request->input('email'),
        'telefone' => $request->input('telefone'),
    ];

    if ($request->hasFile('documento')) {
        $arquivo = $request->file('documento');
        $nomeArquivo = 'documento_' . $cpf . '.' . $arquivo->getClientOriginalExtension();
        $caminho = $arquivo->storeAs('documentos', $nomeArquivo, 'private');
        $dados['documentos_path'] = $caminho;
    }

    if ($request->hasFile('funcao')) {
        $arquivoFuncao = $request->file('funcao');
        $nomeArquivoFuncao = 'funcao_' . $cpf . '.' . $arquivoFuncao->getClientOriginalExtension();
        $caminhoFuncao = $arquivoFuncao->storeAs('funcao', $nomeArquivoFuncao, 'private');
        $dados['funcao_path'] = $caminhoFuncao;
    }

    \DB::table('inscricoes')->where('cpf', $cpf)->update($dados);

    return redirect()->route('corrigir.formulario')->with('success', 'Dados atualizados com sucesso!');
}
public function logoutCorrigir()
{
    session()->forget('corrigir_cpf');
    return redirect()->route('corrigir.login')->with('success', 'Você saiu com sucesso.');
}


//===================FIM DA FUNÇÃO DE  CADASTRO E DOCUMENTOS=============================\\


//funcao buscarcpf - pontuação

public function buscarCPF(Request $request)
{
    $inscricao = null;

    if ($request->filled('cpf')) {
        $inscricao = DB::table('inscricoes')->where('cpf', $request->cpf)->first();
    }

    return view('pontuacao', compact('inscricao'));
}

//fim funcao buscar vpf - pontuação

//funcao salvar pontuação

public function salvarPontuacao(Request $request)
{
    $request->validate([
        'id' => 'required|exists:inscricoes,id',
        'pontuacao' => 'required|integer|min:0|max:1000',
    ]);

    DB::table('inscricoes')->where('id', $request->id)->update([
        'pontuacao' => $request->pontuacao,
        'updated_at' => now()
    ]);

    return redirect()->route('pontuacao.buscar')->with('success', 'Pontuação salva com sucesso.');
}

//fim funcao salvar pontuação


//login
public function loginForm()
{
    return view('login');
}

//FUNÇÃO DOWNLOAD PRIVADO
public function downloadPrivado($tipo, $id)
{
    $inscricao = DB::table('inscricoes')->find($id);

    if (!$inscricao) {
        abort(404, 'Inscrição não encontrada.');
    }

    if ($tipo === 'documento') {
        $path = $inscricao->documentos_path;
    } elseif ($tipo === 'funcao') {
        $path = $inscricao->funcao_path;
    } else {
        abort(404, 'Tipo inválido.');
    }

    $fullPath = storage_path('app/private/' . $path);

    if (!file_exists($fullPath)) {
        abort(404, 'Arquivo não encontrado: ' . $fullPath);
    }

    return response()->file($fullPath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
    ]);
}


//FIM DA  FUNÇÃO DE DOWNLOAD PRIVADO

public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->route('painel');
    }

    return back()->withErrors([
        'email' => 'E-mail ou senha inválidos.',
    ]);
}

public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
}

//fim login

//painel

public function painel(Request $request)
{
    $query = DB::table('inscricoes');

    if ($request->filled('cargo')) {
        $query->where('cargo', $request->cargo);
    }

    if ($request->filled('pcd')) {
        $query->where('pcd', $request->pcd === 'sim');
    }

    if ($request->filled('cpf')) {
      $cpf = preg_replace('/\D/', '', $request->cpf); // Remove pontos e traços
      $query->where('cpf', 'like', '%' . $cpf . '%');
    }
   if ($request->filled('nome_completo')) {
    $query->where('nome_completo', 'like', '%' . $request->nome_completo . '%');
}



    $inscricoes = $query->orderBy('created_at', 'desc')->paginate(15);

    $cargos = DB::table('inscricoes')->select('cargo')->distinct()->pluck('cargo');

    $totalInscritos = DB::table('inscricoes')->count();


    //return view('painel', compact('inscricoes', 'cargos'));
    return view('painel', [
   	'inscricoes' => $inscricoes,
    	'cargos' => $cargos,
    	'totalInscritos' => $totalInscritos,
]);

}
//fim painel

//FUNCAO EXPORTA CSV
public function exportarCSV()
{
    $inscricoes = DB::table('inscricoes')->get();

    // Define headers com UTF-8
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="inscricoes.csv"');

    $csv = fopen('php://output', 'w');

    // 👇 Esta linha adiciona o BOM para que o Excel reconheça UTF-8
    fprintf($csv, chr(0xEF).chr(0xBB).chr(0xBF));

    $header = [
        'ID', 'Nome Completo', 'CPF','Data de Nascimento', 'E-mail', 'Telefone', 'PCD','Cargo', 'Número Inscrição', 'Hash', 'Pontuacao', 'Pontuação Entrevista', 'Status do Deferimento', 'Motivo do Indeferimento','Data'
    ];

    fputcsv($csv, $header);

    foreach ($inscricoes as $i) {
        fputcsv($csv, [
            $i->id,
            $i->nome_completo,
            $i->cpf,
	    $i->data_nascimento,
            $i->email,
            $i->telefone,
            $i->pcd ? 'Sim' : 'Não',
            //$i->descricao_pcd,
            $i->cargo,
            $i->numero_inscricao,
            $i->hash_validacao,
	    $i->pontuacao,
	    $i->pontuacao_entrevista,
	    $i->status_avaliacao,
	    $i->motivo_indeferimento,
            \Carbon\Carbon::parse($i->created_at)->format('d/m/Y H:i'),
        ]);
    }

    fclose($csv);
    exit;
}


//FIM FUNCAO EXPORTA CSV

    // Tela de verificação de CPF
    public function verificarCPF()
    {
        return view('verificar-cpf');
    }
    public function formulario($cpf)
    {
        $cpfSession = session('cpf_verificado');

        if ($cpfSession !== $cpf) {
            return redirect()->route('verificar.cpf')->withErrors([
                'cpf' => 'Acesso direto ao formulário não é permitido.'
            ]);
        }

    return view('formulario', compact('cpf'));
    }


    // Lógica para checar se CPF já existe
    public function checarCPF(Request $request)
    {
    $request->merge([
        'cpf' => preg_replace('/\D/', '', $request->cpf)
    ]);

    $request->validate([
        'cpf' => ['required', 'digits:11', function ($attribute, $value, $fail) {
            if (!self::validaCPF($value)) {
                $fail('CPF inválido.');
            }
        }]
    ]);

    session(['cpf_verificado' => $request->cpf]);

    $cpf = $request->input('cpf');

    $existe = DB::table('inscricoes')->where('cpf', $cpf)->exists();

    if ($existe) {
        return redirect()->back()->withErrors(['cpf' => 'CPF já cadastrado.']);
    }

    return redirect()->route('formulario.inscricao', ['cpf' => $cpf]);
    }   

    // Formulário de dados pessoais e anexos
    public function confirmar(Request $request)
{
    $request->validate([
        'cpf' => 'required',
        'nome_completo' => 'required|string|max:150',
	'nome_social' => 'nullable|string|max:150',
        'email' => 'required|email',
        'telefone' => 'required',
        'pcd' => 'required|in:0,1',
        'descricao_pcd' => 'nullable|string',
        'cargo' => 'required|string',
        'documentos' => 'required|file|mimes:pdf|max:20480',
        'funcao' => 'required|file|mimes:pdf|max:20480',
	'data_nascimento' => 'required|date',
    ]);

    // Gera número de inscrição
    $ultimo = DB::table('inscricoes')->max('id') ?? 0;
    $numeroInscricao = str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);

    // Gera hash único
    $hash = hash('sha256', $request->cpf . now());


    //Senha Correcao
    $senhaCorrecao = substr($hash, 0, 8);

    // Salva os arquivos
    $docPath = $request->file('documentos')->store("documentos", 'private');
    $funcPath = $request->file('funcao')->store("funcoes", 'private');

    // Salva no banco
    $id = DB::table('inscricoes')->insertGetId([
        'cpf' => $request->cpf,
        'nome_completo' => $request->nome_completo,
	'nome_social' => $request->nome_social,
        'email' => $request->email,
        'telefone' => $request->telefone,
        'pcd' => $request->pcd,
        'descricao_pcd' => $request->descricao_pcd,
        'cargo' => $request->cargo,
        'documentos_path' => $docPath,
        'funcao_path' => $funcPath,
        'numero_inscricao' => $numeroInscricao,
        'hash_validacao' => $hash,
        'created_at' => now(),
        'updated_at' => now(),
	'data_nascimento' => $request->data_nascimento,
	'senha_correcao' => $senhaCorrecao,

    ]);

    return redirect()->route('comprovante.inscricao', ['id' => $id]);
}
public function comprovante($id)
{
    $inscricao = DB::table('inscricoes')->find($id);

    if (!$inscricao) {
        abort(404);
    }

    return view('comprovante', compact('inscricao'));

}

public function gerarPDF($id)
{
    $inscricao = DB::table('inscricoes')->find($id);

    if (!$inscricao) {
        abort(404);
    }

    $pdf = Pdf::loadView('comprovante-pdf', compact('inscricao'));
    return $pdf->download("comprovante-{$inscricao->numero_inscricao}.pdf");
}
public function validarHashForm()
{
    return view('validar');
}

//REALTORIO DETALHADO
public function relatorio()
{
    $total = DB::table('inscricoes')->count();

    $porCargo = DB::table('inscricoes')
        ->select('cargo', DB::raw('count(*) as total'))
        ->groupBy('cargo')
        ->orderBy('total', 'desc')
        ->get();

    $pcdSim = DB::table('inscricoes')->where('pcd', 1)->count();
    $pcdNao = DB::table('inscricoes')->where('pcd', 0)->count();

    $hoje = DB::table('inscricoes')
        ->whereDate('created_at', now()->toDateString())
        ->count();

    return view('relatorio', compact('total', 'porCargo', 'pcdSim', 'pcdNao', 'hoje'));
}
//

public function exportarClassificadosSeguroPdf(Request $request)
{
    $query = DB::table('inscricoes');

    if ($request->filled('cargo')) {
        $query->where('cargo', $request->cargo);
    }
    $query->where('status_avaliacao', 'DEFERIDO');

    if ($request->filled('pcd')) {
        $query->where('pcd', $request->pcd == 'sim' ? 1 : 0);
    }
    if ($request->filled('cpf')) {
        $query->where('cpf', $request->cpf);
    }

    $inscricoes = $query->select('nome_completo', 'cpf', 'pontuacao','cargo')
        ->orderBy('nome_completo')
        ->get()
        ->map(function ($i) {
            // Nome MAIÚSCULO
            $i->nome_completo = mb_strtoupper($i->nome_completo, 'UTF-8');
            // CPF mascarado
            $cpf = preg_replace('/\D/', '', $i->cpf);
            $i->cpf_mascarado = (strlen($cpf) === 11)
                ? substr($cpf, 0, 3) . '******' . substr($cpf, -2)
                : $cpf;
            return $i;
        });

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('classificados-seguro-pdf', [
        'inscricoes' => $inscricoes,
        'dataExportacao' => now()->format('d/m/Y H:i'),
    ])->setPaper('a4', 'portrait');

    return $pdf->download('classificados-seguro.pdf');
}


public function exportarEntrevistaSeguroPdf(Request $request)
{
    $query = DB::table('inscricoes');

    if ($request->filled('cargo')) {
        $query->where('cargo', $request->cargo);
    }
    $query->where('status_avaliacao', 'DEFERIDO');

    if ($request->filled('pcd')) {
        $query->where('pcd', $request->pcd == 'sim' ? 1 : 0);
    }
    if ($request->filled('cpf')) {
        $query->where('cpf', $request->cpf);
    }

    $inscricoes = $query->select('nome_completo', 'cpf', 'pontuacao_entrevista', 'cargo')
        ->orderBy('nome_completo')
        ->get()
        ->map(function ($i) {
            // Nome MAIÚSCULO
            $i->nome_completo = mb_strtoupper($i->nome_completo, 'UTF-8');
            // CPF mascarado
            $cpf = preg_replace('/\D/', '', $i->cpf);
            $i->cpf_mascarado = (strlen($cpf) === 11)
                ? substr($cpf, 0, 3) . '******' . substr($cpf, -2)
                : $cpf;
            return $i;
        });

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('classificados-entrevista-seguro-pdf', [
        'inscricoes' => $inscricoes,
    ])->setPaper('a4', 'portrait');
    $cargo = $request->input('cargo') ?? 'todos';
    $cargoSlug = str_replace([' ', '/', '\\'], '_', mb_strtolower($cargo));
    $cargoSlug = iconv('UTF-8', 'ASCII//TRANSLIT', $cargoSlug);

    return $pdf->download("result-preliminar-{$cargoSlug}.pdf");
}












//
//FIM RELATORIO DETALHADO

public function validarHashResultado(Request $request)
{
    $request->validate([
        'hash' => 'required|string'
    ]);

    $inscricao = DB::table('inscricoes')->where('hash_validacao', $request->hash)->first();

    return view('validar', [
        'inscricao' => $inscricao,
        'busca_realizada' => true,
        'hash' => $request->hash
    ]);
}




// RECURSOS ENTREVISTA //

public function recursoEntrevistaLoginForm() {
    return view('recursos_entrevista.login');
}
public function recursoEntrevistaLogin(Request $request) {
   Session::forget('inscricao_recurso_entrevista_id');
    $request->validate([
        'cpf' => 'required',
        'data_nascimento' => 'required|date',
    ]);

    $cpf = preg_replace('/\D/', '', $request->cpf);

    $inscricao = DB::table('inscricoes')
        ->where('cpf', $cpf)
        ->where('data_nascimento', $request->data_nascimento)
        ->first();

    if (!$inscricao) {
        return back()->withErrors(['cpf' => 'Dados inválidos.']);
    }


    // Pode limitar apenas para inscrições deferidas, se desejar:
	if (mb_strtolower(trim($inscricao->status_avaliacao)) !== 'deferido') {
         return back()->withErrors(['cpf' => 'Inscrição não habilitada para recurso.']);
     }

    Session::put('inscricao_recurso_entrevista_id', $inscricao->id);

    return redirect()->route('recurso-entrevista.form');
}

public function recursoEntrevistaForm() {
    $inscricaoId = Session::get('inscricao_recurso_entrevista_id');
    if (!$inscricaoId) {
        return redirect()->route('recurso-entrevista.login');
    }

    $inscricao = DB::table('inscricoes')->where('id', $inscricaoId)->first();
    if (!$inscricao) {
        return redirect()->route('recurso-entrevista.login');
    }

    $jaTem = DB::table('recursos_entrevista')->where('inscricao_id', $inscricaoId)->exists();
    if ($jaTem) {
        return view('recursos_entrevista.ja_enviado');
    }

    return view('recursos_entrevista.form', compact('inscricao'));
}

public function recursoEntrevistaSolicitar(Request $request) {
    $inscricaoId = Session::get('inscricao_recurso_entrevista_id');
    if (!$inscricaoId) {
        return redirect()->route('recurso-entrevista.login');
    }

    $inscricao = DB::table('inscricoes')->where('id', $inscricaoId)->first();
    if (!$inscricao) {
        return redirect()->route('recurso-entrevista.login');
    }

    $messages = ['arquivo.required' => 'O anexo do documento é obrigatório.'];
    $request->validate([
        'motivo' => 'nullable|string',
        'arquivo' => 'required|file|max:30000|mimes:pdf,jpg,jpeg,png',
    ], $messages);

    $jaTem = DB::table('recursos_entrevista')->where('inscricao_id', $inscricaoId)->exists();
    if ($jaTem) {
        return view('recursos_entrevista.ja_enviado');
    }

    $arquivoPath = null;
    $arquivoEnviado = false;
    if ($request->hasFile('arquivo')) {
        $arquivoPath = $request->file('arquivo')->store('recursos_entrevista', 'public');
        $arquivoEnviado = true;
    }

    DB::table('recursos_entrevista')->insert([
        'inscricao_id' => $inscricaoId,
        'nome_completo' => $inscricao->nome_completo,
        'cpf' => $inscricao->cpf,
        'cargo' => $inscricao->cargo,
        'pontuacao_entrevista' => $inscricao->pontuacao_entrevista,
        'motivo' => $request->motivo,
        'arquivo' => $arquivoPath,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Session::forget('inscricao_recurso_entrevista_id');
    return view('recursos_entrevista.sucesso', ['arquivoEnviado' => $arquivoEnviado]);
}



public function recursosEntrevistaAdmin(Request $request) {
    $query = DB::table('recursos_entrevista');
    if ($request->filled('cpf')) {
        $query->where('cpf', 'like', '%' . preg_replace('/\D/', '', $request->cpf) . '%');
    }
    $recursos = $query->orderBy('created_at', 'desc')->paginate(20);
    return view('recursos_entrevista.admin', compact('recursos'));
}



public function analiseRecursoEntrevista(Request $request, $id) {
    $request->validate([
        'status_analise' => 'required',
        'nova_nota' => 'nullable|integer|min:0|max:1000',
    ]);
    $update = [
        'status_analise' => $request->status_analise,
        'updated_at' => now(),
    ];
    if ($request->status_analise == 'Aceito' && $request->filled('nova_nota')) {
        $update['nova_nota'] = $request->nova_nota;
    } else {
        $update['nova_nota'] = null;
    }
    DB::table('recursos_entrevista')->where('id', $id)->update($update);
    return back()->with('success', 'Análise salva.');
}



public function exportarRecursosEntrevistaCSV() {
    $recursos = DB::table('recursos_entrevista')->get();
    $csvData = [];
    $csvData[] = ['Nome', 'CPF', 'Cargo', 'Pontuação Entrevista', 'Motivo', 'Status', 'Nova Nota', 'Data/Hora'];
    foreach ($recursos as $r) {
        $csvData[] = [
            $r->nome_completo,
            $r->cpf,
            $r->cargo,
            $r->pontuacao_entrevista,
            $r->motivo,
            $r->status_analise,
            $r->nova_nota,
            \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i')
        ];
    }
    $filename = 'recursos_entrevista_' . date('Ymd_His') . '.csv';
    $handle = fopen('php://memory', 'r+');
    foreach ($csvData as $linha) {
        fputcsv($handle, $linha, ';');
    }
    rewind($handle);
    $csv = stream_get_contents($handle);
    fclose($handle);
    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', "attachment; filename=$filename");
}




public function exportarRecursosEntrevistaPDF() {
    $recursos = DB::table('recursos_entrevista')->get();
    $pdf = \PDF::loadView('recursos_entrevista.pdf', compact('recursos'));
    return $pdf->download('recursos_entrevista.pdf');
 }

public function telaDesempate()
{
    $cargos = DB::table('inscricoes')->select('cargo')->distinct()->pluck('cargo');
    $empates = [];

    foreach ($cargos as $cargo) {
        $inscricoes = DB::table('inscricoes')
            ->where('cargo', $cargo)
	    ->where('status_avaliacao', 'DEFERIDO')
	    ->select('*', DB::raw('(COALESCE(pontuacao,0) + COALESCE(pontuacao_entrevista,0)) as pontuacao_final'))
            ->orderByDesc('pontuacao_final')
            ->orderBy('data_nascimento')
            ->get();

        $agrupados = [];
        foreach ($inscricoes as $i) {
            $key = $i->pontuacao_final . '_' . $i->data_nascimento;
            $agrupados[$key][] = $i;
        }
        foreach ($agrupados as $key => $grupo) {
            if (count($grupo) > 1) {
                // verifica se já existe um desempate registrado
                $registro = DB::table('desempates')->where('cargo', $cargo)->where('grupo_key', $key)->first();
                $empates[$cargo][] = [
                    'key' => $key,
                    'candidatos' => $grupo,
                    'ja_escolhido' => $registro ? $registro->cpf_escolhido : null
                ];
            }
        }
    }

    return view('desempate_manual', compact('empates'));
}

public function resolverDesempates(Request $request)
{
    $dados = $request->input('desempate', []);
    foreach ($dados as $cargo => $grupos) {
        foreach ($grupos as $key => $cpf_escolhido) {
            // Buscar lista de CPFs empatados para registro
            $cpfs = array_map(function($c) { return $c['cpf']; }, $request->input("candidatos_info.{$cargo}.{$key}", []));
            // Salva ou atualiza
            DB::table('desempates')->updateOrInsert(
                ['cargo' => $cargo, 'grupo_key' => $key],
                ['cpfs' => json_encode($cpfs), 'cpf_escolhido' => $cpf_escolhido, 'updated_at' => now()]
            );
        }
    }
    return redirect()->route('classificacao.final.export')->with('success', 'Desempates salvos! Gere agora o relatório final.');
}

// Para exibir botão de exportação só depois de resolver todos os empates, pode checar se há algum não resolvido

public function exportarClassificacaoFinal(Request $request)
{
   ini_set('memory_limit', '2048M');
   set_time_limit(120);
   ini_set('max_execution_time', 300);
    $cargos = DB::table('inscricoes')->select('cargo')->distinct()->pluck('cargo');
    $listas = [];

    foreach ($cargos as $cargo) {
        $inscricoes = DB::table('inscricoes')
            ->where('cargo', $cargo)
	    ->where('status_avaliacao','DEFERIDO')
	    ->select('*', DB::raw('(COALESCE(pontuacao,0) + COALESCE(pontuacao_entrevista,0)) as pontuacao_final'))
            ->orderByDesc('pontuacao_final')
            ->orderBy('data_nascimento')
            ->get()
            ->toArray();

        // Ajusta a ordem de cada grupo empatado, usando a tabela de desempates
        $agrupados = [];
        foreach ($inscricoes as $i) {
            $key = $i->pontuacao_final . '_' . $i->data_nascimento;
            $agrupados[$key][] = $i;
        }
        $nova_lista = [];
        foreach ($agrupados as $key => $grupo) {
            if (count($grupo) == 1) {
                $nova_lista[] = $grupo[0];
            } else {
                $desempate = DB::table('desempates')
                    ->where('cargo', $cargo)
                    ->where('grupo_key', $key)
                    ->first();
                if ($desempate) {
                    // Coloca o escolhido em primeiro
                    usort($grupo, function($a, $b) use ($desempate) {
                        if ($a->cpf == $desempate->cpf_escolhido) return -1;
                        if ($b->cpf == $desempate->cpf_escolhido) return 1;
                        return 0;
                    });
                }
                // Adiciona todos (mesmo se não resolveu o desempate)
                foreach ($grupo as $g) $nova_lista[] = $g;
            }
        }
        $listas[$cargo] = $nova_lista;
    }

    // Gera PDF
    if ($request->input('tipo') == 'csv') {
        // CSV
        $filename = 'classificacao_final.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, ['Cargo', 'Classificacao', 'Nome', 'CPF', 'Pontuacao', 'Data de Nascimento', 'Idade']);
        foreach ($listas as $cargo => $inscricoes) {
            $pos = 1;
            foreach ($inscricoes as $i) {
                fputcsv($handle, [
                    $cargo,
                    $pos++,
                    $i->nome_completo,
                    $i->cpf,
                    $i->pontuacao_final,
                    $i->data_nascimento,
                    \Carbon\Carbon::parse($i->data_nascimento)->age
                ]);
            }
        }
        fclose($handle);
        return response()->download($filename)->deleteFileAfterSend(true);
    } else {
        // PDF
        $pdf = PDF::loadView('classificacao_final_pdf', ['listas' => $listas]);
        return $pdf->download('classificacao_final.pdf');
    }
}
public function exportarClassificacaoFinalCsv(Request $request)
{
    set_time_limit(300);
    ini_set('memory_limit', '512M');

    $cargos = DB::table('inscricoes')->select('cargo')->distinct()->pluck('cargo');
    $cabecalho = [
        'Cargo', 'Classificação', 'Nome Completo', 'CPF', 'Pontuação Documental',
        'Pontuação Entrevista', 'Nota Final', 'Data de Nascimento', 'PCD'
    ];
	header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="classificacao_final.csv"');
    // Garante acentos:
    echo "\xEF\xBB\xBF";

    $csv = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="classificacao_final.csv"');

    fputcsv($csv, $cabecalho);

    foreach ($cargos as $cargo) {
        $inscricoes = DB::table('inscricoes')
            ->where('cargo', $cargo)
            ->where('status_avaliacao', 'DEFERIDO')
            ->select(
                'nome_completo',
                'cpf',
                'pontuacao',
                'pontuacao_entrevista',
                'data_nascimento',
                'pcd',
                'cargo'
            )
            ->selectRaw('(COALESCE(pontuacao,0) + COALESCE(pontuacao_entrevista,0)) as pontuacao_final')
            ->orderByDesc('pontuacao_final')
            ->orderBy('data_nascimento')
            ->get();

        $classificacao = 1;
        foreach ($inscricoes as $i) {
            // CPF mascarado igual ao PDF
            $cpfLimpo = preg_replace('/\D/', '', $i->cpf);
            $cpfExib = (strlen($cpfLimpo) === 11)
                ? substr($cpfLimpo,0,3).'*****'.substr($cpfLimpo,-2)
                : $i->cpf;

            fputcsv($csv, [
                $i->cargo,
                $classificacao++,
                mb_strtoupper($i->nome_completo, 'UTF-8'),
                $cpfExib,
                $i->pontuacao ?? 0,
                $i->pontuacao_entrevista ?? 0,
                $i->pontuacao_final,
                $i->data_nascimento,
                ($i->pcd ? 'SIM' : 'NÃO')
            ]);
        }
    }

    fclose($csv);
    exit;
}






}


