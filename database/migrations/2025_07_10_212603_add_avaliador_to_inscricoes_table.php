<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            // Adiciona o campo e cria a foreign key para a tabela users
            $table->foreignId('avaliador_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete(); // Se o usuário for deletado, o campo vira NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            // Primeiro remove a foreign key, depois a coluna
           // $table->dropForeign(['avaliador_id']);
            $table->dropColumn('avaliador_id');
        });
    }
};
