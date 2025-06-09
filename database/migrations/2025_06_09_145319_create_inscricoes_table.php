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
    Schema::create('inscricoes', function (Blueprint $table) {
        $table->id();
        $table->string('cpf')->unique();
        $table->string('nome_completo');
        $table->string('email');
        $table->string('telefone');
        $table->boolean('pcd')->default(false);
        $table->text('descricao_pcd')->nullable();
        $table->string('cargo')->nullable();
        $table->string('documentos_path')->nullable();
        $table->string('funcao_path')->nullable();
        $table->string('numero_inscricao');
        $table->string('hash_validacao');
        $table->timestamps();
    });
}

};
