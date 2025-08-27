<a href="{{ route('recursos.export.csv') }}" class="btn btn-outline-success mb-3 me-2">
    ⬇️ Exportar CSV
</a>
<a href="{{ route('recursos.export.pdf') }}" class="btn btn-outline-danger mb-3">
    ⬇️ Exportar PDF
</a>


@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h4>Recursos Enviados</h4>
    <span class="badge bg-info text-dark fs-6 ms-2">
    Recursos enviados: <b>{{ $recursos->total() }}</b>
   </span>

    <div class="table-responsive mt-3">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
		<a href="{{ route('painel') }}" class="btn btn-outline-secondary mb-3">
    ← Voltar para o Painel Inicial
		</a>
		<form method="GET" action="{{ route('recursos.admin') }}" class="d-flex align-items-center mb-3 gap-2 flex-wrap">
		    <input type="text" name="cpf" class="form-control" placeholder="Filtrar por CPF" value="{{ request('cpf') }}" style="max-width:180px;">
		    <button class="btn btn-primary">Filtrar</button>
		    @if(request('cpf'))
		        <a href="{{ route('recursos.admin') }}" class="btn btn-outline-secondary">Limpar Filtro</a>
		    @endif
		</form>

                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Cargo</th>
                    <th>Status</th>
                    <th>Motivo Indeferimento</th>
                    <th>Arquivo</th>
                    <th>Data/Hora</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recursos as $recurso)
                    <tr>
                        <td>{{ $loop->iteration + (($recursos->currentPage()-1) * $recursos->perPage()) }}</td>
                        <td>{{ $recurso->nome_completo ?? '-' }}</td>
                        <td>{{ $recurso->cpf ?? '-' }}</td>
                        <td>{{ $recurso->cargo ?? '-' }}</td>
                        <td>{{ $recurso->status_avaliacao ?? '-' }}</td>
                        <td>{{ $recurso->motivo_indeferimento ?? '-' }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#pdfModal{{ $recurso->id }}">
                                Ver PDF
                            </button>

                            <!-- Modal do PDF -->
                            <div class="modal fade" id="pdfModal{{ $recurso->id }}" tabindex="-1" aria-labelledby="pdfModalLabel{{ $recurso->id }}" aria-hidden="true">
                              <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="pdfModalLabel{{ $recurso->id }}">Defesa do Candidato</h5>
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
                        </td>
                        <td>{{ \Carbon\Carbon::parse($recurso->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <!-- Dropdown para Aceito/Não Aceito -->
                            <form method="POST" action="{{ route('recursos.analise', $recurso->id) }}">
                                @csrf
                                <select name="status_analise" class="form-select form-select-sm mb-1" required>
                                    <option value="Pendente" {{ ($recurso->status_analise ?? 'Pendente') == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                                    <option value="Aceito" {{ ($recurso->status_analise ?? '') == 'Aceito' ? 'selected' : '' }}>Aceito</option>
                                    <option value="Não Aceito" {{ ($recurso->status_analise ?? '') == 'Não Aceito' ? 'selected' : '' }}>Não Aceito</option>
                                </select>
                                <button class="btn btn-success btn-sm w-100">Salvar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Nenhum recurso enviado ainda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {{ $recursos->links() }}
    </div>
</div>
@endsection
