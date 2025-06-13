<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Formulário de Inscrição</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
    $(document).ready(function(){
        $('#cpf').mask('000.000.000-00');
        $('#telefone').mask('(00) 00000-0000');
    });
</script>

</head>
<body class="bg-light">
    <div class="text-center my-4">
        <img src="{{ asset('/images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 300px;">
    </div>
    <div class="container mt-5">
        <h3 class="text-center mb-4">Formulário de Inscrição - Edital003/2025</h3>

        <form action="{{ route('formulario.confirmar') }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow">
            @csrf

            <input type="hidden" name="cpf" value="{{ $cpf }}">

            <div class="mb-3">
                <label class="form-label">Nome Completo:</label>
                <input type="text" name="nome_completo" class="form-control" required>
            </div>
	    <div class="mb-3">
                <label class="form-label">Nome Social:</label>
                <input type="text" name="nome_social" class="form-control" required>
            </div>

	    <div class="mb-3">
		 <label for="data_nascimento" class="form-label">Data de Nascimento</label>
		 <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
	     </div>


            <div class="mb-3">
                <label class="form-label">E-mail:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Telefone:</label>
                <input type="text" name="telefone" id="telefone" class="form-control" required maxlength="15">

            </div>

            <div class="mb-3">
                <label class="form-label">É portador de deficiência (PCD)?</label>
                <select name="pcd" class="form-select" required onchange="mostrarDescricao(this.value)">
                    <option value="0">Não</option>
                    <option value="1">Sim</option>
                </select>
            </div>

            <div class="mb-3" id="descricao_pcd_box" style="display: none;">
                <label class="form-label">Descreva a deficiência:</label>
                <textarea name="descricao_pcd" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Cargo Desejado:</label>
                <select name="cargo" class="form-select" required>
                    <option value="">Selecione um cargo</option>
                    <option value="Professor de Educação Básica - Arte">Professor de Educação Básica - Arte</option>
                    <option value="Professor de Educação Básica - Ciências">Professor de Educação Básica - Ciências</option>
                    <option value="Professor de Educação Básica - Educação Física">Professor de Educação Básica - Educação Física</option>
                    <option value="Professor de Educação Básica - Educação Infantil">Professor de Educação Básica - Educação Infantil</option>
                    <option value="Professor de Educação Básica - Ensino Religioso">Professor de Educação Básica - Ensino Religioso</option>
                    <option value="Professor de Educação Básica - Especial AEE">Professor de Educação Básica - Especial AEE</option>
                    <option value="Professor de Educação Básica - Geografia">Professor de Educação Básica - Geografia</option>
                    <option value="Professor de Educação Básica - História">Professor de Educação Básica - História</option>
                    <option value="Professor de Educação Básica - Libras">Professor de Educação Básica - Libras</option>
                    <option value="Professor de Educação Básica - Língua Inglesa">Professor de Educação Básica - Língua Inglesa</option>
                    <option value="Professor de Educação Básica - Língua Portuguesa">Professor de Educação Básica - Língua Portuguesa</option>
                    <option value="Professor de Educação Básica - Matemática">Professor de Educação Básica - Matemática</option>
                    <option value="Professor de Educação Básica - Pedagogo (1º ao 5º ano)">Professor de Educação Básica - Pedagogo (1º ao 5º ano)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">PDF com Documentos Pessoais (até 20MB):</label>
                <input type="file" name="documentos" class="form-control" accept=".pdf" required>
            </div>

            <div class="mb-3">
                <label class="form-label">PDF com Documentos da Função e Experiência Profissional (até 20MB):</label>
                <input type="file" name="funcao" class="form-control" accept=".pdf" required>
            </div>

            <button class="btn btn-success w-100">Avançar para Confirmação</button>
        </form>
    </div>

    <script>
        function mostrarDescricao(valor) {
            const box = document.getElementById('descricao_pcd_box');
            box.style.display = valor == '1' ? 'block' : 'none';
        }
    </script>
    <footer class="text-center text-muted py-4">
    <small>Desenvolvido pela Gerência de Tecnologia - SME Caucaia</small>
    </footer>
</body>
</html>
