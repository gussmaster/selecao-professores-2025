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
            //
	$table->unsignedInteger('pontuacao_entrevista')->default(0)->after('pontuacao');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            //

	$table->dropColumn('pontuacao_entrevista');

        });
    }
};
