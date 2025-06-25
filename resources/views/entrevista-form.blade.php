<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lançar Entrevista</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Lançar Nota da Entrevista</h3>
    @if(session('erro'))
        <div class="alert alert-danger">{{ session('erro') }}</div>
    @endif
    @if(session('sucesso'))
        <div class="alert alert-success">{{ session('sucesso') }}</div>
    @endif
    <form method="POST" action="{{ route('entrevista.buscar') }}">
        @csrf
        <div class="mb-3">
            <label>Digite o CPF do candidato:</label>
            <input type="text" name="cpf" class="form-control" required maxlength="14">
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
        <a href="{{ route('painel') }}" class="btn btn-secondary">Voltar ao Painel</a>
    </form>
</div>
</body>
</html>
