@extends('layouts.app')
@section('content')
<div class="container" style="max-width: 600px;">
    <div class="card shadow mt-5">
        <div class="card-body">
            <h5 class="mb-4">Envio de Recurso</h5>
	    <h4 class="mb-4"> ATENÇÃO: O Recurso só pode ser Enviado uma vez. Atenção ao motivo do indeferimento e anexe a defesa de acordo com o motivo do Indeferimento. </h4>
            <ul class="list-group mb-3">
                <li class="list-group-item"><b>Nome:</b> {{ $inscricao->nome_completo }}</li>
                <li class="list-group-item"><b>Inscrição:</b> {{ $inscricao->numero_inscricao }}</li>
                <li class="list-group-item"><b>Cargo:</b> {{ $inscricao->cargo }}</li>
                <li class="list-group-item"><b>Status da Inscrição:</b> <span class="fw-bold text-{{ $inscricao->status_avaliacao == 'Indeferido' ? 'danger' : 'success' }}">{{ $inscricao->status_avaliacao }}</span></li>
                <li class="list-group-item"><b>Motivo do Indeferimento:</b> <span class="text-danger">{{ $inscricao->motivo_indeferimento ?? '-' }}</span></li>
            </ul>

            @if($recurso)
                <div class="alert alert-info">Recurso já enviado para esta inscrição. Não é possível reenviar.</div>
            @else
                <form method="POST" action="{{ route('recurso.enviar') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label>Anexar defesa (PDF, máximo 10MB)</label>
                        <input type="file" name="arquivo" class="form-control" accept="application/pdf" required>
                    </div>
                    <button class="btn btn-success w-100">Enviar Recurso</button>
                </form>
            @endif

            @if(session('error'))
                <div class="alert alert-danger mt-3">{{ session('error') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
