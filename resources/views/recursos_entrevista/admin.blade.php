@extends('layouts.app')
@section('content')

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Recursos da Entrevista</h4>
        <div>
            <a href="{{ route('recursos-entrevista.export.csv') }}" class="btn btn-outline-success me-2">
                ⬇️ Exportar CSV
            </a>
            <a href="{{ route('recursos-entrevista.export.pdf') }}" class="btn btn-outline-danger">
                ⬇️ Exportar PDF
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('recursos-entrevista.admin') }}" class="d-flex align-items-center mb-3 gap-2 flex-wrap">
        <input type="text" name="cpf" class="form-control" placeholder="Filtrar por CPF" value="{{ request('cpf') }}" style="max-width:180px;">
        <button class="btn btn-primary">Filtrar</button>
        @if(request('cpf'))
            <a href="{{ route('recursos-entrevista.admin') }}" class="btn btn-outline-secondary">Limpar Filtro</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Cargo</th>
                    <th>Pontuação Entrevista</th>
                    <th>Motivo</th>
                    <th>Status</th>
                    <th>Nova Nota</th>
                    <th>Data/Hora</th>
                    <th class="text-center">Arquivos</th>
                    <th class="text-center">Ação</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recursos as $recurso)
                    <tr>
                        <td>{{ $loop->iteration + (($recursos->currentPage()-1) * $recursos->perPage()) }}</td>
                        <td>{{ $recurso->nome_completo ?? '-' }}</td>
                        <td>{{ $recurso->cpf ?? '-' }}</td>
                        <td>{{ $recurso->cargo ?? '-' }}</td>
                        <td><b>{{ $recurso->pontuacao_entrevista }}</b></td>
                        <td>{{ $recurso->motivo ?? '-' }}</td>
                        <td>{{ $recurso->status_analise ?? '-' }}</td>
                        <td>
                            @if($recurso->status_analise === 'Aceito')
                                <form method="POST" action="{{ route('recursos-entrevista.analise', $recurso->id) }}">
                                    @csrf
                                    <input type="number" name="nova_nota" class="form-control form-control-sm mb-1" placeholder="Nova Nota" value="{{ $recurso->nova_nota }}">
                                    <input type="hidden" name="status_analise" value="Aceito">
                                    <button class="btn btn-success btn-sm w-100">Salvar</button>
                                </form>
                            @else
                                {{ $recurso->nova_nota ?? '-' }}
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($recurso->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            @if($recurso->arquivo)
                                <button class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#arquivoModal{{ $recurso->id }}">
                                    Ver Arquivo
                                </button>
                                <!-- Modal -->
                                <div class="modal fade" id="arquivoModal{{ $recurso->id }}" tabindex="-1" aria-labelledby="arquivoModalLabel{{ $recurso->id }}" aria-hidden="true">
                                  <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title" id="arquivoModalLabel{{ $recurso->id }}">Documento Anexado</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                      </div>
                                      <div class="modal-body" style="height:80vh;">
                                        <iframe
                                            src="{{ asset('storage/' . $recurso->arquivo) }}"
                                            width="100%"
                                            height="100%"
                                            style="min-height:70vh;"
                                            frameborder="0"
                                        ></iframe>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('recursos-entrevista.analise', $recurso->id) }}" class="d-flex flex-column align-items-center gap-1">
                                @csrf
                                <select name="status_analise" class="form-select form-select-sm mb-1" style="min-width: 110px;" required>
                                    <option value="Pendente" {{ ($recurso->status_analise ?? 'Pendente') == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                                    <option value="Aceito" {{ ($recurso->status_analise ?? '') == 'Aceito' ? 'selected' : '' }}>Aceito</option>
                                    <option value="Não Aceito" {{ ($recurso->status_analise ?? '') == 'Não Aceito' ? 'selected' : '' }}>Não Aceito</option>
                                </select>
                                <button class="btn btn-primary btn-sm w-100 mt-1" type="submit">
                                    Alterar Status
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">Nenhum recurso enviado ainda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            {{ $recursos->links() }}
        </div>
    </div>
</div>
@endsection
