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
        Schema::create('processamentos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_bam')->index('fk_processamentos_bam_processados_1');
            $table->integer('id_dms')->index('fk_processamentos_dms_processados_1');
            $table->integer('id_agencia')->index('fk_processamentos_agencias_1');
            $table->string('mes');
            $table->integer('ano');
            $table->boolean('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processamentos');
    }
};
