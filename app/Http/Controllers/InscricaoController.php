<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
public function salvarEntrevista(Request $request)
{
    $cpf = $request->input('cpf');
    $nota = $request->input('pontuacao_entrevista');

    DB::table('inscricoes')
        ->where('cpf', $cpf)
        ->update(['pontuacao_entrevista' => $nota]);

    return redirect()->route('entrevista.form')->with('sucesso', 'Nota da entrevista salva com sucesso!');
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
        'ID', 'Nome Completo', 'CPF','Data de Nascimento', 'E-mail', 'Telefone', 'PCD', 'Descrição PCD',
        'Cargo', 'Número Inscrição', 'Hash', 'Pontuacao', 'Pontuação Entrevista', 'Status do Deferimento', 'Motivo do Indeferimento','Data'
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
            $i->descricao_pcd,
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
}
