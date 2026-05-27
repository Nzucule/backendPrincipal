<?php
// database/migrations/xxxx_create_agendamentos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('servico_id')->constrained()->onDelete('cascade');
            $table->string('nome_cliente');
            $table->string('email_cliente');
            $table->string('telefone_cliente');
            $table->string('endereco_completo');
            $table->string('bairro');
            $table->string('cidade');
            $table->enum('zona', ['cidade', 'fora_cidade']);
            $table->date('data_agendamento');
            $table->time('hora_agendamento')->nullable();
            $table->integer('quantidade_compartimentos')->default(1);
            $table->decimal('preco_unitario', 10, 2);
            $table->decimal('taxa_logistica', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pendente', 'confirmado', 'concluido', 'cancelado'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->string('comprovativo_pagamento')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agendamentos');
    }
};