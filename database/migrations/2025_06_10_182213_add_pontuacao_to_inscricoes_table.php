<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('inscricoes', function (Blueprint $table) {
        $table->unsignedInteger('pontuacao')->nullable()->after('hash_validacao');
    });
}


    /**
     * Reverse the migrations.
     */
   public function down()
{
    Schema::table('inscricoes', function (Blueprint $table) {
        $table->dropColumn('pontuacao');
    });
}

};
