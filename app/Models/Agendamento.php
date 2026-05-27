<?php
// app/Models/Agendamento.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'servico_id',
        'nome_cliente',
        'email_cliente',
        'telefone_cliente',
        'endereco_completo',
        'bairro',
        'cidade',
        'zona',
        'data_agendamento',
        'hora_agendamento',
        'quantidade_compartimentos',
        'preco_unitario',
        'taxa_logistica',
        'subtotal',
        'total',
        'status',
        'observacoes',
        'comprovativo_pagamento'
    ];

    protected $casts = [
        'data_agendamento' => 'date',
        'hora_agendamento' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    // Accessor para formatar data
    public function getDataFormatadaAttribute()
    {
        return $this->data_agendamento->format('d/m/Y');
    }

    // Accessor para status com cor
    public function getStatusBadgeAttribute()
    {
        $cores = [
            'pendente' => 'warning',
            'confirmado' => 'success',
            'concluido' => 'info',
            'cancelado' => 'danger'
        ];
        
        return $cores[$this->status] ?? 'secondary';
    }
}