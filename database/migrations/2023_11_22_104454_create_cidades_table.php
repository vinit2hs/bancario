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
        Schema::create('cidades', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_indice')->nullable()->index('fk_cidades_indices_1');
            $table->string('ibge')->nullable();
            $table->string('nome')->nullable();
            $table->string('slug')->nullable();
            $table->decimal('aliquota', 10)->nullable();
            $table->decimal('multa', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cidades');
    }
};
