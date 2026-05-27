<?php
// database/migrations/xxxx_create_servicos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao');
            $table->string('preco')->default('Sob consulta');
            $table->string('duracao');
            $table->string('imagem')->nullable();
            $table->enum('categoria', ['fumigacao', 'desratizacao', 'termico']);
            $table->string('tipo_servico');
            $table->json('beneficios')->nullable();
            $table->json('detalhes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('servicos');
    }
};