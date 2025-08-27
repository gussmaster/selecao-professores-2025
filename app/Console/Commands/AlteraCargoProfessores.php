<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AlteraCargoProfessores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:altera-cargo-professores';

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
       $cpfs = $this->ask('Informe os CPFs separados por vírgula');
    $novoCargo = $this->ask('Informe o novo cargo');

    $cpfArray = array_map(function ($cpf) {
        return preg_replace('/\D/', '', trim($cpf));
    }, explode(',', $cpfs));

    $atualizados = \DB::table('inscricoes')
        ->whereIn('cpf', $cpfArray)
        ->update(['cargo' => $novoCargo]);

    $this->info("$atualizados registro(s) atualizado(s) com sucesso para o cargo: $novoCargo");
    }
}
