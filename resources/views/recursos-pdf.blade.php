<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Recursos</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px;}
        th, td { border: 1px solid #666; padding: 6px 4px; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Recursos Enviados</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Cargo</th>
                <th>Status</th>
                <th>Motivo Indeferimento</th>
                <th>Status do Recurso</th>
                <th>Data/Hora</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recursos as $i => $recurso)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $recurso->nome_completo }}</td>
                    <td>{{ $recurso->cpf }}</td>
                    <td>{{ $recurso->cargo }}</td>
                    <td>{{ $recurso->status_avaliacao }}</td>
                    <td>{{ $recurso->motivo_indeferimento }}</td>
                    <td>{{ $recurso->status_analise ?? 'Pendente' }}</td>
                    <td>{{ \Carbon\Carbon::parse($recurso->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
