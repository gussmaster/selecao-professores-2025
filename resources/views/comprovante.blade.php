<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comprovante de Inscrição</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
	<div class="text-center my-4">
       		 <img src="{{ asset('/images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 300px;">
   	 </div>

    <div class="container mt-5">
        <div class="card p-4 shadow">
            <h3 class="mb-3">Comprovante de Inscrição</h3>

            <p><strong>Nome:</strong> {{ $inscricao->nome_completo }}</p>
            <p><strong>CPF:</strong> {{ $inscricao->cpf }}</p>
            <p><strong>Inscrição nº:</strong> {{ $inscricao->numero_inscricao }}</p>
            <p><strong>Data/Hora:</strong> {{ \Carbon\Carbon::parse($inscricao->created_at)->format('d/m/Y H:i') }}</p>
            <p><strong>Hash de Validação:</strong> {{ $inscricao->hash_validacao }}</p>

            <hr>
            <p>Guarde este comprovante. Ele pode ser solicitado para validação da sua inscrição.</p>
        </div>
    </div>
    <a href="{{ route('comprovante.pdf', ['id' => $inscricao->id]) }}" class="btn btn-danger mt-3">
    Baixar Comprovante em PDF
</a>

</body>
</html>
