@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-center mt-5">
    <div class="card shadow rounded-4 w-100" style="max-width: 400px;">
        <div class="card-body">
            <h4 class="mb-4 text-center">Entrar para Recurso de Entrevista</h4>
            <form method="POST" action="{{ route('recurso-entrevista.login') }}">
                @csrf
                <div class="mb-3">
                    <label>CPF</label>
                    <input type="text" class="form-control" name="cpf" id="cpf" maxlength="14" required autofocus autocomplete="off">
                </div>
                <div class="mb-3">
                    <label>Data de Nascimento</label>
                    <input type="date" class="form-control" name="data_nascimento" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
                @error('cpf')
                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                @enderror
            </form>
        </div>
    </div>
</div>
<script>
// Máscara de CPF e bloqueio de letras
document.addEventListener('DOMContentLoaded', function() {
    var cpfInput = document.getElementById('cpf');
    cpfInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        let formatted = value;
        if (value.length > 9)
            formatted = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})$/, "$1.$2.$3-$4");
        else if (value.length > 6)
            formatted = value.replace(/^(\d{3})(\d{3})(\d{0,3})$/, "$1.$2.$3");
        else if (value.length > 3)
            formatted = value.replace(/^(\d{3})(\d{0,3})$/, "$1.$2");
        this.value = formatted;
    });
    cpfInput.addEventListener('keypress', function(e) {
        if (e.key && /\D/.test(e.key)) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
