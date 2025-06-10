<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="text-center my-4">
        <img src="{{ asset('/images/logo-sme.png') }}" alt="Logo SME Caucaia" style="max-width: 300px;">
    </div>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Painel de Inscrições</h3>
            <form action="{{ route('logout') }}" method="POST">@csrf
                <button class="btn btn-outline-danger">Sair</button>
            </form>
        </div>

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
                <label>PCD:</label>
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

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Inscrição</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Cargo</th>
                    <th>PCD</th>
                    <th>Data</th>
                    <th>PDFs</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inscricoes as $insc)
                    <tr>
                        <td>{{ $insc->numero_inscricao }}</td>
                        <td>{{ $insc->nome_completo }}</td>
                        <td>{{ $insc->cpf }}</td>
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
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $inscricoes->links() }}
    </div>

    <script>
        function abrirPopup(url) {
            window.open(url, '_blank', 'width=900,height=600,scrollbars=yes,resizable=yes');
        }
    </script>
</body>
</html>
