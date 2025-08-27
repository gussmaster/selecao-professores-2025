@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-center mt-5">
    <div class="card shadow rounded-4 w-100" style="max-width: 600px;">
        <div class="card-body">
            <h4 class="mb-4 text-center">Solicitar Recurso da Entrevista</h4>
            <form method="POST" action="{{ route('recurso-entrevista.solicitar') }}" enctype="multipart/form-data">
                @csrf
		{{-- Exibe erros de validação --}}
    			@if ($errors->any())
			        <div class="alert alert-danger mb-3">
			            <ul class="mb-0">
			                @foreach ($errors->all() as $error)
			                    <li>{{ $error }}</li>
			                @endforeach
			            </ul>
			        </div>
			 @endif
                <div class="mb-3">
                    <label>Nome:</label>
                    <input type="text" class="form-control" value="{{ $inscricao->nome_completo }}" readonly>
                </div>
                <div class="mb-3">
                    <label>Cargo:</label>
                    <input type="text" class="form-control" value="{{ $inscricao->cargo }}" readonly>
                </div>
                <div class="mb-3">
                    <label>Pontuação da Entrevista:</label>
                    <input type="text" class="form-control" value="{{ $inscricao->pontuacao_entrevista }}" readonly>
                </div>

		<div class="mb-3">
            <label for="arquivo">Anexar em PDF ÚNICO o requerimento de recurso com os documentos pertinentes. (PDF ou imagem)
            <input type="file" class="form-control" name="arquivo" accept=".pdf,image/*">
               </div>
		<div class="mb-3 text-center">
    			<a href="{{ asset('arquivos/edital03-recurso-pontuacao.pdf') }}" class="btn btn-outline-primary" target="_blank">
	        📄 Baixar Modelo do Recurso de Entrevista
      			 </a>
		   </div>

		@error('arquivo')
                 <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <button type="submit" class="btn btn-primary w-100">Enviar Recurso</button>
            </form>
        </div>
    </div>
</div>
@endsection
