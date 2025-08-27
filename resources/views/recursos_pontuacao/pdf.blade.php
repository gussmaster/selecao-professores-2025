<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recursos de Pontuação</title>
    <style>
        table,td,th{border:1px solid #000;border-collapse:collapse}
        th,td{padding:3px}
    </style>
</head>
<body>
    <h3>Recursos de Pontuação</h3>
    <table width="100%">
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Cargo</th>
                <th>Pontuação Atual</th>
                <th>Motivo</th>
                <th>Status</th>
                <th>Nova Nota</th>
                <th>Data/Hora</th>
            </tr>
        </thead>
        <tbody>
        @foreach($recursos as $r)
            <tr>
                <td>{{ $r->nome_completo }}</td>
                <td>{{ $r->cpf }}</td>
                <td>{{ $r->cargo }}</td>
                <td>{{ $r->pontuacao_atual }}</td>
                <td>{{ $r->motivo }}</td>
                <td>{{ $r->status_analise }}</td>
                <td>{{ $r->nova_nota }}</td>
                <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
