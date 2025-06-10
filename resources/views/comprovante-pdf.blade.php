<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comprovante de Inscrição</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .box { border: 1px solid #ccc; padding: 15px; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <div class="text-center my-4">
      	<img src="{{ public_path('images/logo-sme.png') }}" alt="" style="max-width: 300px;">
    </div>
    <h2>Comprovante de Inscrição</h2>
    <div class="box">
        <p><strong>Nome:</strong> {{ $inscricao->nome_completo }}</p>
        <p><strong>CPF:</strong> {{ $inscricao->cpf }}</p>
        <p><strong>Inscrição nº:</strong> {{ $inscricao->numero_inscricao }}</p>
        <p><strong>Data/Hora:</strong> {{ \Carbon\Carbon::parse($inscricao->created_at)->format('d/m/Y H:i') }}</p>
        <p><strong>Hash de Validação:</strong> {{ $inscricao->hash_validacao }}</p>
    </div>
    <p style="margin-top: 20px;">Guarde este comprovante. Ele poderá ser solicitado para validação da sua inscrição.</p>
</body>
</html>
