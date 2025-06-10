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

    return view('painel', compact('inscricoes', 'cargos'));
}
//fim painel

    public function exportarCSV()
{
    $inscricoes = DB::table('inscricoes')->get();

    $csv = fopen('php://output', 'w');

    $header = [
        'ID', 'Nome Completo', 'CPF', 'E-mail', 'Telefone', 'PCD', 'Descrição PCD',
        'Cargo', 'Número Inscrição', 'Hash', 'Data'
    ];

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inscricoes.csv"');

    fputcsv($csv, $header);

    foreach ($inscricoes as $i) {
        fputcsv($csv, [
            $i->id,
            $i->nome_completo,
            $i->cpf,
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
