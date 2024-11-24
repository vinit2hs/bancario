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
        Schema::create('indices_valores', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_indice')->nullable()->index('fk_indices_valores_indices_1');
            $table->timestamp('data')->nullable();
            $table->decimal('valor', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indices_valores');
    }
};
