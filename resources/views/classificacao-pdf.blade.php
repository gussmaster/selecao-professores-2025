<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Classificação - {{ $cargo }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .logo { text-align: center; margin-bottom: 15px; }
        .logo img { max-width: 200px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #eee; }
        .empate { background-color: #ffe5e5; }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ public_path('images/logo-sme.png') }}" alt="Logo SME Caucaia">
    </div>
    <h3 style="text-align:center">Classificação - {{ $cargo }}</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Pontuação Documental</th>
                <th>Pontuação Entrevista</th>
                <th>Nota Final</th>
                <th>Data Nascimento</th>
                <th>Idade</th>
                <th>Empate?</th>
            </tr>
        </thead>
        <tbody>
            @php $pos = 1; $anterior = null; @endphp
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
                <tr @if($empate) class="empate" @endif>
                    <td>{{ $pos++ }}</td>
                    <td>{{ $c->nome_completo }}</td>
                    <td>{{ $c->cpf }}</td>
                    <td>{{ $c->pontuacao }}</td>
                    <td>{{ $c->pontuacao_entrevista }}</td>
                    <td>{{ $c->nota_final }}</td>
                    <td>{{ \Carbon\Carbon::parse($c->data_nascimento)->format('d/m/Y') }}</td>
                    <td>{{ $idade }}</td>
                    <td>
                        @if($empate)
                            EMPATE
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
