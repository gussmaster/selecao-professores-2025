<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class CorrigeDataNascimento extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:corrige-data-nascimento';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
    $cpf = $this->ask('Informe o CPF do candidato (somente números)');
    $novaData = $this->ask('Informe a nova data de nascimento (formato YYYY-MM-DD)');

    $atualizados = DB::table('inscricoes')
        ->where('cpf', $cpf)
        ->update(['data_nascimento' => $novaData]);

    if ($atualizados) {
        $this->info("Data de nascimento atualizada com sucesso para o CPF $cpf!");
    } else {
        $this->error('Nenhuma inscrição encontrada para o CPF informado.');
     }
    }
}
