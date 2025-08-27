<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDesempatesTable extends Migration
{
    public function up()
    {
        Schema::create('desempates', function (Blueprint $table) {
            $table->id();
            $table->string('cargo');
            $table->string('grupo_key'); // Ex: "950_1990-08-01" (pontuacao_data_nascimento)
            $table->json('cpfs'); // Lista de CPFs empatados
            $table->string('cpf_escolhido'); // CPF do candidato escolhido como vencedor
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('desempates');
    }
}
