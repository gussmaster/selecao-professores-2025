<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Verificação de CPF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
    $(document).ready(function(){
        $('#cpf').mask('000.000.000-00');
    });
</script>

</head>
<body class="bg-light">
    <div class="text-center my-4">
        <img src="{{ asset('/images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 300px;">
    </div>
    <div class="container mt-5">
        <h3 class="text-center mb-4">Inscrição Simplificada</h3>
        <form action="{{ route('checar.cpf') }}" method="POST" class="card p-4 shadow">
            @csrf
            <div class="mb-3">
                <label for="cpf" class="form-label">Digite seu CPF (somente números):</label>
                <input type="text" name="cpf" id="cpf" class="form-control" required maxlength="11">
                @error('cpf')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>
            <button class="btn btn-primary w-100">Verificar</button>
        </form>
    </div>
    <footer class="text-center text-muted py-4">
        <small>Desenvolvido pela Gerência de Tecnologia - SME Caucaia</small>
    </footer>
</body>
</html>
