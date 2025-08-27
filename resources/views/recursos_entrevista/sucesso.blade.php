@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <div class="alert alert-success">
        <b>Seu recurso de entrevista foi enviado com sucesso.</b> Aguarde a análise da comissão.
        @if(isset($arquivoEnviado) && $arquivoEnviado)
            <br>
            <span class="text-success">O arquivo anexado também foi salvo corretamente.</span>
        @endif
    </div>
    <a href="/" class="btn btn-secondary mt-2">Voltar à página inicial</a>
</div>
@endsection
