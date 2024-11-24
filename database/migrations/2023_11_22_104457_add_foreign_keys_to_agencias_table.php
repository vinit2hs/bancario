<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agencias', function (Blueprint $table) {
            $table->foreign(['id_banco'], 'fk_agencias_bancos_1')->references(['id'])->on('bancos');
            $table->foreign(['id_cidade'], 'fk_agencias_cidades_1')->references(['id'])->on('cidades');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agencias', function (Blueprint $table) {
            $table->dropForeign('fk_agencias_bancos_1');
            $table->dropForeign('fk_agencias_cidades_1');
        });
    }
};
