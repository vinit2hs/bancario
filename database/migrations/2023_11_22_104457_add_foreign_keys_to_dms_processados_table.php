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
        Schema::table('dms_processados', function (Blueprint $table) {
            $table->foreign(['id_cosif'], 'fk_dms_processados_cosifs_1')->references(['id'])->on('cosifs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_processados', function (Blueprint $table) {
            $table->dropForeign('fk_dms_processados_cosifs_1');
        });
    }
};
