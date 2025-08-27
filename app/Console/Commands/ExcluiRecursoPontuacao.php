<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExcluiRecursoPontuacao extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:exclui-recurso-pontuacao';

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
       $id = $this->ask('Digite o ID do recurso de pontuação que deseja excluir (ou pressione Enter para excluir por CPF)');
    if ($id) {
        $deleted = \DB::table('recursos_pontuacao')->where('id', $id)->delete();
        if ($deleted) {
            $this->info("Recurso de pontuação ID $id excluído com sucesso.");
        } else {
            $this->error("Nenhum recurso encontrado com esse ID.");
        }
        return;
    }

    $cpf = $this->ask('Digite o CPF para excluir todos os recursos desse candidato (apenas números)');
    if ($cpf) {
        $deleted = \DB::table('recursos_pontuacao')->where('cpf', $cpf)->delete();
        if ($deleted) {
            $this->info("Todos os recursos do CPF $cpf foram excluídos.");
        } else {
            $this->error("Nenhum recurso encontrado com esse CPF.");
        }
        return;
    }

    $this->error("Nenhum parâmetro informado.");
 }    
}
