<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recurso;
use Illuminate\Support\Facades\DB;

class ReabilitarRecurso extends Command
{
    protected $signature = 'recurso:reabilitar {cpf} {etapa=inscricao}';
    protected $description = 'Permite que um candidato envie novamente o recurso removendo o registro pelo CPF e etapa';

    public function handle()
    {
        $cpf = $this->argument('cpf');
        $etapa = $this->argument('etapa'); // 'inscricao' ou 'entrevista'

        $inscricao = DB::table('inscricoes')->where('cpf', $cpf)->first();

        if (!$inscricao) {
            $this->error("Inscrição não encontrada para o CPF informado.");
            return 1;
        }

        $recurso = Recurso::where('inscricao_id', $inscricao->id)->where('tipo', $etapa)->first();

        if (!$recurso) {
            $this->info("Não existe recurso para o CPF {$cpf} e etapa '{$etapa}'. Nada a remover.");
            return 0;
        }

        $recurso->delete();
        $this->info("Recurso removido! O candidato do CPF {$cpf} pode reenviar o recurso para a etapa '{$etapa}'.");
        return 0;
    }
}
