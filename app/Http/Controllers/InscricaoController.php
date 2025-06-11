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
        'Cargo', 'Número Inscrição', 'Hash', 'Data'
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

    // Salva os arquivos
    $docPath = $request->file('documentos')->store("documentos", 'private');
    $funcPath = $request->file('funcao')->store("funcoes", 'private');

    // Salva no banco
    $id = DB::table('inscricoes')->insertGetId([
        'cpf' => $request->cpf,
        'nome_completo' => $request->nome_completo,
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
