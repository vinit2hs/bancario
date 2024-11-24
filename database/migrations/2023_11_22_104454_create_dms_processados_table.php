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
        Schema::create('dms_processados', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_cosif')->index('fk_dms_processados_cosifs_1');
            $table->string('rubrica');
            $table->string('descricao_rubrica');
            $table->decimal('saldo_anterior', 12);
            $table->decimal('debito', 12);
            $table->decimal('credito', 12);
            $table->decimal('saldo_atual', 12);
            $table->decimal('receita_tributavel', 12);
            $table->decimal('aliquota', 10);
            $table->decimal('issqn', 12);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dms_processados');
    }
};
