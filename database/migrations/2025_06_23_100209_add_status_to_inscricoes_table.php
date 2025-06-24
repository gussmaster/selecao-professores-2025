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
	$table->string('status_avaliacao')->nullable();
        $table->string('motivo_indeferimento')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            //
	$table->dropColumn('status_avaliacao');
        $table->dropColumn('motivo_indeferimento');
        });
    }
};
