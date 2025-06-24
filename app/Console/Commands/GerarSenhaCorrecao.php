<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GerarSenhaCorrecao extends Command
{
    protected $signature = 'gerar:senha-correcao';

    protected $description = 'Gerar senha de correção para inscrições que estão sem senha_correcao';

    public function handle()
    {
        $this->info('🔧 Iniciando geração de senhas...');

        $inscricoes = DB::table('inscricoes')
            ->whereNull('senha_correcao')
            ->orWhere('senha_correcao', '')
            ->get();

        foreach ($inscricoes as $i) {
            if ($i->hash_validacao) {
                $senha = substr($i->hash_validacao, 0, 8);
                DB::table('inscricoes')
                    ->where('id', $i->id)
                    ->update(['senha_correcao' => $senha]);

                $this->line("✔️ Senha gerada para ID {$i->id} -> {$senha}");
            }
        }

        $this->info('✅ Processo finalizado!');
    }
}
