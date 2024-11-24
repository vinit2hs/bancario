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
        Schema::create('agencias', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_banco')->nullable()->index('fk_agencias_bancos_1');
            $table->integer('id_cidade')->nullable()->index('fk_agencias_cidades_1');
            $table->string('cnpj')->nullable();
            $table->string('agencia')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agencias');
    }
};
