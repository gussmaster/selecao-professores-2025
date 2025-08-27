@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h4>Solicitar Recurso contra Pontuação</h4>
    <form method="POST" action="{{ route('recurso-pontuacao.solicitar') }}"" enctype="multipart/form-data" >
        @csrf
        <div class="mb-3">
            <label>Nome:</label>
            <input type="text" class="form-control" value="{{ $inscricao->nome_completo }}" readonly>
        </div>
        <div class="mb-3">
            <label>Cargo:</label>
            <input type="text" class="form-control" value="{{ $inscricao->cargo }}" readonly>
        </div>
        <div class="mb-3">
            <label>Pontuação Atual:</label>
            <input type="text" class="form-control" value="{{ $inscricao->pontuacao }}" readonly>
        </div>
	<div class="mb-4 text-center">
   		 <a href="{{ asset('arquivos/edital03-recurso-pontuacao.pdf') }}" class="btn btn-outline-info" target="_blank" download>
		        📄 Baixar Modelo de Recurso (PDF)
	         </a>
        </div>

        <div class="mb-3">
            <label for="arquivo">Anexar em PDF ÚNICO o requerimento de recurso com os documentos pertinentes. (PDF ou imagem, opcional):</label>
            <input type="file" class="form-control" name="arquivo" accept=".pdf,image/*">
        </div>
        <button type="submit" class="btn btn-primary">Enviar Recurso</button>
    </form>
</div>
@endsection
