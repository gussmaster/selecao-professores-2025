<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CriarUsuarioAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usuario:criar-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um novo usuário administrativo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nome = $this->ask('Nome do usuário');
        $email = $this->ask('E-mail');
        $senha = $this->secret('Senha');

        DB::table('users')->insert([
            'name' => $nome,
            'email' => $email,
            'password' => Hash::make($senha),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("Usuário administrativo criado com sucesso!");
    }
}
