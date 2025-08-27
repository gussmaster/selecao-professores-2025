<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Classificação Final</title>
    <style>
        body { font-size: 12px; }
        .logo { width: 220px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px;}
        th, td { border: 1px solid #444; padding: 4px; text-align: left;}
        th { background: #eee; }
        h3 { margin-top: 30px; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 16px;">
        <img src="{{ public_path('images/logo-sme.png') }}" alt="Logo Secretaria" class="logo">
        <h2 style="margin: 0; font-size: 20px;">SECRETARIA MUNICIPAL DE EDUCAÇÃO DE CAUCAIA</h2>
    </div>
    <h1 style="font-size: 17px; margin-bottom: 10px;">Classificação Final por Cargo</h1>
    @foreach($listas as $cargo => $inscricoes)
        <h3>{{ $cargo }}</h3>
        <table>
            <tr>
                <th>Classificação</th>
                <th>Nome Completo</th>
                <th>CPF</th>
                <th>Pontuação Titulação</th>
                <th>Pontuação Entrevista</th>
                <th>Pontuação Total</th>
		<th>Cargo</th>
            </tr>
            @foreach($inscricoes as $i)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ mb_strtoupper($i->nome_completo, 'UTF-8') }}</td>
                    <td>
                        {{-- CPF: só 3 primeiros, depois *****, 2 últimos --}}
                        @php
                            $cpfLimpo = preg_replace('/\D/', '', $i->cpf);
                            $cpfExib = (strlen($cpfLimpo) === 11)
                                ? substr($cpfLimpo,0,3).'*****'.substr($cpfLimpo,-2)
                                : $i->cpf;
                        @endphp
                        {{ $cpfExib }}
                    </td>
                    <td>{{ $i->pontuacao ?? 0 }}</td>
                    <td>{{ $i->pontuacao_entrevista ?? 0 }}</td>
                    <td>{{ $i->pontuacao_final ?? (($i->pontuacao ?? 0) + ($i->pontuacao_entrevista ?? 0)) }}</td>
		    <td>{{ $i->cargo }}</td>
                </tr>
            @endforeach
        </table>
    @endforeach
</body>
</html>
