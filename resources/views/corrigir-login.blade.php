<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Correção de Inscrição</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body class="bg-light">
    <div class="text-center my-4">
        <img src="{{ asset('/images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 300px;">
    </div>

    <div class="container mt-5">
        <div class="card p-4 shadow mx-auto" style="max-width: 400px;">
            <h3 class="text-center mb-4">Área do Candidato</h3>

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

            <form action="{{ route('corrigir.autenticar') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>CPF:</label>
                    <input type="text" name="cpf" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Senha (8 primeiros dígitos do seu hash de validação que está no comprovante inscrição):</label>
                    <input type="password" name="senha" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('input[name="cpf"]').mask('000.000.000-00');
            $('input[name="cpf"]').on('input', function() {
                this.value = this.value.replace(/[^0-9\.\-]/g, '');
            });
        });
    </script>
</body>
</html>
