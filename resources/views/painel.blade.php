<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>

    {{-- Bootstrap CSS via CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
	@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

    <div class="text-center my-4">
        <img src="{{ asset('/images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 300px;">
    </div>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3 class="m-0">Painel de Inscrições</h3>

            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-info text-dark align-self-center">
                    Total de inscritos: {{ $totalInscritos }}
                </span>

                <a href="{{ route('relatorio') }}" class="btn btn-outline-primary">
                    📊 Relatório
                </a>
                <a href="{{ route('pontuacao.buscar') }}" class="btn btn-outline-dark">
                    🧮 Atribuir Pontuação
                </a>

                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-danger">Sair</button>
                </form>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
		<label>Cargo:</label>
                <select name="cargo" class="form-select">
                    <option value="">Todos</option>
                    @foreach($cargos as $cargo)
                        <option value="{{ $cargo }}" {{ request('cargo') == $cargo ? 'selected' : '' }}>
                            {{ $cargo }}
                        </option>
                    @endforeach
                </select>
            </div>
	    <div class="col-md-3">
		<label>CPF:</label>
                <input type="text" name="cpf" value="{{ request('cpf') }}" class="form-control" placeholder="Digite o CPF">
            </div>
           

            <div class="col-md-3">
                <label class="form-label">PCD:</label>
                <select name="pcd" class="form-select">
                    <option value="">Todos</option>
                    <option value="sim" {{ request('pcd') == 'sim' ? 'selected' : '' }}>Sim</option>
                    <option value="nao" {{ request('pcd') == 'nao' ? 'selected' : '' }}>Não</option>
                </select>
            </div>

            <div class="col-md-3 align-self-end">
                <button class="btn btn-primary w-100">Filtrar</button>
            </div>

            <div class="col-md-2 align-self-end">
                <a href="{{ route('exportar.csv', request()->query()) }}" class="btn btn-success w-100">
                    Exportar CSV
                </a>
            </div>
        </form>

        {{-- Tabela --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Inscrição</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Data de Nascimento</th>
                        <th>Cargo</th>
                        <th>PCD</th>
                        <th>Data</th>
                        <th>PDFs</th>
			<th style="min-width: 300px;"> Avaliação </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inscricoes as $insc)
                        <tr>
                            <td>{{ $insc->numero_inscricao }}</td>
                            <td>{{ $insc->nome_completo }}</td>
                            <td>{{ $insc->cpf }}</td>
                            <td>{{ \Carbon\Carbon::parse($insc->data_nascimento)->format('d/m/Y') }}</td>
                            <td>{{ $insc->cargo }}</td>
                            <td>{{ $insc->pcd ? 'Sim' : 'Não' }}</td>
                            <td>{{ \Carbon\Carbon::parse($insc->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="d-flex gap-1 flex-wrap">
                                <button onclick="abrirPopup('{{ route('admin.download', ['tipo' => 'documento', 'id' => $insc->id]) }}')"
                                        class="btn btn-sm btn-outline-primary">
                                    📄 Documento
                                </button>

                                <button onclick="abrirPopup('{{ route('admin.download', ['tipo' => 'funcao', 'id' => $insc->id]) }}')"
                                        class="btn btn-sm btn-outline-secondary">
                                    📄 Função
                                </button>

                                <button onclick="abrirPopup('{{ route('comprovante.pdf', ['id' => $insc->id]) }}')"
                                        class="btn btn-sm btn-outline-success">
                                    📄 Comprovante
                                </button>
			<td>
    <form action="{{ route('avaliar.salvar', $insc->id) }}" method="POST" class="d-flex flex-column gap-2">
        @csrf

        <select name="status_avaliacao" class="form-select status-select">
            <option value="">Selecione</option>
            <option value="Deferido" {{ $insc->status_avaliacao == 'Deferido' ? 'selected' : '' }}>Deferido</option>
            <option value="Indeferido" {{ $insc->status_avaliacao == 'Indeferido' ? 'selected' : '' }}>Indeferido</option>
        </select>

        {{-- Se Indeferido, Motivo --}}
        <select name="motivo_indeferimento" class="form-select motivo-select" 
		style="{{ $insc->status_avaliacao == 'Indeferido' ? '' : 'display: none;' }}">
            <option value=""{{ $insc->motivo_indeferimento == '' ? 'selected' : '' }}>Selecione o motivo</option>
            <option value="Não apresentou documento do item 6.1 (letra a " {{ $insc->motivo_indeferimento == 'Não apresentou documento do item 6.1 (letra a)' ? 'selected' : '' }}>Não apresentou documento do item 6.1 (letra a)</option>
            <option value="Não apresentou documento do item 6.1 (letra b " {{ $insc->motivo_indeferimento == 'Não apresentou documento do item 6.1 (letra b)' ? 'selected' : '' }}>Não apresentou documento do item 6.1 (letra b)</option>
            <option value="Não apresentou documento do item 6.1 (letra c " {{ $insc->motivo_indeferimento == 'Não apresentou documento do item 6.1 (letra c)' ? 'selected' : '' }}>Não apresentou documento do item 6.1 (letra c)</option>
            <option value="Não apresentou documento do item 6.1 (letra d " {{ $insc->motivo_indeferimento == 'Não apresentou documento do item 6.1 (letra d)' ? 'selected' : '' }}>Não apresentou documento do item 6.1 (letra d)</option>
            <option value="Não apresentou documento do item 6.1 (letra e " {{ $insc->motivo_indeferimento == 'Não apresentou documento do item 6.1 (letra e)' ? 'selected' : '' }}>Não apresentou documento do item 6.1 (letra e)</option>
            <option value="Não apresentou documento do item 6.1 (letra f " {{ $insc->motivo_indeferimento == 'Não apresentou documento do item 6.1 (letra f)' ? 'selected' : '' }}>Não apresentou documento do item 6.1 (letra f)</option>
	    <option value="Não apresentou documento do item 6.1 (letra g " {{ $insc->motivo_indeferimento == 'Não apresentou documento do item 6.1 (letra g)' ? 'selected' : '' }}>Não apresentou documento do item 6.1 (letra g)</option>
            <option value="Não apresentou documento do item 6.1 (letra h " {{ $insc->motivo_indeferimento == 'Não apresentou documento do item 6.1 (letra h)' ? 'selected' : '' }}>Não apresentou documento do item 6.1 (letra h)</option>
	    <option value="Não apresentou documento do item 6.1 (letra i " {{ $insc->motivo_indeferimento == 'Não apresentou documento do item 6.1 (letra i)' ? 'selected' : '' }}>Não apresentou documento do item 6.1 (letra i)</option>
	    <option value="Não atingiu a pontuação mínima necessária informada no item 7.2" {{ $insc->motivo_indeferimento == 'Não atingiu a pontuação mínima necessária informada no item 7.2' ? 'selected' : '' }}>Não atingiu a pontuação mínima necessária informada no item 7.2</option>




        </select>

        {{-- Se Deferido, Pontuação --}}
        <input type="number" name="pontuacao" class="form-control pontuacao-input" placeholder="Pontuação" min="0" max="1000" value="{{ $insc->pontuacao }}" style="display: none;">

        <button type="submit" class="btn btn-sm btn-success">Salvar</button>
    </form>
</td>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>



<script>
    document.querySelectorAll('.status-select').forEach(function(select) {
        toggleFields(select);

        select.addEventListener('change', function() {
            toggleFields(select);
        });
    });

    function toggleFields(select) {
        const form = select.closest('form');
        const motivo = form.querySelector('.motivo-select');
        const pontuacao = form.querySelector('.pontuacao-input');

        if (select.value === 'Indeferido') {
            motivo.style.display = 'block';
            pontuacao.style.display = 'none';
        } else if (select.value === 'Deferido') {
            motivo.style.display = 'none';
            pontuacao.style.display = 'block';
        } else {
            motivo.style.display = 'none';
            pontuacao.style.display = 'none';
        }
    }
</script>





        {{-- Paginação --}}
        <div class="d-flex justify-content-center">
           {{ $inscricoes->appends(request()->query())->links() }}
        </div>
    </div>

    {{-- Script para abrir PDFs em popup --}}
    <script>
        function abrirPopup(url) {
            window.open(url, '_blank', 'width=900,height=600,scrollbars=yes,resizable=yes');
        }
    </script>
</body>
</html>
