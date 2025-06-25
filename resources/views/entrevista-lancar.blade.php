<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Nota da Entrevista</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Adicionar Nota da Entrevista</h3>
    <form method="POST" action="{{ route('entrevista.salvar') }}">
        @csrf
        <div class="mb-3">
            <label>Nome:</label>
            <input type="text" class="form-control" value="{{ $candidato->nome_completo }}" disabled>
        </div>
        <div class="mb-3">
            <label>CPF:</label>
            <input type="text" class="form-control" value="{{ $candidato->cpf }}" disabled>
            <input type="hidden" name="cpf" value="{{ $candidato->cpf }}">
        </div>
        <div class="mb-3">
            <label>Cargo:</label>
            <input type="text" class="form-control" value="{{ $candidato->cargo }}" disabled>
        </div>
        <div class="mb-3">
            <label>Pontuação Documental:</label>
            <input type="text" class="form-control" value="{{ $candidato->pontuacao }}" disabled>
        </div>
        <div class="mb-3">
            <label>Pontuação Entrevista (atual):</label>
            <input type="number" name="pontuacao_entrevista" class="form-control" value="{{ $candidato->pontuacao_entrevista }}" min="0" max="1000" required>
        </div>
        <button type="submit" class="btn btn-success">Salvar Nota</button>
        <a href="{{ route('entrevista.form') }}" class="btn btn-secondary">Nova Pesquisa</a>
    </form>
</div>
</body>
</html>
