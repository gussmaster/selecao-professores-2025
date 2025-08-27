<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlteraNomeCandidato extends Command
{
    protected $signature = 'altera-nome-candidato';
    protected $description = 'Altera o nome completo de um candidato pelo CPF';

    public function handle()
    {
        $cpf = $this->ask('Informe o CPF do candidato (somente números)');
        $novoNome = $this->ask('Informe o novo nome completo (corrigido)');

        $cpfLimpo = preg_replace('/\D/', '', $cpf);

        $atualizado = DB::table('inscricoes')
            ->where('cpf', $cpfLimpo)
            ->update(['nome_completo' => $novoNome]);

        if ($atualizado) {
            $this->info("Nome atualizado com sucesso para: $novoNome");
        } else {
            $this->error("CPF não encontrado ou nome já está igual.");
        }
    }
}
