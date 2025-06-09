<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Validação de Inscrição</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="text-center my-4">
        <img src="{{ asset('/images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 300px;">
    </div>
    <div class="container mt-5">
        <h3 class="text-center mb-4">Validação de Inscrição</h3>

        <form method="POST" action="{{ route('validar.resultado') }}" class="card p-4 shadow mb-4">
            @csrf
            <label for="hash" class="form-label">Cole o hash da inscrição:</label>
            <input type="text" name="hash" id="hash" value="{{ old('hash', $hash ?? '') }}" class="form-control mb-3" required>
            <button class="btn btn-primary w-100">Validar</button>
        </form>

        @if(isset($busca_realizada))
            @if($inscricao)
                <div class="alert alert-success">
                    <strong>Inscrição encontrada!</strong><br>
                    <ul class="mb-0">
                        <li><strong>Nome:</strong> {{ $inscricao->nome_completo }}</li>
                        <li><strong>CPF:</strong> {{ $inscricao->cpf }}</li>
                        <li><strong>Inscrição nº:</strong> {{ $inscricao->numero_inscricao }}</li>
                        <li><strong>Cargo:</strong> {{ $inscricao->cargo }}</li>
                        <li><strong>Data/Hora:</strong> {{ \Carbon\Carbon::parse($inscricao->created_at)->format('d/m/Y H:i') }}</li>
                    </ul>
                </div>
            @else
                <div class="alert alert-danger">
                    Nenhuma inscrição encontrada com este hash.
                </div>
            @endif
        @endif
    </div>
</body>
</html>
