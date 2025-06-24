<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Reenvio de Documentos - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 500px;">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="{{ asset('/images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 220px;">
                </div>

                <h4 class="text-center mb-4">Login para Reenvio de Documentos</h4>

                {{-- Mensagens de erro --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $erro)
                                <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulário --}}
                <form method="POST" action="{{ route('reenvio.login.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">CPF</label>
                        <input type="text" name="cpf" class="form-control" placeholder="Digite seu CPF" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" placeholder="8 primeiros dígitos do hash" required>
                        <small class="text-muted">Senha são os 8 primeiros dígitos do código gerado na inscrição.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
