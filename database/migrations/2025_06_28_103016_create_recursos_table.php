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
     Schema::create('recursos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inscricao_id');
            $table->string('numero_recurso')->unique();
            $table->enum('tipo', ['inscricao', 'entrevista'])->default('inscricao');
            $table->string('arquivo');
            $table->timestamps();

            $table->foreign('inscricao_id')->references('id')->on('inscricoes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recursos');
    }
};
