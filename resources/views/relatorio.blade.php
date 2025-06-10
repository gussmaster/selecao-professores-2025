<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório Geral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>Relatório Geral</h3>
        <a href="{{ route('painel') }}" class="btn btn-secondary">← Voltar ao Painel</a>
    </div>

    <div class="mb-4">
        <p><strong>Total de Inscritos:</strong> {{ $total }}</p>
        <p><strong>Inscritos Hoje:</strong> {{ $hoje }}</p>
        <p><strong>PCD:</strong> {{ $pcdSim }} (Sim) / {{ $pcdNao }} (Não)</p>
    </div>

    <h5>Inscritos por Cargo:</h5>
    <table class="table table-bordered">
        <thead class="table-light">
        <tr>
            <th>Cargo</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($porCargo as $cargo)
            <tr>
                <td>{{ $cargo->cargo }}</td>
                <td>{{ $cargo->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
