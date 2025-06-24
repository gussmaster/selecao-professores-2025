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
        <h3 class="text-center mb-4">SELEÇÃO PÚBLICA SIMPLIFICADA - EDITAL 003/2025</h3>
	 <div class="text-center mb-4">
    <p class="fs-6 lh-base text-justify mx-auto" style="max-width: 720px;">
        SELEÇÃO PÚBLICA SIMPLIFICADA PARA<strong> FORMAÇÃO DE BANCO DE RECURSOS HUMANOS DE PROFESSORES TEMPORÁRIOS</strong>, A FIM DE ATENDER ÀS NECESSIDADES 
        DE EXCEPCIONAL INTERESSE PÚBLICO DECORRENTES DAS CARÊNCIAS TEMPORÁRIAS EXISTENTES NAS INSTITUIÇÕES EDUCACIONAIS DA REDE PÚBLICA MUNICIPAL NO ÂMBITO DO MUNICÍPIO DE CAUCAIA-CE
    </p>
	<h1> INSCRIÇÕES ENCERRADAS <h1>
   </div>
	<!--
         <form action="{{ secure_url(route('checar.cpf',[], false)) }}" method="POST" autocomplete="on"  class="card p-4 shadow">
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
    </div> -->
	<div class="text-center mt-3 mt-3 d-grid gap-3">
                 <a href="{{ asset('arquivos/edital03.pdf') }}" class="btn btn-outline-secondary" target="_blank" download>
                        📄 Baixar Edital
                    </a>
	<div class="text-center mt-3 d-grid gap-2">
                 <a href="{{ asset('arquivos/ret001.pdf') }}" class="btn btn-outline-secondary" target="_blank" download>
                        📄 Retificação -001/2025 - EDITAL 003/2025 - PROFESSORES
                    </a>

		<a href="{{ asset('arquivos/ret002-prof.pdf') }}" class="btn btn-outline-secondary" target="_blank" download>
                        📄 Retificação -002/2025 - EDITAL 003/2025 - PROFESSORES
                    </a>

		<a href="{{ asset('arquivos/ret003.pdf') }}" class="btn btn-outline-secondary" target="_blank" download>
                        📄 Retificação -003/2025 - EDITAL 003/2025 - PROFESSORES
                    </a>

		


	<div class="text-center mt-3">
   		 		 </a>
        <a href="{{ route('segunda.via.buscar') }}" class="btn btn-outline-secondary">
                        📄 2ª Via do Comprovante
                    </a>

	 <!-- <a href="{{ route('corrigir.login') }}" class="btn btn-outline-secondary">
                        📄 Área do Candidato
                    </a> -->

	</div>
	</div>
    </div>

    <footer class="text-center text-muted py-4">
        <small>Desenvolvido pela Gerência de Tecnologia - SME Caucaia</small>
    </footer>
</body>
</html>
