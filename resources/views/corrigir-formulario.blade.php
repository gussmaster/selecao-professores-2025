<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Corrigir Inscrição</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="text-center my-4">
        <img src="{{ asset('/images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 300px;">
    </div>

    <div class="container mt-5">
        <div class="card p-4 shadow mx-auto" style="max-width: 600px;">
            <h3 class="text-center mb-4">Corrigir Inscrição</h3>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('corrigir.atualizar') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label>Nome Completo:</label>
                    <input type="text" name="nome_completo" class="form-control" value="{{ $inscricao->nome_completo }}" required>
                </div>

                <div class="mb-3">
                    <label>Nome Social (se houver):</label>
                    <input type="text" name="nome_social" class="form-control" value="{{ $inscricao->nome_social }}">
                </div>

                <div class="mb-3">
                    <label>Telefone:</label>
                    <input type="text" name="telefone" class="form-control" value="{{ $inscricao->telefone }}">
                </div>

                <div class="mb-3">
                    <label>Email:</label>
                    <input type="email" name="email" class="form-control" value="{{ $inscricao->email }}">
                </div>

                <div class="mb-3">
                    <label>Substituir Documento Geral (PDF):</label>
                    <input type="file" name="documento" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Substituir Documento de Função (PDF):</label>
                    <input type="file" name="funcao" class="form-control">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('corrigir.logout') }}" class="btn btn-danger">Sair</a>
                    <button class="btn btn-success">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
