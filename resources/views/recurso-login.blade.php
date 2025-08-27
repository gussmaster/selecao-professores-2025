@extends('layouts.app')
@section('content')
<div class="container" style="max-width: 480px;">
    <div class="card shadow mt-5">
        <div class="card-body">
            <h5 class="mb-4 text-center">Solicitar Recurso</h5>
            <form method="POST" action="{{ route('recurso.login') }}">
                @csrf
                <div class="mb-3">
                    <label>CPF</label>
                    <input type="text" name="cpf" class="form-control" maxlength="14" required autofocus>
                </div>
                <div class="mb-3">
                    <label>Data de Nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control" required>
                </div>
                <button class="btn btn-warning w-100">Acessar</button>
            </form>
            @if(session('error'))
                <div class="alert alert-danger mt-3">{{ session('error') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
