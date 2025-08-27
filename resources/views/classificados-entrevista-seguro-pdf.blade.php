<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Classificados - SME Caucaia</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo img { max-width: 160px; }
        .titulo { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px 5px; text-align: left; }
        th { background: #e5e5e5; }
        .footer { text-align: right; margin-top: 18px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ public_path('images/logo-sme.png') }}" alt="Logo SME Caucaia">
    </div>
    <div class="titulo">
        RESULTADO PRELIMINAR DA SEGUNDA ETAPA - ENTREVISTA
    </div>

    <table>
        <thead>
            <tr>
                <th>ORDEM</th>
                <th>NOME</th>
                <th>CPF</th>
                <th>PONTUAÇÃO ENTREVISTA</th>
                <th>CARGO</th>
            </tr>
        </thead>
        <tbody>
        @foreach($inscricoes as $idx => $i)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $i->nome_completo }}</td>
                <td>{{ $i->cpf_mascarado }}</td>
                <td style="text-align: center">{{ $i->pontuacao_entrevista }}</td>
                <td>{{ $i->cargo }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="footer">
        SME Caucaia &copy; {{ date('Y') }}
    </div>
</body>
</html>
