@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Resolver Empates para Classificação Final</h2>
    <form action="{{ route('classificacao.desempate.resolver') }}" method="POST">
        @csrf
        @foreach($empates as $cargo => $grupos)
            <h4 class="mt-4">{{ $cargo }}</h4>
            @if(isset($grupos))
                @foreach($grupos as $gKey => $grupo)
                    <div class="card mb-3">
                        <div class="card-header">
                            <b>Empate:</b>
                            Pontuação Documental: {{ $grupo['candidatos'][0]->pontuacao ?? 0 }},
                            Pontuação Entrevista: {{ $grupo['candidatos'][0]->pontuacao_entrevista ?? 0 }},
                            <span style="color: #28a745; font-weight: bold">
                                Soma Final: {{ $grupo['candidatos'][0]->pontuacao_final ?? (($grupo['candidatos'][0]->pontuacao ?? 0) + ($grupo['candidatos'][0]->pontuacao_entrevista ?? 0)) }}
                            </span>,
                            Nasc: {{ $grupo['candidatos'][0]->data_nascimento }}
                        </div>
                        <div class="card-body">
                            @foreach($grupo['candidatos'] as $cand)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio"
                                           name="desempate[{{$cargo}}][{{$grupo['key']}}]"
                                           value="{{$cand->cpf}}"
                                           {{ $grupo['ja_escolhido'] == $cand->cpf ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label" style="display: block;">
                                        <b>{{ $cand->nome_completo }}</b>
                                        | CPF: {{ $cand->cpf }}
                                        | Documental: {{ $cand->pontuacao ?? 0 }}
                                        | Entrevista: {{ $cand->pontuacao_entrevista ?? 0 }}
                                        | <span style="color: #28a745; font-weight: bold">
                                            Soma Final: {{ $cand->pontuacao_final ?? (($cand->pontuacao ?? 0) + ($cand->pontuacao_entrevista ?? 0)) }}
                                        </span>
                                        | Nasc: {{ $cand->data_nascimento }}
                                        | Idade: {{ \Carbon\Carbon::parse($cand->data_nascimento)->age }}
                                    </label>
                                    <input type="hidden" name="candidatos_info[{{$cargo}}][{{$grupo['key']}}][{{$loop->index}}][cpf]" value="{{ $cand->cpf }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        @endforeach
        <button type="submit" class="btn btn-success">Salvar Desempates</button>
    </form>

    <a href="{{ route('classificacao.final.export') }}" class="btn btn-primary mt-3">Gerar Relatório Final</a>
</div>
@endsection
