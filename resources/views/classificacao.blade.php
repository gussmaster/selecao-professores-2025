<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Classificação Parcial dos Candidatos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 250px;">
        <h3 class="mt-3">Classificação Parcial dos Candidatos</h3>
    </div>

    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Selecione o Cargo</label>
            <select class="form-select" name="cargo" onchange="this.form.submit()">
                <option value="">Selecione</option>
                @foreach($cargos as $cargo)
                    <option value="{{ $cargo }}" {{ $cargo == $cargoSelecionado ? 'selected' : '' }}>
                        {{ $cargo }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    @if($cargoSelecionado && count($candidatos) > 0)
        <div class="mb-3">
            <a href="{{ route('classificacao.pdf', ['cargo' => $cargoSelecionado]) }}" class="btn btn-danger">
                Exportar PDF
            </a>
        </div>
        <div class="table-responsive">
        <table class="table table-bordered table-striped mt-4">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Pontuação Documental</th>
                    <th>Pontuação Entrevista</th>
                    <th>Nota Final</th>
                    <th>Data de Nascimento</th>
                    <th>Idade</th>
                    <th class="text-center">Empate?</th>
                </tr>
            </thead>
            <tbody>
                @php $pos = $candidatos->firstItem();; $anterior = null; @endphp
                @foreach($candidatos as $c)
                    @php
                        $idade = \Carbon\Carbon::parse($c->data_nascimento)->age;
                        $empate = false;
                        if ($anterior &&
                            $c->nota_final == $anterior->nota_final &&
                            $c->data_nascimento == $anterior->data_nascimento) {
                            $empate = true;
                        }
                        $anterior = $c;
                    @endphp
                    <tr @if($empate) style="background-color: #ffe5e5;" @endif>
                        <td>{{ $pos++ }}</td>
                        <td>{{ $c->nome_completo }}</td>
                        <td>{{ $c->cpf }}</td>
                        <td>{{ $c->pontuacao }}</td>
                        <td>{{ $c->pontuacao_entrevista }}</td>
                        <td><strong>{{ $c->nota_final }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($c->data_nascimento)->format('d/m/Y') }}</td>
                        <td>{{ $idade }} anos</td>
                        <td class="text-center">
                            @if($empate)
                                <span class="badge bg-danger text-white">EMPATE</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
     </div>
    {{ $candidatos->appends(['cargo' => $cargoSelecionado])->links() }}
    @elseif($cargoSelecionado)
        <div class="alert alert-warning mt-4">Nenhum candidato encontrado para este cargo.</div>
    @endif

    <a href="{{ url('/admin/painel') }}" class="btn btn-secondary mt-3">Voltar ao Painel</a>
</div>
</body>
</html>
