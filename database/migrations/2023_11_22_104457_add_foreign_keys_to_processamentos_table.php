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
        Schema::table('processamentos', function (Blueprint $table) {
            $table->foreign(['id_agencia'], 'fk_processamentos_agencias_1')->references(['id'])->on('agencias');
            $table->foreign(['id_dms'], 'fk_processamentos_dms_processados_1')->references(['id'])->on('dms_processados');
            $table->foreign(['id_bam'], 'fk_processamentos_bam_processados_1')->references(['id'])->on('bam_processados');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processamentos', function (Blueprint $table) {
            $table->dropForeign('fk_processamentos_agencias_1');
            $table->dropForeign('fk_processamentos_dms_processados_1');
            $table->dropForeign('fk_processamentos_bam_processados_1');
        });
    }
};
