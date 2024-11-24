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
        Schema::table('indices_valores', function (Blueprint $table) {
            $table->foreign(['id_indice'], 'fk_indices_valores_indices_1')->references(['id'])->on('indices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('indices_valores', function (Blueprint $table) {
            $table->dropForeign('fk_indices_valores_indices_1');
        });
    }
};
