<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Atribuir Pontuação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
   <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Atribuir Pontuação ao Candidato</h3>
    <a href="{{ route('painel') }}" class="btn btn-secondary">← Voltar ao Painel</a>
</div>


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('pontuacao.buscar') }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="cpf" class="form-control" placeholder="Digite o CPF do candidato" required>
            <button class="btn btn-primary">Buscar</button>
        </div>
    </form>

    @if($inscricao)
        <div class="card p-4 shadow-sm">
            <h5>{{ $inscricao->nome_completo }}</h5>
            <p>CPF: {{ $inscricao->cpf }}</p>
            <p>Cargo: {{ $inscricao->cargo }}</p>

            <form method="POST" action="{{ route('pontuacao.salvar') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $inscricao->id }}">

                <div class="mb-3">
                    <label class="form-label">Pontuação (0 a 1000)</label>
                    <input type="number" name="pontuacao" value="{{ $inscricao->pontuacao }}" min="0" max="1000" class="form-control" required>
                </div>

                <button class="btn btn-success">Salvar Pontuação</button>
            </form>
        </div>
    @elseif(request()->filled('cpf'))
        <div class="alert alert-warning mt-3">Nenhum candidato encontrado com este CPF.</div>
    @endif
</div>
</body>
</html>
